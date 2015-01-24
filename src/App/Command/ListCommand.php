<?php

namespace App\Command;

use CLIFramework\Command;

class ListCommand extends Command
{

    public function init()
    {
        // register your subcommand here ..
    }

    public function options($opts)
    {
        // command options

    }

    public function execute()
    {
        $logger = $this->logger;
        $logger->info('List something');
    }
}
