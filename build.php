<?php

require __DIR__ . '/vendor/autoload.php';

use App\Application;
use App\JsonFile;

class Build
{
    const SEMVER_PATTERN = 'v?(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)(?:-[\da-z\-]+(?:\.[\da-z\-]+)*)?(?:\+[\da-z\-]+(?:\.[\da-z\-]+)*)?';

    protected $appName = 'App';

    protected $baseDir = null;

    protected $composer = null;

    protected $box = null;

    protected $oldSemver = '0.0.0';

    public function __construct()
    {
        $this->appName = strtolower(Application::NAME);
        $this->baseDir = getcwd();
        $this->composer = new JsonFile($this->baseDir . '/composer.json');
        $this->box = new JsonFile($this->baseDir . '/box.json');
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
        exec('git tag ' . $newVersion);
    }

    protected function updateVersion($version)
    {
        if (null !== $version && !$this->checkSemver($version)) {
            throw new \Exception('Version must match semantic version');
        }

        $newVersion = $this->getNewVersionFrom($version);
        $this->replaceComposerJsonVersion($newVersion);
        $this->replaceApplicationVersion($newVersion);
    }

    protected function updateAppBin()
    {
        $this->composer->info->bin = ['bin/' . $this->appName];
        $this->box->info->output = 'bin/' . $this->appName . '.phar';
    }

    protected function saveMetafiles()
    {
        $this->composer->save();
        $this->box->save();
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
        $this->ensureOldSemver();
        $this->updateVersion($version);
        $this->updateAppBin();
        $this->saveMetafiles();
        $this->buildPhar();
    }
}

$version = isset($argv[1]) ? $argv[1] : null;
$command = new Build();
$command->execute($version);
