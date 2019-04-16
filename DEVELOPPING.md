# Developing on Commerce Google Tag Manager

- Issues should be filed at
  https://www.drupal.org/project/issues/commerce_google_tag_manager
- Pull requests can be made against
  https://github.com/gridonic/commerce_google_tag_manager/pulls

## 📦 Repositories

Github repo

```bash
git remote add github https://github.com/gridonic/commerce_google_tag_manager
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

## 🏆 Tests

You must provide a `SIMPLETEST_BASE_URL`, Eg. `http://localhost`.
You must provide a `SIMPLETEST_DB`, Eg. `sqlite://localhost/build/commerce_google_tag_manager.sqlite`.

Run the functional tests:

```bash
# You must be on the drupal-root folder - usually /web.
$ cd web
$ SIMPLETEST_DB="sqlite://localhost//tmp/commerce_google_tag_manager.sqlite" \
SIMPLETEST_BASE_URL='http://sandbox.test' \
BROWSERTEST_OUTPUT_DIRECTORY="/path/to/webroot/browser_output" \
../vendor/bin/phpunit -c core \
--group commerce_google_tag_manager \
--printer="\Drupal\Tests\Listeners\HtmlOutputPrinter" --stop-on-error
```

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
