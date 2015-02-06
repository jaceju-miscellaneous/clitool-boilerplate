<?php

namespace App\File;


abstract class Base
{
    /**
     * @var null|string
     */
    protected $file = null;

    /**
     * @var string
     */
    protected $content = '';

    /**
     * @param $path
     * @param $create
     * @throws \Exception
     */
    public function __construct($path, $create = false)
    {
        $this->file = $path;
        $isExists = file_exists($this->file);

        if (!$create && !$isExists) {
            $message = 'Here is not a ' . basename($path);
            throw new \Exception($message);
        } elseif (!$isExists) {
            @mkdir(dirname($this->file));
            @touch($this->file);
        }
    }

    /**
     * @return void
     */
    public function save()
    {
        file_put_contents($this->file, $this->content);
    }
}
