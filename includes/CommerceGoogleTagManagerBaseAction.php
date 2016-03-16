<?php

/**
 * Created by PhpStorm.
 * User: ema
 * Date: 16/03/16
 * Time: 13:48
 */
abstract class CommerceGoogleTagManagerBaseAction extends RulesActionHandlerBase {

  /**
   * Returns the Action name
   * @return string
   */
  abstract public function getCommerceEventName();

  /**
   * Defines the action.
   * @return array
   */
  abstract public function getInfo();

  /**
   * @return array
   */
  protected function getDefaultsInfo() {
    return array(
      'group' => t('Commerce Google-TagManager'),
    );
  }

  /**
   * Builds and pushes the current commerce data.
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
