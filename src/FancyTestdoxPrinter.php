<?php

namespace rpkamp;

use SebastianBergmann\Timer\Timer;
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
    }

    public function startTest(Test $test): void
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

    public function endTest(Test $test, float $time): void
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

    public function addError(Test $test, \Throwable $t, float $time): void
    {
        $this->currentTestResult->fail(
            $this->colorizer->colorize('✘', Colorizer::COLOR_YELLOW),
            (string) $t
        );
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->currentTestResult->fail(
            $this->colorizer->colorize('✘', Colorizer::COLOR_YELLOW),
            (string) $e
        );
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->currentTestResult->fail(
            $this->colorizer->colorize('✘', Colorizer::COLOR_RED),
            (string) $e
        );
    }

    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
        $this->currentTestResult->fail(
            $this->colorizer->colorize('∅', Colorizer::COLOR_YELLOW),
            (string) $t,
            true
        );
    }

    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
        $this->currentTestResult->fail(
            $this->colorizer->colorize('☢', Colorizer::COLOR_YELLOW),
            (string) $t,
            true
        );
    }

    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
        $this->currentTestResult->fail(
            $this->colorizer->colorize('→', Colorizer::COLOR_YELLOW),
            (string) $t,
            true
        );
    }

    public function writeProgress($progress): void
    {
        // NOOP, block normal behavior of \PHPUnit\TextUI\ResultPrinter
    }

    public function flush(): void
    {
    }

    public function printResult(TestResult $result): void
    {
        $this->printHeader();

        $this->printNonSuccessfulTestsSummary($result->count());

        $this->printFooter($result);
    }

    protected function printHeader(): void
    {
        $this->write("\n" . Timer::resourceUsage() . "\n\n");
    }

    public function printNonSuccessfulTestsSummary(int $numberOfExecutedTests)
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
