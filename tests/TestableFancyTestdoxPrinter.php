<?php

namespace rpkamp;

class TestableFancyTestdoxPrinter extends FancyTestdoxPrinter
{
    private $buffer;

    public function write($text)
    {
        $this->buffer .= $text;
    }

    public function getBuffer()
    {
        return $this->buffer;
    }
}
