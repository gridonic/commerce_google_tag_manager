# Developing on Commerce Google Tag Manager Enhanced Ecommerce

- Issues should be filed at
  https://www.drupal.org/project/issues/commerce_gtm_enhanced_ecommerce
- Pull requests can be made against
  https://github.com/gridonic/commerce_gtm_enhanced_ecommerce/pulls

## 📦 Repositories

Github repo

```bash
git remote add drupal git@git.drupal.org:project/commerce_gtm_enhanced_ecommerce.git
```

Drupal repo

```bash
git remote add github https://github.com/gridonic/commerce_gtm_enhanced_ecommerce
```

## 🔧 Prerequisites

First of all, you will need to have the following tools installed
globally on your environment:

- drush
- Latest dev release of Drupal 8.x.

## 🏆 Tests

You must provide a `SIMPLETEST_BASE_URL`, Eg. `http://localhost`.
You must provide a `SIMPLETEST_DB`, Eg. `sqlite://localhost/build/commerce_gtm_enhanced_ecommerce.sqlite`.

Run the functional tests:

```bash
# You must be on the drupal-root folder - usually /web.
$ cd web
$ SIMPLETEST_DB="sqlite://localhost//tmp/commerce_gtm_enhanced_ecommerce.sqlite" \
SIMPLETEST_BASE_URL='http://sandbox.test' \
BROWSERTEST_OUTPUT_DIRECTORY="/path/to/webroot/browser_output" \
../vendor/bin/phpunit -c core \
--group commerce_gtm_enhanced_ecommerce \
--printer="\Drupal\Tests\Listeners\HtmlOutputPrinter" --stop-on-error
```
