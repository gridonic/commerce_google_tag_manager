<?php

/**
 * @file
 * This file contains the base class for CommerceGoogleTagManager actions.
 */
abstract class CommerceGoogleTagManagerBaseAction extends RulesActionHandlerBase {

  /**
   * Returns the commerce event's name to be sent.
   *
   * @return string
   */
  abstract public function getCommerceEventName();

  /**
   * Get the default Rule's info.
   *
   * @return array
   */
  protected static function getDefaultsInfo() {
    return array(
      'group' => t('Commerce Google-TagManager'),
      'name' => static::class,
    );
  }

  /**
   * Builds and pushes the current commerce data.
   *
   * @param array $commerceData
   */
  protected function pushCommerceData(array $commerceData) {
    $script = 'var dataLayer = dataLayer || []; ';

    $data = array(
      'event' => $this->getCommerceEventName(),
      'ecommerce' => $commerceData,
    );

    $context = array('event' => $this->getCommerceEventName());
    // Allow other modules to alter this data before sending to DataLayer
    drupal_alter('commerce_google_tag_manager_commmerce_data', $data, $context);

    // Add the data line to the JS array.
    $_SESSION['commerce_google_tag_manager'][] = $script . 'dataLayer.push(' . drupal_json_encode($data) . ');';
  }

}
