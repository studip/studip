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
	cli/cleanup-assets.php --before
	npm run webpack-dev
	cli/cleanup-assets.php --after

webpack-prod: force_update
	cli/cleanup-assets.php --before
	npm run webpack-prod
	cli/cleanup-assets.php --after

webpack-watch: force_update
	cli/cleanup-assets.php --before
	npm run webpack-watch
	cli/cleanup-assets.php --after

wds: force_update
	npm run wds

# dummy target to force update of "doc" target
force_update:
