# PrestonBot

This application is based on Symfony CarsonBot and aims to help PrestaShop maintainers and contributors

![Preston, the PrestaShop contributor best friend](http://i.imgur.com/r26gJW4.png)

*Features to come:*

- [ ] Send a welcome message :)
- [X] Check pull request template
- [ ] Send an "How to rebase ?" message
- [ ] Reveal hot pull requests by email (old pull requests that are valid)
- [ ] Check is commit label respect our Coding standards
- [ ] Regarding information on PR table, check the commit label and the related branch
- [ ] Generate contributing data that have a meaningful interest
- [ ] Send mails to a group of persons according to the labels (ex: notify PM or QA)
- [ ] A great UI/UX because the current UI/UX sucks!


## How to install ?

First of all you have to configure your GitHub repository and have a GitHub token.

```bash
composer install // and complete the interactive fields asked
```

For now this application allow you to valid pull requests description
according to **PrestaShop** standards.

## How to test ?

```bash
bin/phpunit -c app
```

## Our standards ?

Yeah, the *Symfony* ones:

```bash
bin/php-cs-fixer fix .
```


Todo rapide:

- [X] Déplacer twig dans le commentApi, implementer sendMessage($string) et sendTemplate($templateName, array $params)
- [ ] Implementer le welcome message
- [ ] Système d'analyse des PRS selon mergeabilité et date de dernier update (bootstrap Dashboard)
- [ ] Trouver une solution pour vérifier la mergeability d'une pull request
- [ ] Récupérer les labels de PrestaShop
- [ ] Définir un **workflow** avec Xavier (utiliser le composant workflow ? une autre lib ?)
- [ ] Maintenir l'item précédemment cité SIMPLE
- [ ] Migrer sur Sf3, supprimer tous les composants inutiles et ne plus dépendre du metapackage
- [ ] Systeme d'envoi de mails (ré écrire encore et encore cette putain de classe Mailer)


Roadmap:
=> envoi de mail internes
=> dashboard de tri des PRS
=> gestion labels automatiques
=> welcome message
=> passe de vérification