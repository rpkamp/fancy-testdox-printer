test:
	vendor/bin/phpcs --standard=PSR2 src/
	vendor/bin/phpunit
.PHONY: test
