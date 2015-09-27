<?php
namespace Onigoetz\Dyn53\Commands;

use Aws\Route53\Route53Client;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('update')
            ->setDescription('Update a domain on route53')
            ->addOption(
                'zone',
                'z',
                InputOption::VALUE_OPTIONAL,
                'Which zone do you want to update ?'
            )
            ->addOption(
                'domain',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Which domain do you want to update ?'
            )
            ->addOption(
                'ip',
                null,
                InputOption::VALUE_OPTIONAL,
                'The IP Adress to apply'
            )
            ->addOption(
                'policy',
                'p',
                InputOption::VALUE_OPTIONAL,
                'How do you want to detect the IP address',
                'Onigoetz\Dyn53\Policies\MyExternalIP'
            );
    }

    protected function getMandatoryParameters()
    {
        $parameters = parent::getMandatoryParameters();

        $parameters[] = 'domain';
        $parameters[] = 'zone';

        return $parameters;
    }

    protected function getCurrentRecord(Route53Client $client, $config)
    {
        return $client->listResourceRecordSets(
            [
                'HostedZoneId' => $config['zone'],
                'StartRecordName' => $config['domain'],
                'StartRecordType' => 'A',
                'MaxItems' => 1,
            ]
        );
    }

    protected function needsUpdate($config, $result)
    {

        //if there is no record, update (create) one
        if (!count($result['ResourceRecordSets'])) {
            return true;
        }

        //if we have more or less than one entry, we have a problem
        if (count($result['ResourceRecordSets'][0]['ResourceRecords']) != 1) {
            return true;
        }

        //if the IP's don't match : update them
        if ($result['ResourceRecordSets'][0]['ResourceRecords'][0]['Value'] != $config['ip']) {
            return true;
        }

        return false;
    }

    protected function applyUpdate($client, $config)
    {
        $client->changeResourceRecordSets(
            [
                // HostedZoneId is required
                'HostedZoneId' => $config['zone'],
                // ChangeBatch is required
                'ChangeBatch' => [
                    'Comment' => 'An IP address change was detected; updating',
                    // Changes is required
                    'Changes' => [
                        [
                            // Action is required
                            'Action' => 'UPSERT',
                            // ResourceRecordSet is required
                            'ResourceRecordSet' => [
                                'Name' => $config['domain'],
                                'Type' => 'A',
                                'TTL' => '60',
                                'ResourceRecords' => [['Value' => $config['ip']]],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getConfiguration($input);

        //get the IP address
        if ($config['ip'] == null) {
            $resolver = new $config['policy']();
            $config['ip'] = $resolver->getIP();
        }

        //open connection to R53
        $client = Route53Client::factory(['key' => $config['key'], 'secret' => $config['secret']]);

        $current = $this->getCurrentRecord($client, $config);

        if ($this->needsUpdate($config, $current)) {
            $result = $this->applyUpdate($client, $config);

            $output->writeln('Updated IP to : ' . $config['ip']);
        } else {
            $output->writeln('Nothing to update');
        }
    }
}
