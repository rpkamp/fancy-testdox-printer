# ⛔ This package is deprecated, upgrade to PHPUnit 7+ and use `phpunit --testdox` instead! ⛔

# Fancy Testdox Printer [![Build Status](https://travis-ci.org/rpkamp/fancy-testdox-printer.svg?branch=master)](https://travis-ci.org/rpkamp/fancy-testdox-printer)

A fancy testdox printer for PHPUnit. Output is similar to PHPUnit's `--testdox` output, but:

- Tests are more clearly marked as failing/passing by colored<sup>1</sup> symbols
- Failures/errors etc are shown inline instead of at the end of the test run for more clear and early feedback

## PHPUnit compatibility

| Major version | Support |
| --- | --- |
| PHPUnit 5 | Not supported |
| PHPUnit 6 | Supported until February 2019 |
| PHPUnit 7+ | N/A, use built-in `--testdox` option |

This printer is compatible with **PHPUnit 6 only**. PHPUnit 5 support has ended, so there are no plans to add support for it in this package.

In **PHPUnit 7** the default testdox printer has been replaced with this printer. Therefore there is no version of this package that supports PHPUnit 7, nor will there ever be, as it is not needed.
To obtain the same output in PHPUnit 7 run `phpunit --testdox`. If you also want to see the time each test took run `phpunit --testdox -v`.

During the support period for PHPUnit 6, which ends February 2019, this printer will receive support as well, but after that it will be abandoned.

## Legend

| Symbol | Color | Meaning |
| --- | --- | --- |
| ✔ | green | test passed |
| ✘ | red | assertion failed |
| ✘ | yellow | PHPUnit error or warning |
| ∅ | yellow | incomplete test |
| ☢ | yellow | risky test |
| → | yellow | skipped test |

When less than 70% of the tests fail it will show a summary of errors at the end of the output. 70% is a guess and may change in the future, but it seemed like a nice cut off point that you have so much noise in the output already you don't need any more noise. (this was introduced in version 0.2.0)

## Installation
On the command line run

```
composer require rpkamp/fancy-testdox-printer --dev
```

to install this package as a development dependency

## Usage
To use this printer you can either pass a command line argument to PHPUnit or edit `phpunit.xml`

### Command line
On the command line run

```
vendor/bin/phpunit --printer "rpkamp\FancyTestdoxPrinter"
```

### phpunit.xml
In `phpunit.xml` add `printerClass="rpkamp\FancyTestdoxPrinter"` to the `phpunit` tag (see [`phpunit.xml`][phpunitxml] for an example).

## Example output

The output of the test suite for this project using itself as a printer looks as follows:

![Own test suite](images/testsuite.png)

(run `vendor/bin/phpunit` to obtain this output)

A possible output with failures, errors, etc looks as follows:

![Example output](images/example.png)

(run `vendor/bin/phpunit --configuration phpunit.example.xml -v` to obtain this output)

Note that without `-v` the risky, incomplete and skipped tests don't print additional information

[phpunitxml]: https://github.com/rpkamp/fancy-testdox-printer/blob/master/phpunit.xml

<sup>1</sup> In case your terminal supports this and you have it enabled in PHPUnit
