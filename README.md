# PrestonBot

This application is based on Symfony CarsonBot and aims to help PrestaShop maintainers and contributors

![Preston, the PrestaShop contributor best friend](http://i.imgur.com/r26gJW4.png)

*Features to come:*

- [ ] Send a welcome message :)
- [X] Check pull request template
- [ ] Send an "How to rebase ?" message
- [ ] Reveal hot pull requests by email (old pull requests that are valid)
- [ ] Check if commit label respect our Coding standards
- [ ] Regarding information on PR table, check the commit label and the related branch
- [X] Send mails to a group of persons according to the labels (ex: notify PM or QA)
- [ ] A great UI/UX because the current UI/UX sucks!
- [X] Move Twig to CommentApi, implement sendMessage($string) and sendTemplate($templateName, array $params)
- [X] List all pull requests according to some tags and last update date
- [ ] How to check "mergeability" of a pull request ?
- [ ] Get [PrestaShop labels](https://github.com/PrestaShop/PrestaShop/labels)
- [ ] Define a **workflow** with Xavier & Julien about how we merge
- [ ] Keep the previous item *SIMPLE*
- [X] Migrate to Symfony 3
- [X] ~~Delete all useless dependencies and remove `symfony/symfony` metapackage~~ (no need thanks to Nicolas Grekas)
- [X] Mailing system
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