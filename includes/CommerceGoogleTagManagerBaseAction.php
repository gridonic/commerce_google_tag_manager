<?php

/**
 * Created by PhpStorm.
 * User: ema
 * Date: 16/03/16
 * Time: 13:48
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

    // Add the data line to the JS array.
    $_SESSION['commerce_google_tag_manager'][] = $script . 'dataLayer.push(' . drupal_json_encode($data) . ');';
  }

}
