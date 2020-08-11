-include .env

ifndef PHP_BINARY
	PHP_BINARY=php
endif

###> symfony/framework-bundle ###
CONSOLE := $(PHP_BINARY) $(shell which bin/console)
sf_console:
ifndef CONSOLE
	@printf "Run \033[32mcomposer require cli\033[39m to install the Symfony console.\n"
endif

cache-clear:
ifdef CONSOLE
	@$(CONSOLE) cache:clear --no-warmup
else
	@rm -rf var/cache/*
endif
.PHONY: cache-clear

cache-warmup: cache-clear
ifdef CONSOLE
	@$(CONSOLE) cache:warmup
else
	@printf "Cannot warm up the cache (needs symfony/console)\n"
endif
.PHONY: cache-warmup

serve_as_sf: sf_console
ifndef CONSOLE
	@${MAKE} serve_as_php
endif
	@$(CONSOLE) | grep server:start > /dev/null || ${MAKE} serve_as_php
	@$(CONSOLE) server:start

	@printf "Quit the server with \033[32;49mbin/console server:stop\033[39m\n"

serve_as_php:
	@printf "\033[32;49mServer listening on http://0.0.0.0:8000\033[39m\n";
	@printf "Quit the server with CTRL-C.\n"
	@printf "Run \033[32mcomposer require symfony/web-server-bundle\033[39m for a better web server\n"
	php -S 0.0.0.0:8000 -t public

serve:
	@${MAKE} serve_as_sp
.PHONY: sf_console serve serve_as_sf serve_as_php
###< symfony/framework-bundle ###

cc:
	make cache-clear

clean:
	test ! -f .env
	rm -rf node_modules vendor var/cache/* var/log/* public/build/* public/bundles public/upload/default public/upload/private

build:
	test -f .env
	$(PHP_BINARY) $(shell which composer) install --no-scripts
	@$(CONSOLE) doctrine:database:create --if-not-exists
	@$(CONSOLE) assets:install
	@$(CONSOLE) doctrine:migrations:migrate -n
	@$(CONSOLE) do:fi:lo --append --group=sonataClassification --group=userAdmin --group=demo --group=baseData
	npm i
	npm run dev

install:
	test -f .env
	make build
	@$(CONSOLE) ckeditor:install --clear=skip
	@$(CONSOLE) assets:install --symlink

db_diff:
	@$(CONSOLE) doctrine:schema:update --dump-sql

db_generate_migration:
	@$(CONSOLE) doctrine:migrations:diff

db_migrate:
	@$(CONSOLE) doctrine:migrations:migrate -n

db_reset:
	@$(CONSOLE) doctrine:database:drop --force
	@$(CONSOLE) doctrine:database:create --if-not-exists
	@$(CONSOLE) doctrine:migrations:migrate -n
	@$(CONSOLE) do:fi:lo --append --group=sonataClassification --group=userAdmin --group=demo --group=baseData

run:
	@$(CONSOLE) server:run > /dev/null & npm run dev-server --hot
