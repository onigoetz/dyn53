<?php
namespace Onigoetz\Dyn53\Commands;

use Aws\Common\Exception\LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCommand extends Command
{
    protected function configure()
    {
        $this
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_OPTIONAL,
                'The path to a configuration file that will provide all options'
            )
            ->addOption(
                'key',
                'k',
                InputOption::VALUE_OPTIONAL,
                'Amazon Key'
            )
            ->addOption(
                'secret',
                's',
                InputOption::VALUE_OPTIONAL,
                'Amazon Secret'
            );
    }

    protected function readConfig(InputInterface $input)
    {
        $config = array();
        $file = $input->getOption('config');
        if ($file) {
            if(!file_exists($file)) {
                throw new \LogicException("the file '$file' doesn't exist");
            }

            $config = parse_ini_file($file);
        }

        return $this->fillTheBlanks($config);
    }

    protected function fillTheBlanks($config)
    {
        foreach ($this->getParameters() as $parameter) {
            if (!array_key_exists($parameter, $config)) {
                $config[$parameter] = null;
            }
        }

        return $config;
    }

    protected function getParameters()
    {
        $parameters = array();

        foreach ($this->getDefinition()->getOptions() as $option) {
            if ($option->getName() != 'config') {
                $parameters[] = $option->getName();
            }
        }

        return $parameters;
    }

    protected function getMandatoryParameters()
    {
        return array('key', 'secret');
    }

    protected function getConfiguration(InputInterface $input)
    {
        //get the base configuration
        $config = $this->readConfig($input);

        //override with provided options
        $parameters = $this->getParameters();
        foreach ($parameters as $key) {
            if ($input->getOption($key)) {
                $config[$key] = $input->getOption($key);
            }
        }

        //check that we have the mandataory parameters
        $mandatory_parameters = $this->getMandatoryParameters();
        foreach ($mandatory_parameters as $key) {
            if ($config[$key] == null) {
                throw new \LogicException(
                    "the parameter '$key' wasn't provided please specify it either in the configuration file or as an argument"
                );
            }
        }

        return $config;
    }
}
