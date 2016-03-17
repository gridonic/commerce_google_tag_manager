<?php

/**
 * @file
 * This file contains the "Send the Purchase event to GTM" action definition.
 */
class CommerceGoogleTagManagerActionPurchase extends CommerceGoogleTagManagerBaseAction {

  /**
   * Defines the action.
   *
   * @return array
   */
  public static function getInfo() {
    return self::getDefaultsInfo() + array(
      'label' => t('Send "Purchase" event to google analytics via GTM'),
      'parameter' => array(
        'order' => array(
          'type' => 'commerce_order',
          'label' => t('Order in checkout'),
        ),
      ),
    );
  }

  /**
   * @return string
   */
  public function getCommerceEventName() {
    return 'purchase';
  }

  /**
   * Executes the action.
   *
   * @param array $order The order being purchased
   */
  public function execute($order) {
    $order = CommerceGoogleTagManagerHelper::getWrappedOrder($order);

    $productsData = CommerceGoogleTagManagerHelper::getLineItemsData($order, $this->getCommerceEventName());
    $orderData = CommerceGoogleTagManagerHelper::getOrderData($order, $this->getCommerceEventName());
    $currencyCode = $order->commerce_order_total->currency_code->value();

    $data = array(
      'currencyCode' => $currencyCode,
      'purchase' => array(
        'actionField' => $orderData,
        'products' => $productsData
      ),
    );

    $this->pushCommerceData($data);
  }

}
