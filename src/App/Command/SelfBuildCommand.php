<?php

namespace App\Command;

use App\Application;
use CLIFramework\Command;
use CLIFramework\CommandException;

class SelfBuildCommand extends Command
{
    const SEMVER_PATTERN = 'v?(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)(?:-[\da-z\-]+(?:\.[\da-z\-]+)*)?(?:\+[\da-z\-]+(?:\.[\da-z\-]+)*)?';

    protected $appName = 'App';

    protected $baseDir = null;

    protected $composerFile = null;

    protected $composerInfo = null;

    protected $boxFile = null;

    protected $boxInfo = null;

    protected $oldSemver = '0.0.0';

    public function brief()
    {
        return 'Build executable phar into `bin` folder';
    }

    public function init()
    {
        parent::init();
        $this->appName = strtolower(Application::NAME);
        $this->baseDir = getcwd();
    }

    protected function checkComposer()
    {
        $this->composerFile = $this->baseDir . '/composer.json';

        if (!file_exists($this->composerFile)) {
            $message = 'Here has not a project based on composer.';
            throw new CommandException($message);
        }
        $this->composerInfo = json_decode(file_get_contents($this->composerFile));
    }

    protected function checkBox()
    {
        $this->boxFile = $this->baseDir . '/box.json';

        if (!file_exists($this->boxFile)) {
            $message = 'Here has not a project based on box.';
            throw new CommandException($message);
        }
        $this->boxInfo = json_decode(file_get_contents($this->boxFile));
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

    protected function updateAppBin()
    {
        $this->composerInfo->bin = ['bin/' . $this->appName];
        $this->boxInfo->output = 'bin/' . $this->appName . '.phar';
    }

    protected function saveComposerJson()
    {
        $jsonOptions = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
        $content = json_encode($this->composerInfo, $jsonOptions);
        file_put_contents($this->composerFile, $content);
    }

    protected function saveBoxJson()
    {
        $jsonOptions = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
        $content = json_encode($this->boxInfo, $jsonOptions);
        file_put_contents($this->boxFile, $content);
    }

    protected function buildPhar()
    {
        $pharName = $this->appName . '.phar';
        $buildDir = $this->baseDir . '/bin';
        $buildFile = $buildDir . '/' . $pharName;

        if (file_exists($buildFile)) {
            @unlink($buildFile);
        }

        exec('./box.phar build');
        rename($buildFile, $buildDir . '/' . $this->appName);
    }

    public function execute($version = null)
    {
        $this->checkComposer();
        $this->checkBox();
        $this->ensureOldSemver();
        $this->updateVersion($version);
        $this->updateAppBin();
        $this->saveComposerJson();
        $this->saveBoxJson();
        $this->buildPhar();
    }
}
