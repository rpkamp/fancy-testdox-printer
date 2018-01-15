<?php

namespace rpkamp;

use Exception;
use PHP_Timer;
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
     * @var FancyTestResult
     */
    private $previousTestResult;

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

        if (!static::$phpUnitIntegrationWarningHandled) {
            if (class_exists(\PHPUnit\Util\TestDox\CliTestDoxPrinter::class)) {
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
        if (!$test instanceof TestCase && !$test instanceof PhptTestCase) {
            return;
        }

        $class = get_class($test);
        if ($test instanceof TestCase) {
            $annotations = $test->getAnnotations();

            if (isset($annotations['class']['testdox'][0])) {
                $className = $annotations['class']['testdox'][0];
            } else {
                $className = $this->prettifier->prettifyTestClass($class);
            }

            if (isset($annotations['method']['testdox'][0])) {
                $testMethod = $annotations['method']['testdox'][0];
            } else {
                $testMethod = $this->prettifier->prettifyTestMethod($test->getName(false));
            }

            $dataDescription = $test->dataDescription();
            if ($dataDescription) {
                if (is_int($dataDescription)) {
                    $testMethod .= sprintf(' with data set #%d', $dataDescription);
                } else {
                    $testMethod .= sprintf(' with data set "%s"', $dataDescription);
                }
            }
        } elseif ($test instanceof PhptTestCase) {
            $className  = $class;
            $testMethod = $test->getName();
        }

        $this->currentTestResult = new FancyTestResult(
            $this->colorizer,
            $className,
            $testMethod
        );

        parent::startTest($test);
    }

    public function endTest(Test $test, $time): void
    {
        if (!$test instanceof TestCase && !$test instanceof PhptTestCase) {
            return;
        }

        parent::endTest($test, $time);

        $this->currentTestResult->setRuntime($time);

        $this->write($this->currentTestResult->toString($this->previousTestResult, $this->verbose));

        $this->previousTestResult = $this->currentTestResult;

        if (!$this->currentTestResult->isTestSuccessful()) {
            $this->nonSuccessfulTestResults[] = $this->currentTestResult;
        }
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

    protected function printHeader(): void
    {
        $this->write("\n" . PHP_Timer::resourceUsage() . "\n\n");
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

        $previousTestResult = null;
        foreach ($this->nonSuccessfulTestResults as $testResult) {
            $this->write($testResult->toString($previousTestResult, $this->verbose));
            $previousTestResult = $testResult;
        }
    }
}
