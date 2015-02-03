<?php

require __DIR__ . '/vendor/autoload.php';

use App\Application;
use App\JsonFile;

class Build
{
    const SEMVER_PATTERN = 'v?(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)(?:-[\da-z\-]+(?:\.[\da-z\-]+)*)?(?:\+[\da-z\-]+(?:\.[\da-z\-]+)*)?';

    const BUILD_PATH = 'build/downloads';

    protected $appName = 'App';

    protected $baseDir = null;

    protected $composer = null;

    protected $box = null;

    protected $manifest = null;

    protected $oldSemver = '0.0.0';

    protected $newVersion = '0.0.0';

    public function __construct()
    {
        $this->appName = strtolower(Application::NAME);
        $this->baseDir = getcwd();
        $this->composer = new JsonFile($this->baseDir . '/composer.json');
        $this->box = new JsonFile($this->baseDir . '/box.json');
        $this->manifest = new JsonFile($this->baseDir . '/build/manifest.json', true);
    }

    protected function ensureOldSemver()
    {
        $version = trim(exec('git tag -l'));

        if ($version !== '' && !$this->checkSemver($version)) {
            throw new \Exception('Latest version does not match semantic version.');
        }

        if ('' === $version) {
            $version = '0.0.0';
        }

        list($major, $minor, $patch) = explode('.', $version);

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

    protected function updateVersion($version)
    {
        if (null !== $version && !$this->checkSemver($version)) {
            throw new \Exception('Version must match semantic version');
        }

        $this->newVersion = $this->getNewVersionFrom($version);
        exec('git tag ' . $this->newVersion);
    }

    protected function updateRepository()
    {
        $this->composer->info->name = Application::REPOSITORY;
        $this->composer->save();
    }

    protected function updateAppBin()
    {
        $this->box->info->output = self::BUILD_PATH . '/' . $this->appName . '.phar';
        $this->box->save();
    }

    protected function getFullPharPath()
    {
        $pharName = $this->appName . '.phar';
        $buildDir = $this->baseDir . '/' . self::BUILD_PATH;
        $buildFile = $buildDir . '/' . $pharName;
        return $buildFile;
    }

    protected function buildPhar()
    {
        $buildFile = $this->getFullPharPath();

        if (file_exists($buildFile)) {
            @unlink($buildFile);
        }

        @mkdir(dirname($buildFile));
        exec('./box.phar build');
    }

    protected function updateManifest()
    {
        if (null === $this->manifest->info) {
            $this->manifest->info = [];
        }

        $buildFile = $this->getFullPharPath();

        list($vendor, $repository) = explode('/', Application::REPOSITORY);
        $url = sprintf('http://%s.github.io/%s/downloads/%s', $vendor, $repository, basename($buildFile));

        $manifest = [
            'name' => basename($buildFile),
            'sha1' => sha1_file($buildFile),
            'url'  => $url,
            'version' => $this->newVersion,
        ];

        $this->manifest->info[] = $manifest;
        $this->manifest->save();
    }

    public function execute($version = null)
    {
        $this->ensureOldSemver();
        $this->updateVersion($version);
        $this->updateRepository();
        $this->updateAppBin();
        $this->buildPhar();
        $this->updateManifest();
    }
}

$version = isset($argv[1]) ? $argv[1] : null;
$command = new Build();
$command->execute($version);
