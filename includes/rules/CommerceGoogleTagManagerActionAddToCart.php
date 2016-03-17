<?php

/**
 * @file
 * This file contains the "Send Add To Cart event to GTM" action definition.
 */
class CommerceGoogleTagManagerActionAddToCart extends CommerceGoogleTagManagerBaseAction {

  /**
   * Defines the action.
   *
   * @return array
   */
  public static function getInfo() {
    return self::getDefaultsInfo() + array(
      'label' => t('Send "Add to Cart" event to google analytics via GTM'),
      'parameter' => array(
        'item' => array(
          'type' => 'commerce_line_item',
          'label' => t('Line Item in the Order'),
        ),
        'count' => array(
          'type' => 'text',
          'label' => t('The count of items added to the Cart'),
          'optional' => TRUE,
        ),
      ),
    );
  }

  /**
   * @return string
   */
  public function getCommerceEventName() {
    return 'addToCart';
  }

  /**
   * Executes the action.
   *
   * @param array $item The Line Item of the order
   * @param string $count Number of items added to the order
   */
  public function execute($item, $count) {
    $item = CommerceGoogleTagManagerHelper::getWrappedLineItem($item);

    $productData = CommerceGoogleTagManagerHelper::getLineItemData($item, $this->getCommerceEventName());
    $order = $item->order;
    $currencyCode = $order->commerce_order_total->currency_code->value();

    // Override the product quantity, if set:
    if ($count) {
      $productData['quantity'] = $count;
    }

    $data = array(
      'currencyCode' => $currencyCode,
      'add' => array(
        'products' => array($productData),
      ),
    );

    $this->pushCommerceData($data);
  }
}
