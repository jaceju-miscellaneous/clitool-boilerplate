<?php

namespace App\Filesystem;

class Json extends File
{
    /**
     * @var mixed
     */
    public $info = null;

    /**
     * @param $path
     * @param $create
     * @throws \Exception
     */
    public function __construct($path, $create = false)
    {
        parent::__construct($path, $create);
        $this->info = json_decode(file_get_contents($this->path));
    }

    public function save($newPath = null)
    {
        $jsonOptions = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
        $this->content = json_encode($this->info, $jsonOptions) . PHP_EOL;
        parent::save($newPath);
    }
}
