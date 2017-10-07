<?php

namespace rpkamp;

use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\TextUI\ResultPrinter;
use PHPUnit\Util\TestDox\NamePrettifier;

class FancyTestdoxPrinter extends ResultPrinter
{
    /**
     * @var string
     */
    private $currentTestName;

    /**
     * @var string|null
     */
    private $currentTestSymbol;

    /**
     * @var string|null
     */
    private $currentClassNameUnderTest;

    /**
     * @var string
     */
    private $currentTestResultAdditionalInformation;

    /**
     * @var NamePrettifier
     */
    private $prettifier;

    const COLOR_RED = 'red';
    const COLOR_GREEN = 'green';
    const COLOR_YELLOW = 'yellow';

    const ANSI_COLORS = [
        self::COLOR_RED => 31,
        self::COLOR_GREEN => 32,
        self::COLOR_YELLOW => 33,
    ];

    public function __construct(
        $out = null,
        $verbose = false,
        $colors = self::COLOR_DEFAULT,
        $debug = false,
        $numberOfColumns = 80,
        $reverse = false
    ) {
        $this->prettifier = new NamePrettifier();
        parent::__construct($out, $verbose, $colors, $debug, $numberOfColumns, $reverse);
    }

    public function startTest(Test $test)
    {
        $className = $this->prettifier->prettifyTestClass(get_class($test));
        if ($className !== $this->currentClassNameUnderTest) {
            if (null !== $this->currentClassNameUnderTest) {
                $this->write("\n");
            }
            $this->write(sprintf("%s\n", $className));
            $this->currentClassNameUnderTest = $className;
        }

        if ($test instanceof TestCase || $test instanceof PhptTestCase) {
            $this->currentTestName = $this->prettifier->prettifyTestMethod($test->getName());
        }

        $this->currentTestSymbol = $this->colorize('✔', self::COLOR_GREEN);
        $this->currentTestResultAdditionalInformation = null;

        parent::startTest($test);
    }

    public function endTest(Test $test, $time): void
    {
        parent::endTest($test, $time);
        
        if (!$test instanceof TestCase && !$test instanceof PhptTestCase) {
            return;
        }

        if ($time > 5) {
            $timeString = $this->colorize(sprintf('[%.2f ms]', $time * 1000), self::COLOR_RED);
        } elseif ($time > 1) {
            $timeString = $this->colorize(sprintf('[%.2f ms]', $time * 1000), self::COLOR_YELLOW);
        } else {
            $timeString = sprintf('[%.2f ms]', $time * 1000);
        }

        $additionalInformation = '';
        if (null !== $this->currentTestResultAdditionalInformation) {
            $lines = explode("\n", $this->currentTestResultAdditionalInformation);
            
            $additionalInformation = sprintf(
                "   │\n%s\n\n",
                implode(
                    "\n",
                    array_map(
                        function (string $text) {
                            return sprintf('   │ %s', $text);
                        },
                        $lines
                    )
                )
            );
        }

        $this->write(sprintf(
            " %s %s %s\n%s",
            $this->currentTestSymbol,
            $this->currentTestName,
            $timeString,
            $additionalInformation
        ));
    }

    public function addError(Test $test, Exception $e, $time)
    {
        $this->currentTestSymbol = $this->colorize('✘', self::COLOR_YELLOW);
        $this->currentTestResultAdditionalInformation = (string) $e;
    }

    public function addWarning(Test $test, Warning $e, $time)
    {
        $this->currentTestSymbol = $this->colorize('✘', self::COLOR_YELLOW);
        $this->currentTestResultAdditionalInformation = (string) $e;
    }

    public function addFailure(Test $test, AssertionFailedError $e, $time)
    {
        $this->currentTestSymbol = $this->colorize('✘', self::COLOR_RED);
        $this->currentTestResultAdditionalInformation = (string) $e;
    }

    public function addIncompleteTest(Test $test, Exception $e, $time)
    {
        $this->currentTestSymbol = $this->colorize('∅', self::COLOR_YELLOW);
        if ($this->verbose) {
            $this->currentTestResultAdditionalInformation = (string) $e;
        }
    }

    public function addRiskyTest(Test $test, Exception $e, $time)
    {
        $this->currentTestSymbol = $this->colorize('☢', self::COLOR_YELLOW);
        if ($this->verbose) {
            $this->currentTestResultAdditionalInformation = (string) $e;
        }
    }

    public function addSkippedTest(Test $test, Exception $e, $time)
    {
        $this->currentTestSymbol = $this->colorize('→', self::COLOR_YELLOW);
        if ($this->verbose) {
            $this->currentTestResultAdditionalInformation = (string) $e;
        }
    }

    public function writeProgress($progress)
    {
        // NOOP, block normal behavior of \PHPUnit\TextUI\ResultPrinter
    }

    public function flush()
    {
    }

    public function printResult(TestResult $result)
    {
        $this->printHeader();
        $this->printFooter($result);
    }

    private function colorize(string $text, string $color): string
    {
        if (!$this->colors || !array_key_exists($color, self::ANSI_COLORS)) {
            return $text;
        }

        return sprintf("\033[%dm%s\033[0m", self::ANSI_COLORS[$color], $text);
    }
}
