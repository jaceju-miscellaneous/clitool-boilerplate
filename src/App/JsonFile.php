<?php

namespace App;

class JsonFile
{
    /**
     * @var null|string
     */
    protected $file = null;

    /**
     * @var mixed|null
     */
    public $info = null;

    /**
     * @param $path
     * @param $create
     * @throws \Exception
     */
    public function __construct($path, $create = false)
    {
        $this->file = $path;

        if (!$create && !file_exists($this->file)) {
            $message = 'Here is not a ' . basename($path);
            throw new \Exception($message);
        } elseif (!file_exists($this->file)) {
            @mkdir(dirname($this->file));
            @touch($this->file);
        }

        $this->info = json_decode(file_get_contents($this->file));
    }

    public function save()
    {
        $jsonOptions = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
        $content = json_encode($this->info, $jsonOptions) . PHP_EOL;
        file_put_contents($this->file, $content);
    }

}
