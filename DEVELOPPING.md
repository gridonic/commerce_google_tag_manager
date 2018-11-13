# Developing on Commerce Google Tag Manager

- Issues should be filed at
  https://www.drupal.org/project/issues/commerce_google_tag_manager
- Pull requests can be made against
  https://github.com/gridonic/commerce_google_tag_manager/pulls

## 📦 Repositories

Github repo

```bash
git remote add drupal git@git.drupal.org:project/commerce_google_tag_manager.git
```

Drupal repo

```bash
git remote add github https://github.com/gridonic/commerce_google_tag_manager
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
