# "Bot/social" features

## Pull request description parser

PrestonBot is able to check the pull request description if any and validate against defined business rules.
This allow both maintainers and contributors to review the pull request.

## Pull request commit name parser

PrestonBot is able to check every commit of a pull request. As we have some guidelines regarding the name of 
commit, we can help the contributor to respect them.

## Git diff static analyser

PrestonBot is able to check the git diff of a pull request and react to help both contributors and maintainers.
For instance, we use it to inform our Translation team leader when a translation have been added or updated in PrestaShop.

# Command Line features

## Activity report

PrestonBot send activity report of pull requests, ordered by date of last update and label.
You can configure groups of users and emails, see `app/config/parameters.yml.dist` file.
