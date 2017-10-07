<?php

/**
 * This file only serves for a demo output of FancyTestdoxPrinter, and should not
 * be used in production as it is all kinds of broken!
 */
class FileReader
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function read(): string
    {
        return '';
    }

    public function open()
    {
        fopen();
    }
}
