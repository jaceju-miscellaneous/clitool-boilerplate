<?php

namespace App\Command;

use CLIFramework\Command;

class SelfBuildCommand extends Command
{
    public function execute($name = 'app')
    {
        $pharName = $name . '.phar';

        $baseDir = getcwd();

        $compileDirs = ['src', 'vendor'];
        $buildDir = $baseDir . '/bin';
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

        $phar->buildFromIterator($compoIterator, $baseDir);
        $phar->setStub($phar->createDefaultStub('src/bootstrap.php'));
    }
}
