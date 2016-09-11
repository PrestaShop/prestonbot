# Harmony

This application is based on Symfony CarsonBot and aims to help maintainers and contributors work.

*Features to come:*

- [ ] Send a welcome message :)
- [ ] Send an "How to rebase ?" message
- [ ] Reveal hot pull requests by email (old pull requests that are valid)
- [ ] Check if commit label respect our Coding standards
- [ ] Regarding information on PR table, check the commit label and the related branch
- [ ] A great UI/UX because the current UI/UX sucks!
- [ ] How to check "mergeability" of a pull request ?
- [ ] Get project labels
- [ ] Define a **workflow**
- [ ] Keep the previous item *SIMPLE*
- [ ] **Change and delete all used GitHub tokens** before put repository in open source


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
```
