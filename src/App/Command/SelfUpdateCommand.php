<?php

namespace App\Command;

use App\Application;
use CLIFramework\Command;
use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;

class SelfUpdateCommand extends Command
{
    const MANIFEST_FILE = 'http://%s.github.io/%s/manifest.json';

    public function brief()
    {
        return 'Updates craftsman.phar to the latest version';
    }

    public function options($opts)
    {
        $opts->add('major', 'Lock to current major version');
        $opts->add('pre', 'Allow pre-releases');
    }

    public function execute()
    {
        list($vendor, $repository) = explode('/', Application::REPOSITORY);
        $url = sprintf(self::MANIFEST_FILE, $vendor, $repository);
        $manager = new Manager(Manifest::loadFile($url));
        $major = (bool) $this->getOptions()->major;
        $pre = (bool) $this->getOptions()->pre;
        $manager->update($this->getApplication()->getVersion(), $major, $pre);
    }
}
