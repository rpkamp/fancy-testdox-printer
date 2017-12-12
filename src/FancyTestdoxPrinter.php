<?php

namespace rpkamp;

use Exception;
use rpkamp\FancyTestdoxPrinter\Colorizer;
use rpkamp\FancyTestdoxPrinter\TestResult as FancyTestResult;
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
     * @var FancyTestResult
     */
    private $currentTestResult;

    /**
     * @var FancyTestResult[]
     */
    private $nonSuccessfulTestResults = [];

    /**
     * @var NamePrettifier
     */
    private $prettifier;

    /**
     * @var Colorizer
     */
    private $colorizer;

    public function __construct(
        $out = null,
        $verbose = false,
        $colors = self::COLOR_DEFAULT,
        $debug = false,
        $numberOfColumns = 80,
        $reverse = false
    ) {
        parent::__construct($out, $verbose, $colors, $debug, $numberOfColumns, $reverse);

        $this->prettifier = new NamePrettifier();
        $this->colorizer = new Colorizer($this->colors);
    }

    public function startTest(Test $test)
    {
        $className = $this->prettifier->prettifyTestClass(get_class($test));

        $testName = '';
        if ($test instanceof TestCase || $test instanceof PhptTestCase) {
            $testName = $this->prettifier->prettifyTestMethod($test->getName());
        }

        $this->currentTestResult = new FancyTestResult(
            $this->colorizer,
            $this->currentTestResult ? $this->currentTestResult->getClassUnderTest() : null,
            $className,
            $testName
        );

        parent::startTest($test);
    }

    public function endTest(Test $test, $time): void
    {
        parent::endTest($test, $time);
        
        if (!$test instanceof TestCase && !$test instanceof PhptTestCase) {
            return;
        }

        $this->currentTestResult->setRuntime($time);

        if (!$this->currentTestResult->isTestSuccessful()) {
            $this->nonSuccessfulTestResults[] = $this->currentTestResult;
        }

        $this->write($this->currentTestResult->toString($this->verbose));
    }

    public function addError(Test $test, Exception $e, $time)
    {
        $this->currentTestResult->fail(
            $this->colorizer->colorize('✘', Colorizer::COLOR_YELLOW),
            (string) $e
        );
    }

    public function addWarning(Test $test, Warning $e, $time)
    {
        $this->currentTestResult->fail(
            $this->colorizer->colorize('✘', Colorizer::COLOR_YELLOW),
            (string) $e
        );
    }

    public function addFailure(Test $test, AssertionFailedError $e, $time)
    {
        $this->currentTestResult->fail(
            $this->colorizer->colorize('✘', Colorizer::COLOR_RED),
            (string) $e
        );
    }

    public function addIncompleteTest(Test $test, Exception $e, $time)
    {
        $this->currentTestResult->fail(
            $this->colorizer->colorize('∅', Colorizer::COLOR_YELLOW),
            (string) $e,
            true
        );
    }

    public function addRiskyTest(Test $test, Exception $e, $time)
    {
        $this->currentTestResult->fail(
            $this->colorizer->colorize('☢', Colorizer::COLOR_YELLOW),
            (string) $e,
            true
        );
    }

    public function addSkippedTest(Test $test, Exception $e, $time)
    {
        $this->currentTestResult->fail(
            $this->colorizer->colorize('→', Colorizer::COLOR_YELLOW),
            (string) $e,
            true
        );
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

        $this->printNonSuccessfulTestsSummary($result->count());

        $this->printFooter($result);
    }

    public function printNonSuccessfulTestsSummary(int $numberOfExecutedTests): void
    {
        $numberOfNonSuccessfulTests = count($this->nonSuccessfulTestResults);
        if ($numberOfNonSuccessfulTests === 0) {
            return;
        }

        if (($numberOfNonSuccessfulTests / $numberOfExecutedTests) >= 0.7) {
            return;
        }

        $this->write("Summary of non-successful tests:\n\n");

        foreach ($this->nonSuccessfulTestResults as $testResult) {
            $this->write($testResult->toString($this->verbose));
        }
    }
}
