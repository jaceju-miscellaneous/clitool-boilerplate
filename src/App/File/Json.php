<?php

namespace App\File;

class Json extends Base
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
        $this->info = json_decode(file_get_contents($this->file));
    }

    public function save()
    {
        $jsonOptions = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
        $this->content = json_encode($this->info, $jsonOptions) . PHP_EOL;
        parent::save();
    }
}
