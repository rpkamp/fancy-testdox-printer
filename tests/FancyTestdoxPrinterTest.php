<?php

namespace rpkamp;

use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Warning;

class FancyTestdoxPrinterTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_print_the_class_name_of_test_class()
    {
        $printer = new TestableFancyTestdoxPrinter();
        $printer->startTest($this);
        $printer->endTest($this, 0);

        $this->assertStringStartsWith('rpkamp\FancyTestdoxPrinter', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_print_testdox_version_of_test_method()
    {
        $printer = new TestableFancyTestdoxPrinter();
        $printer->startTest($this);
        $printer->endTest($this, 0.001);

        $this->assertContains('It should print testdox version of test method', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_print_check_mark_on_passed_test()
    {
        $printer = new TestableFancyTestdoxPrinter();
        $printer->startTest($this);
        $printer->endTest($this, 0.001);

        $this->assertContains('✔', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_not_print_additional_information_on_test_passed()
    {
        $printer = new TestableFancyTestdoxPrinter();
        $printer->startTest($this);
        $printer->endTest($this, 0.001);

        $this->assertNotContains('│', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_print_cross_on_test_with_error()
    {
        $printer = new TestableFancyTestdoxPrinter();
        $printer->startTest($this);
        $printer->addError($this, new Exception(), 0);
        $printer->endTest($this, 0.001);

        $this->assertContains('✘', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_print_additional_information_on_test_with_error()
    {
        $printer = new TestableFancyTestdoxPrinter();
        $printer->startTest($this);
        $printer->addError($this, new Exception(), 0);
        $printer->endTest($this, 0.001);

        $this->assertContains('│', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_print_cross_on_test_with_warning()
    {
        $printer = new TestableFancyTestdoxPrinter();
        $printer->startTest($this);
        $printer->addWarning($this, new Warning(), 0);
        $printer->endTest($this, 0.001);

        $this->assertContains('✘', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_print_additional_information_on_test_with_warning()
    {
        $printer = new TestableFancyTestdoxPrinter();
        $printer->startTest($this);
        $printer->addWarning($this, new Warning(), 0);
        $printer->endTest($this, 0.001);

        $this->assertContains('│', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_print_cross_on_test_with_failure()
    {
        $printer = new TestableFancyTestdoxPrinter();
        $printer->startTest($this);
        $printer->addFailure($this, new AssertionFailedError(), 0);
        $printer->endTest($this, 0.001);

        $this->assertContains('✘', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_print_additional_information_on_test_with_failure()
    {
        $printer = new TestableFancyTestdoxPrinter();
        $printer->startTest($this);
        $printer->addFailure($this, new AssertionFailedError(), 0);
        $printer->endTest($this, 0.001);

        $this->assertContains('│', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_print_empty_set_symbol_on_incomplete_test()
    {
        $printer = new TestableFancyTestdoxPrinter();
        $printer->startTest($this);
        $printer->addIncompleteTest($this, new Exception(), 0);
        $printer->endTest($this, 0.001);

        $this->assertContains('∅', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_not_print_additional_information_on_incomplete_test_when_not_verbose()
    {
        $printer = new TestableFancyTestdoxPrinter();
        $printer->startTest($this);
        $printer->addIncompleteTest($this, new Exception(), 0);
        $printer->endTest($this, 0.001);

        $this->assertNotContains('│', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_print_additional_information_on_incomplete_test_when_verbose()
    {
        $printer = new TestableFancyTestdoxPrinter(null, true);
        $printer->startTest($this);
        $printer->addIncompleteTest($this, new Exception('E_X_C_E_P_T_I_O_N'), 0);
        $printer->endTest($this, 0.001);

        $this->assertContains('│', $printer->getBuffer());
        $this->assertContains('E_X_C_E_P_T_I_O_N', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_print_radioactive_symbol_on_test_risky()
    {
        $printer = new TestableFancyTestdoxPrinter();
        $printer->startTest($this);
        $printer->addRiskyTest($this, new Exception(), 0);
        $printer->endTest($this, 0.001);

        $this->assertContains('☢', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_not_print_additional_information_on_risky_test_when_not_verbose()
    {
        $printer = new TestableFancyTestdoxPrinter();
        $printer->startTest($this);
        $printer->addRiskyTest($this, new Exception(), 0);
        $printer->endTest($this, 0.001);

        $this->assertNotContains('│', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_print_additional_information_on_risky_test_when_verbose()
    {
        $printer = new TestableFancyTestdoxPrinter(null, true);
        $printer->startTest($this);
        $printer->addRiskyTest($this, new Exception(), 0);
        $printer->endTest($this, 0.001);

        $this->assertContains('│', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_print_arrow_on_skipped_test()
    {
        $printer = new TestableFancyTestdoxPrinter();
        $printer->startTest($this);
        $printer->addSkippedTest($this, new Exception(), 0);
        $printer->endTest($this, 0.001);

        $this->assertContains('→', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_not_print_additional_information_on_skipped_test_when_not_verbose()
    {
        $printer = new TestableFancyTestdoxPrinter();
        $printer->startTest($this);
        $printer->addSkippedTest($this, new Exception(), 0);
        $printer->endTest($this, 0.001);

        $this->assertNotContains('│', $printer->getBuffer());
    }

    /**
     * @test
     */
    public function it_should_print_additional_information_on_skipped_test_when_verbose()
    {
        $printer = new TestableFancyTestdoxPrinter(null, true);
        $printer->startTest($this);
        $printer->addSkippedTest($this, new Exception('S_K_I_P_P_E_D'), 0);
        $printer->endTest($this, 0.001);

        $this->assertContains('│', $printer->getBuffer());
        $this->assertContains('S_K_I_P_P_E_D', $printer->getBuffer());
    }
}
