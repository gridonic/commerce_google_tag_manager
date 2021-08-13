<?php

namespace Drupal\Tests\commerce_google_tag_manager\Traits;

/**
 * Override DeprecationSuppressionTrait of Drupal Commerce.
 *
 * The original DeprecationSuppressionTrait from Drupal Commerce is not
 * compatible with PHP 8.
 *
 * @see \Drupal\Tests\commerce\Traits\DeprecationSuppressionTrait;
 */
trait DeprecationSuppressionTrait {

  /**
   * Sets an error handler to suppress specified deprecation messages.
   */
  protected function setErrorHandler() {
    $previous_error_handler = set_error_handler(function ($severity, $message, $file, $line) use (&$previous_error_handler) {

      $skipped_deprecations = [
        // @see https://www.drupal.org/project/address/issues/3089266
        'Theme functions are deprecated in drupal:8.0.0 and are removed from drupal:10.0.0. Use Twig templates instead of theme_inline_entity_form_entity_table(). See https://www.drupal.org/node/1831138',
      ];

      if (!in_array($message, $skipped_deprecations, TRUE)) {
        return $previous_error_handler($severity, $message, $file, $line);
      }
    }, E_USER_DEPRECATED);
  }

  /**
   * Restores the original error handler.
   */
  protected function restoreErrorHandler() {
    restore_error_handler();
  }

}
