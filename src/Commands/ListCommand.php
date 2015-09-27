<?php
namespace Onigoetz\Dyn53\Commands;

use Aws\Route53\Route53Client;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('list')
            ->setDescription('List the zones on route53');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getConfiguration($input);

        $client = Route53Client::factory(['key' => $config['key'], 'secret' => $config['secret']]);

        $result = $client->listHostedZones();

        $content = [];
        foreach ($result['HostedZones'] as $zone) {
            $content[] = [$zone['Name'], $zone['Id']];
        }

        $table = $this->getHelperSet()->get('table');
        $table->setHeaders(['Name', 'ID'])->setRows($content);
        $table->render($output);
    }
}
