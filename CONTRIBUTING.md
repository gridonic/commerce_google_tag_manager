# Developing on Commerce Google Tag Manager

- Issues should be filed at
  https://www.drupal.org/project/issues/commerce_google_tag_manager
- Pull requests can be made against
  https://github.com/gridonic/commerce_google_tag_manager/pulls

## 📦 Repositories

Github repo

```bash
git remote add github git@github.com:gridonic/commerce_google_tag_manager.git
```

Drupal repo

```bash
git remote add drupal git@git.drupal.org:project/commerce_google_tag_manager.git
```

## 🔧 Prerequisites

First of all, you will need to have the following tools installed
globally on your environment:

- drush
- Latest dev release of Drupal 8.x.
- docker
- docker-compose

### Project bootstrap

Once run, you will be able to access to your fresh installed Drupal on `localhost::8888`.

    docker-compose build --pull --build-arg BASE_IMAGE_TAG=8.9 drupal
    (get a coffee, this will take some time...)
    docker-compose up --build -d drupal
    docker-compose exec -u www-data drupal drush site-install standard --db-url="mysql://drupal:drupal@db/drupal" --site-name=Example -y
    
    # You may be interesed by reseting the admin passowrd of your Docker and install the module using those cmd.
    docker-compose exec drupal drush user:password admin admin
    docker-compose exec drupal drush en commerce_google_tag_manager

## 🏆 Tests

We use the [Docker for Drupal Contrib images](https://hub.docker.com/r/wengerk/drupal-for-contrib) to run testing on our project.

Run testing by stopping at first failure using the following command:

    docker-compose exec -u www-data drupal phpunit --group=commerce_google_tag_manager --no-coverage --stop-on-failure

## 🚔 Check Drupal coding standards & Drupal best practices

You need to run composer before using PHPCS. Then register the Drupal
and DrupalPractice Standard with PHPCS:

```bash
`./vendor/bin/phpcs --config-set installed_paths \
`pwd`/vendor/drupal/coder/coder_sniffer`
```

### Command Line Usage

Check Drupal coding standards:

```bash
./vendor/bin/phpcs --standard=Drupal --colors \
--extensions=php,module,inc,install,test,profile,theme,info,md \
--ignore=*/vendor/*,*/node_modules/*,*/scripts/* --encoding=utf-8 ./
```

Check Drupal best practices:

```bash
./vendor/bin/phpcs --standard=DrupalPractice --colors \
--extensions=php,module,inc,install,test,profile,theme,info,md \
--ignore=*/vendor/*,*/node_modules/*,*/scripts/* --encoding=utf-8 ./
```

Automatically fix coding standards

```bash
./vendor/bin/phpcbf --standard=Drupal --colors \
--extensions=php,module,inc,install,test,profile,theme,info \
--ignore=*/vendor/*,*/node_modules/*,*/scripts/* ./
```

### Improve global code quality using PHPCPD PHPMD, & PHPCF

Add requirements if necessary using `composer`:

```bash
composer install
```

Detect overcomplicated expressions & Unused parameters, methods, properties

```bash
./vendor/bin/phpmd ./ text ./phpmd.xml --suffixes \
php,module,inc,install,test,profile,theme,info,txt \
--exclude vendor,scripts
```

Copy/Paste Detector

```bash
./vendor/bin/phpcpd ./ \
--names=*.php,*.module,*.inc,*.install,*.test,*.profile,*.theme,*.info,*.txt \
--names-exclude=*.md,*.info.yml --progress --ansi \
--exclude=scripts --exclude=vendor
```

Checks compatibility with PHP interpreter versions

```bash
./vendor/bin/phpcf --target 7.2 \
--file-extensions php,module,inc,install,test,profile,theme,info \
./src
```

### Enforce code standards with git hooks

Maintaining code quality by adding the custom post-commit hook to yours.

```bash
cat ./scripts/hooks/post-commit >> ./.git/hooks/post-commit
```
