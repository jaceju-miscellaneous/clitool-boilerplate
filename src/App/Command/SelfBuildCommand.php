<?php

namespace App\Command;

use CLIFramework\Command;
use CLIFramework\CommandException;

class SelfBuildCommand extends Command
{
    const SEMVER_PATTERN = 'v?(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)(?:-[\da-z\-]+(?:\.[\da-z\-]+)*)?(?:\+[\da-z\-]+(?:\.[\da-z\-]+)*)?';

    protected $baseDir = null;

    protected $composerFile = null;

    protected $composerInfo = null;

    protected $oldSemver = '0.0.0';

    public function brief()
    {
        return 'Build executable phar into `bin` folder';
    }

    protected function checkComposer()
    {
        $this->baseDir = getcwd();
        $this->composerFile = $this->baseDir . '/composer.json';

        if (!file_exists($this->composerFile)) {
            $message = 'Here has not a project based on composer.';
            throw new CommandException($message);
        }
        $this->composerInfo = json_decode(file_get_contents($this->composerFile));
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

    protected function checkSemver($version)
    {
        return (bool) preg_match('/\b' . self::SEMVER_PATTERN . '\b/', $version);
    }

    protected function getNewVersionFrom($version)
    {
        $newVersion = $version;

        if (null === $version) {
            $this->oldSemver['patch'] ++;
            $newVersion = implode('.', $this->oldSemver);
        }

        return $newVersion;
    }

    protected function replaceComposerJsonVersion($newVersion)
    {
        $this->composerInfo->version = $newVersion;
        $jsonOptions = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
        $content = json_encode($this->composerInfo, $jsonOptions);
        file_put_contents($this->composerFile, $content);
    }


    protected function replaceApplicationVersion($newVersion)
    {
        $pattern = '/^\s+const VERSION = \'(' . self::SEMVER_PATTERN . ')\'\s*;$/m';
        $appFile = $this->baseDir . '/src/App/Application.php';

        $content = file_get_contents($appFile);
        $replace = '    const VERSION = \'' . $newVersion . '\';';

        $content = preg_replace($pattern, $replace, $content);
        file_put_contents($appFile, $content);
    }

    protected function updateVersion($version)
    {
        if (null !== $version && !$this->checkSemver($version)) {
            throw new CommandException('Version must match semantic version');
        }

        $newVersion = $this->getNewVersionFrom($version);
        $this->replaceComposerJsonVersion($newVersion);
        $this->replaceApplicationVersion($newVersion);
    }

    public function execute($name = 'app', $version = null)
    {
        $this->checkComposer();
        $this->ensureOldSemver();
        $this->buildPhar($name);
        $this->updateVersion($version);
    }
}
