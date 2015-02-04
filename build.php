<?php

require __DIR__ . '/vendor/autoload.php';
declare(ticks = 1);

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

    protected $output = [];

    protected static $debug = false;

    public function __construct($debug = false)
    {
        self::$debug = $debug;
        $this->appName = strtolower(Application::NAME);
        $this->baseDir = getcwd();
        $this->composer = new JsonFile($this->baseDir . '/composer.json');
        $this->box = new JsonFile($this->baseDir . '/box.json');
        $this->manifest = new JsonFile($this->baseDir . '/build/manifest.json', true);
    }

    protected static function exec($cmd, $hide = true, &$output = null)
    {
        $hide = $hide && !self::$debug;
        $cmd .= $hide ? ' &> /dev/null' : '';
        return exec($cmd, $output);
    }

    protected function getLatestVersion()
    {
        $versions = [];
        self::exec('git tag -l', false, $versions);
        usort($versions, 'version_compare');
        $version = array_pop($versions);
        return trim($version);
    }

    protected function getHashByTag($tag)
    {
        if (empty($tag)) { return ''; }
        $hashes = [];
        self::exec('git rev-list ' . $tag  . ' | head -n 1', false, $hashes);
        $hash = array_pop($hashes);
        return trim($hash);
    }

    protected function getLatestCommitHash()
    {
        return exec('git log --format="%H" -n 1');
    }

    protected function ensureOldSemver()
    {
        $version = $this->getLatestVersion();
        $oldHash = $this->getHashByTag($version);
        $latestHash = $this->getLatestCommitHash();

        if ($oldHash === $latestHash) {
            return false;
        }

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

        return true;
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
        self::exec('git tag ' . $this->newVersion);
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
            self::exec('git init');
            self::exec('git remote add origin ' . $gitUrl);
        }

        $result = self::exec('git checkout gh-pages');
        if ('' === $result) {
            self::exec('git checkout -b gh-pages');
        }
    }

    protected function buildPhar()
    {
        chdir($this->baseDir);
        $buildFile = $this->getFullPharPath();

        if (file_exists($buildFile)) {
            unlink($buildFile);
            sleep(3);
        }

        if (!file_exists(dirname($buildFile))) {
            mkdir(dirname($buildFile));
        }
        self::exec('./box.phar build');
        copy($buildFile, $this->getFullPharPath($this->newVersion));
    }

    protected function publishGhPages()
    {
        $buildDir = $this->baseDir . '/build';
        chdir($buildDir);
        self::exec('git commit -a -m "Build ' . $this->newVersion . '"');
        self::exec('git push -u origin gh-pages');
        echo PHP_EOL, 'Version ' . $this->newVersion . ' be published.', PHP_EOL;
    }

    public function execute($version = null)
    {
        register_tick_function([$this, 'progress']);
        if ($this->ensureOldSemver()) {
            $this->updateVersion($version);
            $this->updateRepository();
            $this->updateAppBin();
            $this->initGhPages();
            $this->buildPhar();
            $this->publishGhPages();
        }
    }

    public function progress()
    {
        static $start;

        if (empty($start)) { $start = microtime(true); }
        $now = microtime(true);

        if (!self::$debug && ($now - $start > 0.0001)) {
            echo '.';
        }
        $start = $now;
    }
}

$version = isset($argv[1]) ? $argv[1] : null;
$command = new Build();
$command->execute($version);
