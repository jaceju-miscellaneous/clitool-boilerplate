<?php

namespace App;

use CLIFramework\Application as CliApp;

class Application extends CliApp
{
    const NAME = 'App';
    const VERSION = '@package_version@';
    const REPOSITORY = 'jaceju/clitool-boilerplate';

    public function options($opts)
    {
        parent::options($opts);
        $opts->add('t|test?', 'Test something.');
    }

    public function init()
    {
        parent::init();
        $this->command('example');
        $this->command('self-update');
    }
}
