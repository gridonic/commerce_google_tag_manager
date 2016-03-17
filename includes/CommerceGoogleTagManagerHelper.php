<?php

/**
 * @file
 * This file contains the helper class CommerceGoogleTagManagerHelper.
 */

class CommerceGoogleTagManagerHelper {

  /**
   * Wraps the give order, if needed, to a EntityMetadataWrapper object.
   *
   * @param array|stdClass $order The Order object or array
   * @return \EntityMetadataWrapper
   */
  static function getWrappedOrder($order) {
    if (!($order instanceof EntityMetadataWrapper)) {
      return entity_metadata_wrapper('commerce_order', $order);
    }
    return $order;
  }

  /**
   * Wraps the give line-item, if needed, to a EntityMetadataWrapper object.
   *
   * @param array|stdClass $item The LineItem object or array
   * @return \EntityMetadataWrapper
   */
  static function getWrappedLineItem($item) {
    if (!($item instanceof EntityMetadataWrapper)) {
      return entity_metadata_wrapper('commerce_line_item', $item);
    }
    return $item;
  }

  /**
   * Gets an array-based representation of the given Order.
   *
   * @param EntityMetadataWrapper $order The Order object
   * @return array
   */
  static function getOrderData(\EntityMetadataWrapper $order, $event = NULL) {
    $tax_sum = 0;
    if (module_exists('commerce_tax')) {
      foreach (commerce_tax_rates() as $name => $tax_rate) {
        if ($tax_rate['price_component']) {
          $tax_component = commerce_price_component_load($order->commerce_order_total->value(), $tax_rate['price_component']);
          // Some taxes may not have been applied.
          if (isset($tax_component[0]['price']['amount'])) {
            $tax_sum += commerce_currency_amount_to_decimal($tax_component[0]['price']['amount'], $tax_component[0]['price']['currency_code']);
          }
        }
      }
    }

    $shipping = 0;
    if (module_exists('commerce_shipping')) {
      foreach ($order->commerce_line_items as $item) {
        if ($item->type->value() == 'shipping') {
          $shipping += commerce_currency_amount_to_decimal($item->commerce_total->amount->value(), $item->commerce_total->currency_code->value());
        }
      }
    }

    // Build the transaction arguments.
    $order_id = $order->order_id->value();
    $order_number = $order->order_number->value() ? $order->order_number->value() : $order_id;
    $order_currency_code = $order->commerce_order_total->currency_code->value();
    $order_total = commerce_currency_amount_to_decimal($order->commerce_order_total->amount->value(), $order_currency_code);
    $affiliation = variable_get('site_name', '');

    $order_data = array(
      'id'          => $order_number,
      'affiliation' => $affiliation,
      'revenue'     => $order_total,
      'tax'         => $tax_sum,
      'shipping'    => $shipping,
    );

    // Allow other modules to alter this order data.
    $context = array('order'  => $order, 'event' => $event);
    drupal_alter('commerce_google_tag_manager_order_data', $order_data, $context);

    return $order_data;
  }


  /**
   * Gets an array-based representation of the given Line Item.

   * @param EntityMetadataWrapper $item The order Line-Item object.
   * @return array
   */
  static function getLineItemData(\EntityMetadataWrapper $item, $event = NULL) {
    $properties = $item->getPropertyInfo();

    $product_data = NULL;

    if (isset($properties['commerce_product'])) {
      $product_id = $item->commerce_product->getIdentifier();
      if (!empty($product_id)) {
        // Build the item arguments.
        $sku = $item->commerce_product->sku->value();
        $name = $item->commerce_product->title->value();
        $category = '';
        $variant = commerce_product_type_get_name($item->commerce_product->getBundle());
        $price = commerce_currency_amount_to_decimal(
          $item->commerce_unit_price->amount->value(),
          $item->commerce_unit_price->currency_code->value()
        );
        $quantity = (int) $item->quantity->value();

        // Building the ProductData array
        $product_data = array(
          'id' => $sku,
          'name' => $name,
          'category' => $category,
          'variant' => $variant,
          'price' => $price,
          'quantity' => $quantity,
        );

        // Allow other modules to alter this product data.
        $context = array('line-item'  => $item, 'event' => $event);
        drupal_alter('commerce_google_tag_manager_line_item_data', $product_data, $context);
      }
    }
    return $product_data;
  }


  /**
   * @param $order
   * @return array
   */
  static function getLineItemsData($order, $event = NULL) {

    $products = array();

    // Loop through the products on the order.
    foreach ($order->commerce_line_items as $line_item_wrapper) {
      $products[] = self::getLineItemData($line_item_wrapper, $event);
    }

    return array_filter($products);
  }
}
