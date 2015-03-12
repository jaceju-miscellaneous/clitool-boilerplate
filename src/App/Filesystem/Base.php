<?php

namespace App\Filesystem;


abstract class Base
{
    /**
     * @var null|string
     */
    protected $path = null;

    /**
     * @var string
     */
    protected $content = '';

    /**
     * @var bool
     */
    protected $isExists = false;

    /**
     * @param $path
     * @param $create
     * @throws \Exception
     */
    public function __construct($path, $create = false)
    {
        $this->path = $path;
        $this->isExists = file_exists($this->path);
    }

    /**
     * @param $newPath
     * @return void
     */
    public function save($newPath = null)
    {
        if (null === $newPath) {
            $newPath = $this->path;
        }
        file_put_contents($newPath, $this->content);
    }

    public function copy($dest)
    {
        $source = $this->path;

        @mkdir($dest, 0755);
        foreach (
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            ) as $item
        ) {
            if ($item->isDir()) {
                @mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                @copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
    }

    public function rename($source, $newName)
    {
        $fullName = dirname($source) . '/' . $newName;
        @rename($source, $fullName);
    }
}
