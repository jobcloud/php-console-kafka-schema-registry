.PHONY: clean code-check fix-code-style test coverage help install-dependencies code-style static-analysis ci-static-analysis infection-testing pcov-disable pcov-enable update-dependencies
.DEFAULT_GOAL := test

INFECTION = ./vendor/bin/infection
PHPUNIT = ./vendor/bin/phpunit -c ./phpunit.xml
PHPSTAN = ./vendor/bin/phpstan --no-progress
PHPCS = ./vendor/bin/phpcs
PHPCBF = ./vendor/bin/phpcbf
COVCHK = ./vendor/bin/coverage-check

clean:
	rm -rf ./vendor ./build

code-check:
	${PHPCS}
	${PHPSTAN}

fix-code-style:
	${PHPCBF}

code-style:
	mkdir -p build/logs/phpcs
	${PHPCS}

static-analysis:
	mkdir -p build/logs/phpstan
	${PHPSTAN} analyse

ci-static-analysis:
	mkdir -p build/logs/phpstan
	${PHPSTAN} analyse --error-format=junit | tee build/logs/phpstan/junit.xml
	${PHPSTAN} analyse

test:
	${PHPUNIT} --no-coverage

coverage:
	${PHPUNIT} && ${COVCHK} build/logs/phpunit/coverage/coverage.xml 100

infection-testing:
	make coverage
	cp -f build/logs/phpunit/junit.xml build/logs/phpunit/coverage/junit.xml
	sudo php-ext-disable pcov
	${INFECTION} --coverage=build/logs/phpunit/coverage --min-msi=86 --threads=`nproc`
	sudo php-ext-enable pcov

install-dependencies:
	composer install

update-dependencies:
	composer update

pcov-enable:
	sudo php-ext-enable pcov

pcov-disable:
	sudo php-ext-disable pcov

help:
	# Usage:
	#   make <target> [OPTION=value]
	#
	# Targets:
	#   clean                   Cleans the coverage and the vendor directory
	#   code-check              For Developer machine, to check code style using phpcs & Code analysis
	#   code-fix                For Developer machine, to fix code-style automatcially using phpcbf
	#   code-style              Check code style using phpcs
	#   coverage                Code Coverage display
	#   help                    You're looking at it!
	#   install-dependencies    Install dependencies
	#   update-dependencies     Run composer update
	#   static-analysis         Run static analysis using phpstan
	#   ci-static-analysis      Run static analysis using phpstan for CI only.
	#   test                    Run tests
	#   pcov-enable             Enable pcov
	#   pcov-disable            Disable pcov
