<?php

/**
 * @file
 * Documentation of Commerce GoogleTagManager hooks.
 */

/**
 * Allow other modules to alter the Product data of a order line-item.
 *
 * The $context variable is an associative array with the following keys:
 * - line-item : the Line item of the order
 * - event : the event that is requesting the product data
 */
function hook_commerce_google_tag_manager_line_item_data_alter(&$product_data, $context) {
  // If we're sending the Checkout event
  if ($context['event'] == 'checkout') {
    // Overwrite the category with the product title
    $product_data['category'] = $context['line-item']->commerce_product->title->value();
  }
}

/**
 * Allow other modules to alter the Order data of a order line-item.
 *
 * The $context variable is an associative array with the following keys:
 * - order : the Order being processed
 * - event : the event that is requesting the product data
 */
function hook_commerce_google_tag_manager_order_data_alter(&$order_data, $context) {
  // If we're sending the Purchase event
  if ($context['event'] == 'purchase') {
    // Remove the Sipping costs
    unset($order_data['shipping']);
  }
}

/**
 * Allow other modules to alter the data before pushing to DataLayer.
 * The $data array structure changes depending on the event that is executed.
 *
 * The $context variable is an associative array with the following keys:
 * - event : the event that is sending the Commerce data to Google TagManager.
 */
function hook_commerce_google_tag_manager_commmerce_data_alter(&$data, $context) {
  // If we're sending a "Checkout" event,
  if ($context['event'] == 'checkout') {
    // Remove the 'order' data
    unset($data['ecommerce']['order']);
  }
}
