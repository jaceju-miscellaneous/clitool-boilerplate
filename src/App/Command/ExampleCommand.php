<?php

namespace App\Command;

use CLIFramework\Command;

class ExampleCommand extends Command
{
    public function brief()
    {
        return 'Show something for example';
    }

    /**
     * register your command here
     */
    public function init()
    {
        parent::init();
        $this->command('sub-example');
    }

    /**
     * init your application options here
     */
    public function options($opts)
    {
        $opts->add('v|verbose', 'verbose message');
        $opts->add('required:', 'required option with a value.');
        $opts->add('optional?', 'optional option with a value');
        $opts->add('multiple+', 'multiple value option.');
    }

    /**
     * Run!!
     */
    public function execute()
    {
        $logger = $this->getLogger();
        $formater = $this->getFormatter();
        $logger->info($formater->format('This is a example command.', 'yellow'));
        $input = $this->ask('Please type something:');
        $logger->info('Your typed: ' . $this->getFormatter()->format($input, 'green'));
    }
}
