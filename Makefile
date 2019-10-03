.PHONY: clean fix-code-style test coverage install-dependencies code-style static-analysis xdebug-disable xdebug-enable update-dependencies
.DEFAULT_GOAL := test

PHPDBG = phpdbg -qrr ./vendor/bin/phpunit -c ./phpunit.xml
PHPUNIT = ./vendor/bin/phpunit -c ./phpunit.xml
PHPSTAN = ./vendor/bin/phpstan analyse src --level=7
PHPCS = ./vendor/bin/phpcs ./src --extensions=php --report-full --report-gitblame --standard=PSR2 --exclude=Generic.Commenting.Todo
PHPCBF = ./vendor/bin/phpcbf ./src --standard=./vendor/jobcloud/unity-coding-standards/ruleset.xml

clean:
	rm -rf ./vendor

code-check:xdebug-disable
	${PHPCS}
	${PHPSTAN}

fix-code-style:xdebug-disable
	${PHPCBF}

code-style:xdebug-disable
	mkdir -p build/logs/phpcs
	${PHPCS} --report-junit=build/logs/phpcs/junit.xml

static-analysis: xdebug-disable
	mkdir -p build/logs/phpstan
	${PHPSTAN} --no-progress

ci-static-analysis: xdebug-disable
	mkdir -p build/logs/phpstan
	${PHPSTAN} --no-progress --error-format=junit | tee build/logs/phpstan/junit.xml
	${PHPSTAN} --no-progress

test:xdebug-disable
	${PHPUNIT}

coverage:xdebug-disable
	${PHPDBG}

install-dependencies:xdebug-disable
	composer install

update-dependencies:xdebug-disable
	composer update

xdebug-disable:
	sudo php-ext-disable xdebug

xdebug-enable:
	sudo php-ext-enable xdebug

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
	#   xdebug-enable           Enable xdebug
	#   xdebug-disable          Disable xdebug
