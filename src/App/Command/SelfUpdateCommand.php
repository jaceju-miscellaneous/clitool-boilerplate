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

    public function execute()
    {
        list($vendor, $repository) = explode('/', Application::REPOSITORY);
        $url = sprintf(self::MANIFEST_FILE, $vendor, $repository);
        $manager = new Manager(Manifest::loadFile($url));
        $manager->update($this->getApplication()->getVersion(), true);
    }
}
