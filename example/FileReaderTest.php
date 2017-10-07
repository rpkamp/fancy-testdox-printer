<?php

use PHPUnit\Framework\TestCase;

/**
 * This file only serves for a demo output of FancyTestdoxPrinter, and should not
 * be used in production as it is all kinds of broken!
 */
class FileReaderTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_be_instantiated_without_exceptions()
    {
        new FileReader('abc.txt');
    }

    /**
     * @test
     */
    public function it_should_return_filename_it_was_instantiated_with()
    {
        $this->assertEquals('abc.txt', (new FileReader('abc.txt'))->getPath());
    }

    /**
     * @test
     */
    public function it_should_read_line_from_file()
    {
        $this->assertEquals('Hello world', (new FileReader('abc.txt'))->read());
    }

    /**
     * @test
     */
    public function it_should_open_the_file()
    {
        (new FileReader('abc.txt'))->open();
    }

    /**
     * @test
     */
    public function it_should_be_able_to_read_entire_file_at_once()
    {
        $this->markTestIncomplete('This functionality does not exist yet!');
    }

    /**
     * @test
     */
    public function it_should_convert_file_to_utf8()
    {
        $this->markTestSkipped('No suitable utf8 encoder available');
    }
}
