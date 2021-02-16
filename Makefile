CODECEPT  = composer/bin/codecept
RESOURCES = $(shell find resources -type f)

# build all needed files
build: composer webpack-prod

# remove all generated files
clean: clean-composer clean-npm clean-webpack clean-doc

composer: composer/composer/installed.json

composer-dev: $(CODECEPT)

composer/composer/installed.json: composer.json composer.lock
	composer install --no-dev
	@touch $@

$(CODECEPT): composer.json composer.lock
	composer install
	@touch $@

clean-composer:
	rm -rf composer

npm: node_modules/.package-lock.json

node_modules/.package-lock.json: package.json package-lock.json
	npm install --no-save

clean-npm:
	rm -rf node_modules

webpack-dev: .webpack.dev

webpack-prod: .webpack.prod

webpack-watch: npm
	npm run webpack-watch

wds: npm
	npm run wds

.webpack.dev: node_modules/.package-lock.json $(RESOURCES)
	@rm -f .webpack.prod
	npm run webpack-dev
	@touch $@

.webpack.prod: node_modules/.package-lock.json $(RESOURCES)
	@rm -f .webpack.dev
	npm run webpack-prod
	@touch $@

clean-webpack:
	@rm -f .webpack.dev .webpack.prod
	rm -rf public/assets/javascripts/*.js
	rm -rf public/assets/javascripts/*.js.map
	rm -rf public/assets/stylesheets/*.css
	rm -rf public/assets/stylesheets/*.css.map

doc: force_update
	doxygen Doxyfile

clean-doc:
	rm -rf doc/html

test: test-unit

test-functional: $(CODECEPT)
	$(CODECEPT) run functional

test-jsonapi: $(CODECEPT)
	$(CODECEPT) run jsonapi

test-unit: $(CODECEPT)
	$(CODECEPT) run unit

# dummy target to force update of "doc" target
force_update:
