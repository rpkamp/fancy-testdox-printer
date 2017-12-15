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
    public static $phpUnitIntegrationWarningHandled = false;

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

    /**
     * @var string|null
     */
    private $previousClassUnderTest;

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

        if (!static::$phpUnitIntegrationWarningHandled) {
            if (!class_exists(\PHPUnit\Util\TestDox\CliTestDoxPrinter::class)) {
                $warning = <<<WARNING
 
 !!! WARNING !!!
 rpkamp/fancy-testdox-printer is deprecated as it is part of PHPUnit now.
 You can remove rpkamp/fancy-testdox-printer and simply run `phpunit --testdox` to
 obtain the same output.
 If you are configuring PHPUnit with a printer class you can use the
 PHPUnit\Util\TestDox\CliTestDoxPrinter class.
 
 
WARNING;
                echo $this->colorizer->colorize($warning, Colorizer::COLOR_YELLOW);
            }
            static::$phpUnitIntegrationWarningHandled = true;
        }
    }

    public function startTest(Test $test)
    {
        $this->previousClassUnderTest = $this->currentTestResult
            ? $this->currentTestResult->getClassUnderTest()
            : null;

        $className = $this->prettifier->prettifyTestClass(get_class($test));

        $testName = '';
        if ($test instanceof TestCase || $test instanceof PhptTestCase) {
            $testName = $this->prettifier->prettifyTestMethod($test->getName());
        }

        $this->currentTestResult = new FancyTestResult(
            $this->colorizer,
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

        $this->write($this->currentTestResult->toString($this->previousClassUnderTest, $this->verbose));
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

        $previousClassUnderTest = null;
        foreach ($this->nonSuccessfulTestResults as $testResult) {
            $this->write($testResult->toString($previousClassUnderTest, $this->verbose));
            $previousClassUnderTest = $testResult->getClassUnderTest();
        }
    }
}
