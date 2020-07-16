######  symfony flex + sonata starter kit

This kit includes the essentials to get you started with a sonata-based project:
- symfony 4.4
- symfony encore (webpack) with es6 + sass support
- sonata admin + fundamental packages
- jms job queue bundle
- friends of symfony packages: user, rest
- carbon
- doctrine with migrations and fixtures, gedmo extensions (softdelete, etc)
- src/Core contains some fabricated solutions and patches for sonata bundles
- directory structure supports creation of custom bundles
- fully configured

Tested on php 7.3.19, node 12, mysql 5.7.

# Instructions:
- created .env file from .env.dist
- execute `make install` (will run composer, npm, all the necessary scripts)
- execute `make run` (will start both the php and the webpack hot reload server)

Use the url of the php server to access to project, usually http://localhost:8000

Press ctrl+c to stop the server processes.

The admin interface is accessible at `/admin` with the following credentials:

`admin` / `admin` (make sure to change it later on).



# Makefile commands:
- `make cc` alias to cache-clear
- `make install` executes all the install scripts necessary to start after creating the working copy
- `make build` use it when chaning branches, to ensure you have the latest package versions, migrations ran, and to have your assets compiled
- `make clean` drop everything except the codebase. do not use it in production! (although it works only if  .env has been deleted already)
- `make db_diff` see the changes you made in your entities
- `make db_generate_migration` generates a migration
- `make db_migrate` executes new migrations
- `make db_reset` drops, re-creates and re-seeds the database
