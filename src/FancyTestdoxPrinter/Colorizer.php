<?php
declare(strict_types=1);

namespace rpkamp\FancyTestdoxPrinter;

final class Colorizer
{
    const COLOR_RED = 'red';
    const COLOR_GREEN = 'green';
    const COLOR_YELLOW = 'yellow';

    const ANSI_COLORS = [
        self::COLOR_RED => 31,
        self::COLOR_GREEN => 32,
        self::COLOR_YELLOW => 33,
    ];

    private $useColors;

    public function __construct($useColors)
    {
        $this->useColors = $useColors;
    }

    public function colorize(string $text, string $color): string
    {
        if (!$this->useColors || !array_key_exists($color, self::ANSI_COLORS)) {
            return $text;
        }

        return sprintf("\033[%dm%s\033[0m", self::ANSI_COLORS[$color], $text);
    }
}
