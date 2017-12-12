<?php
declare(strict_types=1);

namespace rpkamp\FancyTestdoxPrinter;

final class TestResult
{
    /**
     * @var Colorizer
     */
    private $colorizer;

    /**
     * @var string|null
     */
    private $previousClassUnderTest;

    /**
     * @var string
     */
    private $classUnderTest;

    /**
     * @var string
     */
    private $testMethod;

    /**
     * @var bool
     */
    private $testSuccesful;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var string
     */
    private $additionalInformation;

    /**
     * @var bool
     */
    private $additionalInformationVerbose;

    /**
     * @var float
     */
    private $runtime;

    public function __construct(
        Colorizer $colorizer,
        $previousClassUnderTest,
        string $classUnderTest,
        string $testMethod
    ) {
        $this->colorizer = $colorizer;
        $this->previousClassUnderTest = $previousClassUnderTest;
        $this->classUnderTest = $classUnderTest;
        $this->testMethod = $testMethod;
        $this->testSuccesful = true;
        $this->symbol = $this->colorizer->colorize('✔', Colorizer::COLOR_GREEN);
        $this->additionalInformation = '';
    }

    public function getClassUnderTest(): string
    {
        return $this->classUnderTest;
    }

    public function isTestSuccessful(): bool
    {
        return $this->testSuccesful;
    }

    public function fail(
        string $symbol,
        string $additionalInformation,
        bool $additionalInformationVerbose = false
    ): void {
        $this->testSuccesful = false;
        $this->symbol = $symbol;
        $this->additionalInformation = $additionalInformation;
        $this->additionalInformationVerbose = $additionalInformationVerbose;
    }

    public function setRuntime(float $runtime): void
    {
        $this->runtime = $runtime;
    }

    public function toString($verbose = false)
    {
        return sprintf(
            "%s %s %s %s\n%s",
            $this->getClassNameHeader(),
            $this->symbol,
            $this->testMethod,
            $this->getFormattedRuntime(),
            $this->getFormattedAdditionalInformation($verbose)
        );
    }

    /**
     * @return string
     */
    public function getClassNameHeader(): string
    {
        $className = '';
        if ($this->classUnderTest !== $this->previousClassUnderTest) {
            if (null !== $this->previousClassUnderTest) {
                $className = "\n";
            }
            $className .= sprintf("%s\n", $this->classUnderTest);
        }
        return $className;
    }

    public function getFormattedRuntime(): string
    {
        if ($this->runtime > 5) {
            return $this->colorizer->colorize(sprintf('[%.2f ms]', $this->runtime * 1000), Colorizer::COLOR_RED);
        }

        if ($this->runtime > 1) {
            return $this->colorizer->colorize(sprintf('[%.2f ms]', $this->runtime * 1000), Colorizer::COLOR_YELLOW);
        }

        return sprintf('[%.2f ms]', $this->runtime * 1000);
    }

    public function getFormattedAdditionalInformation($verbose): string
    {
        if ($this->additionalInformation === '') {
            return '';
        }

        if ($this->additionalInformationVerbose && !$verbose) {
            return '';
        }

        return sprintf(
            "   │\n%s\n\n",
            implode(
                "\n",
                array_map(
                    function (string $text) {
                        return sprintf('   │ %s', $text);
                    },
                    explode("\n", $this->additionalInformation)
                )
            )
        );
    }
}
