<?php

namespace App;

use CLIFramework\Application as CliApp;

class Application extends CliApp
{
    const NAME = 'App';
    const BIN_NAME = 'app';
    const VERSION = '@package_version@';
    const REPOSITORY = 'vendor-name/app-name';

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
