build: webpack-prod

install: force_update
	composer install --no-dev

install-dev: force_update
	npm install
	svn revert package-lock.json
	composer install --no-dev

doc: force_update
	doxygen Doxyfile

test: test-unit

test-functional:
	composer/bin/codecept run functional

test-jsonapi:
	composer/bin/codecept run jsonapi

test-unit:
	composer/bin/codecept run unit

webpack-dev: force_update
	npm run webpack-dev

webpack-prod: force_update
	npm run webpack-prod

webpack-watch: force_update
	npm run webpack-watch

wds: force_update
	npm run wds

# dummy target to force update of "doc" target
force_update:
