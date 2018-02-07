<?php

namespace rpkamp;

class TestableFancyTestdoxPrinter extends FancyTestdoxPrinter
{
    private $buffer;

    public function write($text): void
    {
        $this->buffer .= $text;
    }

    public function getBuffer()
    {
        return $this->buffer;
    }
}
