<?php

namespace App\Command\ExampleCommand;

use CLIFramework\Command;

class SubExampleCommand extends Command
{
    public function brief()
    {
        return 'This is a sub-command example';
    }

    /**
     * Run!!
     */
    public function execute()
    {
        $logger = $this->getLogger();
        $formater = $this->getFormatter();
        $logger->info($formater->format('This is a sub command.', 'yellow'));
    }
}
