<?php

namespace App\Filesystem;

class File extends Base
{
    public function __construct($path, $create = false)
    {
        parent::__construct($path, $create);

        if (!$create && !$this->isExists) {
            $message = 'Here is not a ' . basename($path);
            throw new \Exception($message);
        } elseif (!$this->isExists) {
            @mkdir(dirname($this->path));
            @touch($this->path);
        }
    }
}
