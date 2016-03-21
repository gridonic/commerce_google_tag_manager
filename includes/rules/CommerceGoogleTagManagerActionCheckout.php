<?php

/**
 * @file
 * This file contains the "Send Checkout event to GTM" action definition.
 */
class CommerceGoogleTagManagerActionCheckout extends CommerceGoogleTagManagerBaseAction {

  /**
   * Defines the action.
   *
   * @return array
   */
  public static function getInfo() {
    return self::getDefaultsInfo() + array(
      'label' => t('Send "Checkout" event to google analytics via GTM'),
      'parameter' => array(
        'order' => array(
          'type' => 'commerce_order',
          'label' => t('Order in checkout'),
        ),
        'step' => array(
          'type' => 'text',
          'label' => t('Step of the Checkout process'),
          'optional' => TRUE,
        ),
        'option' => array(
          'type' => 'text',
          'label' => t('Additional Option of the Checkout process'),
          'optional' => TRUE,
        ),
      ),
    );
  }

  /**
   * @return string
   */
  public function getCommerceEventName() {
    return 'checkout';
  }

  /**
   * Executes the action.
   *
   * @param array $order The Order being checked out
   * @param string $step The checkout step
   * @param string $option Any checkout action related to the step.
   */
  public function execute($order, $step = null, $option = null) {
    $order = CommerceGoogleTagManagerHelper::getWrappedOrder($order);

    $productsData = CommerceGoogleTagManagerHelper::getLineItemsData($order, $this->getCommerceEventName());
    $currencyCode = $order->commerce_order_total->currency_code->value();

    $data = array(
      'currencyCode' => $currencyCode,
      'checkout' => array(
        'products' => $productsData
      ),
    );

    if (!is_null($step)) {
      $data['checkout']['actionField']['step'] = $step;
    }

    if (!is_null($option)) {
      $data['checkout']['actionField']['option'] = $option;
    }

    // Push the commerce-data.
    $this->pushCommerceData($data);
  }
}
