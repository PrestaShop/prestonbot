# PrestonBot

This application is based on Symfony CarsonBot and aims to help PrestaShop maintainers and contributors.

![Preston, the PrestaShop contributor best friend](http://i.imgur.com/r26gJW4.png)

### Classic installation

First of all you have to configure your GitHub repository and have a GitHub token.

```bash
composer install // and complete the interactive fields asked
```

### Docker installation

First, setup the `docker-compose.yml` file with a valid GitHub token and a valid Secure token (can be empty).

```bash
make start
```

The Home page is now available at "http://localhost:81/".

You need also to create your own GitHub [personal token](https://github.com/settings/tokens) and export it:

```bash
export SYMFONY_PHPUNIT_VERSION=5.7
export GH_TOKEN=XXXXXXXXXXXXXXXXXXXXXXXXXXXX
export GH_SECURED_TOKEN=YYYYYYYYYYYYYYYYYYYYYYYYYYYY
```

## How to run the test suite ?

```bash
./vendor/bin/simple-phpunit
# or (using docker)
make test
```

> To launch unit tests, you only need to setup your own Github token (`GH_TOKEN`).

## Our standards ?

Yeah, mostly the *Symfony* ones:

```bash
./vendor/bin/php-cs-fixer fix # we use the Symfony level + short array notation filter
```

## What is Preston capable of doing?

* Comment on a pull request to help a contributor fix his work;
* Extract data from the pull request and look for some terms;
* Manage labels;
* Validate a pull request description;
* Welcome every new contributor;
* Labelize a PR regarding information in description
* Labelize a PR regarding files updated
* Add labels according to the branch
