<?php

namespace App\Command;

use CLIFramework\Command;

class SelfBuildCommand extends Command
{
    protected $baseDir = null;

    public function setAndCheckEnv()
    {
        $this->baseDir = getcwd();
    }

    protected function buildPhar($name)
    {
        $pharName = $name . '.phar';

        $compileDirs = ['src', 'vendor'];
        $buildDir = $this->baseDir . '/bin';
        $buildFile = $buildDir . '/' . $pharName;

        if (file_exists($buildFile)) {
            @unlink($buildFile);
        }

        $phar = new \Phar(
            $buildFile,
            \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_FILENAME, $pharName
        );

        $compoIterator = new \AppendIterator();
        foreach ($compileDirs as $dir) {
            $it = new \RecursiveDirectoryIterator($dir);
            $compoIterator->append(new \RecursiveIteratorIterator($it));
        }

        $phar->buildFromIterator($compoIterator, $this->baseDir);
        $phar->setStub($phar->createDefaultStub('src/bootstrap.php'));
    }

    public function execute($name = 'app', $version = null)
    {
        $this->setAndCheckEnv();
        $this->buildPhar($name);
    }
}
