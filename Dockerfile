ARG BASE_IMAGE_TAG=8.9
FROM wengerk/drupal-for-contrib:${BASE_IMAGE_TAG}

# Drupal Commerce 2.20+ requires bcmath extensions.
RUN docker-php-ext-install bcmath

ENV COMPOSER_MEMORY_LIMIT=-1

# Disable deprecation notice because of Drupal Commerce hard-dependency.
ENV SYMFONY_DEPRECATIONS_HELPER=disabled

# Install Drupal Commerce as required by the module
RUN composer require drupal/commerce:^2.26 'drupal/inline_entity_form:^1.0@RC'

# Install the Google Tag module as required by the module
RUN composer require drupal/google_tag:^1.1

# Register the Drupal and DrupalPractice Standard with PHPCS.
RUN ./vendor/bin/phpcs --config-set installed_paths \
    `pwd`/vendor/drupal/coder/coder_sniffer

# Copy the Analyzer definition files to ease run.
COPY phpcs.xml.dist phpmd.xml ./

# Download & install PHPMD.
RUN set -eux; \
  curl -LJO https://phpmd.org/static/latest/phpmd.phar; \
  chmod +x phpmd.phar; \
  mv phpmd.phar /usr/bin/phpmd

# Download & install PHPCPD.
RUN set -eux; \
  curl -LJO https://phar.phpunit.de/phpcpd.phar; \
  chmod +x phpcpd.phar; \
  mv phpcpd.phar /usr/bin/phpcpd

# Download & install PhpDeprecationDetector.
RUN set -eux; \
  \
  apt-get update; \
  apt-get install -y \
   libbz2-dev \
  ; \
  \
  docker-php-ext-install bz2; \
  \
  curl -LJO https://github.com/wapmorgan/PhpDeprecationDetector/releases/download/2.0.24/phpcf-2.0.24.phar; \
  chmod +x phpcf-2.0.24.phar; \
  mv phpcf-2.0.24.phar /usr/bin/phpdd
