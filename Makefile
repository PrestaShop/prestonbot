# Makefile for docker use

APP=docker-compose exec -T app
TAPP=docker-compose exec
TTAPP=docker-compose exec -e APP_ENV=test
CAPP=docker-compose run app composer
CONSOLE=$(APP) /usr/local/bin/php bin/console

.PHONY: help install start stop destroy composer console app nginx test cs server

help:           ## Show this help
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

install:        ## Setup the project using Docker and docker-compose
install: start composer-install

start:          ## Start the containers
	docker-compose up -d

stop:           ## Stop the Docker containers and remove the volumes
	docker-compose down -v

destroy:        ## Destroy all containers, volumes, networks
	docker-compose down --rmi all

composer:       ## Composer
	$(CAPP) $(filter-out $@,$(MAKECMDGOALS))

composer-install:  # Install the project PHP dependencies
	$(CAPP) install -o

console:        ## Console
	$(CONSOLE) $(filter-out $@,$(MAKECMDGOALS))

app:            ## Shell of Application container
	$(TAPP) app sh

nginx:          ## Shell of Nginx container
	$(TAPP) nginx sh

test:           ## Launch tests
	$(TAPP) app env APP_ENV=test ./vendor/bin/simple-phpunit

cs:             ## Fix Coding styles
	$(TAPP) app ./vendor/bin/php-cs-fixer fix

server:         ## Start local PHP server (Non docker use only)
	php -S localhost:8888 -t web

%:
@: