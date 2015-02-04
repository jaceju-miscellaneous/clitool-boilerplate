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

    protected function getFullPharPath($version = null)
    {
        $pharName = $this->appName;
        $pharName .= $version ? '-' . $version : '';
        $pharName .= '.phar';
        $buildDir = $this->baseDir . '/' . self::BUILD_PATH;
        $buildFile = $buildDir . '/' . $pharName;
        return $buildFile;
    }

    protected function initGhPages()
    {
        $buildDir = $this->baseDir . '/build';

        if (!file_exists($buildDir)) {
            @mkdir($buildDir . '/downloads', 0755, true);
        }
        chdir($buildDir);

        if (!file_exists('.git')) {
            $gitUrl = sprintf('git@github.com:%s.git', Application::REPOSITORY);
            exec('git init');
            exec('git remote add origin ' . $gitUrl);
        }

        $result = @exec('git checkout gh-pages');
        if ('' === $result) {
            exec('git checkout -b gh-pages');
        }
    }

    protected function buildPhar()
    {
        chdir($this->baseDir);
        $buildFile = $this->getFullPharPath();

        if (file_exists($buildFile)) {
            @unlink($buildFile);
        }

        @mkdir(dirname($buildFile));
        exec('./box.phar build');

        rename($buildFile, $this->getFullPharPath($this->newVersion));
    }

    protected function updateManifest()
    {
        if (null === $this->manifest->info) {
            $this->manifest->info = [];
        }

        $buildFile = $this->getFullPharPath($this->newVersion);

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

    protected function publishGhPages()
    {
        $buildDir = $this->baseDir . '/build';
        chdir($buildDir);
        exec('git add .');
        exec('git commit -m "Build ' . $this->newVersion . '"');
        exec('git push -u origin gh-pages');
    }

    public function execute($version = null)
    {
        $this->ensureOldSemver();
        $this->updateVersion($version);
        $this->updateRepository();
        $this->updateAppBin();
        $this->initGhPages();
        $this->buildPhar();
        $this->updateManifest();
        $this->publishGhPages();
    }
}

$version = isset($argv[1]) ? $argv[1] : null;
$command = new Build();
$command->execute($version);
