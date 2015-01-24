<?php

namespace App;

use CLIFramework\Application as CliApp;

class Application extends CliApp
{
    const NAME = 'App';
    const VERSION = '0.0.1';

    public function options($opts)
    {
        parent::options($opts);
        $opts->add('t|test?', 'Test something.');
    }

    public function init()
    {
        parent::init();
        $this->command('list');
    }
}
