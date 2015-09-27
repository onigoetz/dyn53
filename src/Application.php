<?php

namespace Onigoetz\Dyn53;

use Onigoetz\Dyn53\Commands\ListCommand;
use Onigoetz\Dyn53\Commands\UpdateCommand;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * The console application that handles the commands
 *
 * @author Stéphane Goetz <onigoetz@onigoetz.ch>
 */
class Application extends BaseApplication
{
    /**
     * Initializes all the composer commands
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new UpdateCommand();
        $commands[] = new ListCommand();

        return $commands;
    }
}
