<?php

namespace App\Command;

use CLIFramework\Command;
use CLIFramework\CommandException;

class SelfBuildCommand extends Command
{
    protected $baseDir = null;

    protected $composerInfo = null;

    protected $oldSemver = '0.0.0';

    public function setAndCheckEnv()
    {
        $this->baseDir = getcwd();
        $composerFile = $this->baseDir . '/composer.json';

        if (!file_exists($composerFile)) {
            $message = 'Here has not a project based on composer.';
            throw new CommandException($message);
        }
        $this->composerInfo = json_decode(file_get_contents($composerFile));
    }

    protected function ensureOldSemver()
    {
        $oldVersion = $this->composerInfo->version;

        list($major, $minor, $patch) = explode('.', $oldVersion);

        $this->oldSemver = [
            'major' => $major,
            'minor' => $minor,
            'patch' => $patch,
        ];
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
        $this->ensureOldSemver();
        $this->buildPhar($name);
    }
}
