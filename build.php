<?php

/**
 * @see http://www.sitepoint.com/packaging-your-apps-with-phar/
 */

call_user_func(
    function () {
        $pharName = 'app.phar';

        $compileDirs = ['src', 'vendor'];
        $buildDir = __DIR__ . '/bin';

        $phar = new Phar(
            $buildDir . '/' . $pharName,
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, $pharName
        );

        $compoIterator = new AppendIterator();
        foreach ($compileDirs as $dir) {
            $it = new RecursiveDirectoryIterator($dir);
            $compoIterator->append(new RecursiveIteratorIterator($it));
        }

        $phar->buildFromIterator($compoIterator, __DIR__);
        $phar->setStub($phar->createDefaultStub('src/bootstrap.php'));
    }
);
