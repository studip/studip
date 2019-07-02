CODECEPT_VENDOR = $(shell which composer/bin/codecept)
CODECEPT = $(shell which codecept)

ifneq ($(wildcard $(CODECEPT_VENDOR)),)
	RUN_TESTS = $(CODECEPT_VENDOR) run unit
else ifneq ($(wildcard $(CODECEPT)),)
	RUN_TESTS = $(CODECEPT) run unit
else
	RUN_TESTS = phpunit -c tests/phpunit.xml
endif

install: force_update
	composer install --no-dev

install-dev: force_update
	npm install
	svn revert package-lock.json
	composer install --no-dev

build: webpack-prod

doc: force_update
	doxygen Doxyfile

test: force_update
	$(RUN_TESTS)

webpack-dev: force_update
	npm run webpack-dev

webpack-prod: force_update
	npm run webpack-prod

wds: force_update
	npm run wds

# dummy target to force update of "doc" target
force_update:
