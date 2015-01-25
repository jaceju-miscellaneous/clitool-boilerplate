<?php

namespace App\Command;

use CLIFramework\Command;

class ExampleCommand extends Command
{
    public function brief()
    {
        return 'Show something for example';
    }

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
        $logger->info('This is a example command.');
    }
}
