# PrestonBot

This application is based on Symfony CarsonBot and aims to help PrestaShop maintainers and contributors

![Preston, the PrestaShop contributor best friend](http://i.imgur.com/r26gJW4.png)

## How to install ?

First of all you have to configure your GitHub repository and have a GitHub token.

```bash
composer install // and complete the interactive fields asked
```

For now this application allow you to valid pull requests description
according to **PrestaShop** standards.

## How to test ?

```bash
./vendor/bin/phpunit
```

## Our standards ?

Yeah, the *Symfony* ones:

```bash
./vendor/bin/php-cs-fixer fix .
