.PHONY: clean fix-code-style test coverage install-dependencies code-style static-analysis xdebug-disable xdebug-enable update-dependencies
.DEFAULT_GOAL := test

INFECTION = ./vendor/bin/infection
PHPDBG = phpdbg -qrr ./vendor/bin/phpunit -c ./phpunit.xml
PHPUNIT = ./vendor/bin/phpunit -c ./phpunit.xml
PHPSTAN = ./vendor/bin/phpstan analyse src --level=7
PHPCS = ./vendor/bin/phpcs
PHPCBF = ./vendor/bin/phpcbf ./src --standard=PSR12

clean:
	rm -rf ./vendor

code-check:
	${PHPCS}
	${PHPSTAN}

fix-code-style:
	${PHPCBF}

code-style:
	mkdir -p build/logs/phpcs
	${PHPCS} --report-junit=build/logs/phpcs/junit.xml

static-analysis:
	mkdir -p build/logs/phpstan
	${PHPSTAN} --no-progress

ci-static-analysis:
	mkdir -p build/logs/phpstan
	${PHPSTAN} --no-progress --error-format=junit | tee build/logs/phpstan/junit.xml
	${PHPSTAN} --no-progress

test:
	${PHPUNIT}

coverage:
	${PHPDBG} && ./vendor/bin/coverage-check build/logs/phpunit/coverage/coverage.xml 100

infection-testing:
	make coverage
	cp -f build/logs/phpunit/junit.xml build/logs/phpunit/coverage/junit.xml
	${INFECTION} --coverage=build/logs/phpunit/coverage --min-msi=65 --threads=`nproc`

install-dependencies:
	composer install

update-dependencies:
	composer update

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
