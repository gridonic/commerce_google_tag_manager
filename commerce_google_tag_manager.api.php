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
 * Allow other modules to alter data for a single event before aggregation.
 * The $data array structure changes depending on the event that is executed.
 *
 * The $context variable is an associative array with the following keys:
 * - event : the event that is sending the Commerce data to Google TagManager.
 */
function hook_commerce_google_tag_manager_commmerce_data_alter(&$data, $context) {
  // If we're sending a "Checkout" event,
  if ($context['event'] == 'checkout') {
    // Change the 'option' data
    $data['ecommerce']['checkout']['actionField']['option'] = 'Overwritten Option';
  }
}

/**
 * Allow other modules to alter aggregated event-data just before pushing to
 * the DataLayer.
 *
 * This hook is called within page_build().
 */
function hook_commerce_google_tag_manager_commerce_data_aggregated_alter(&$data) {
  // Reorder the events by priority: most important events on top.
  $prio_events = array(
    '"event":"purchase"',
    '"event":"addToCart"',
    '"event":"removeFromCart"',
    '"event":"checkout"',
  );

  // Build a string representation of the data, to be able to match our events.
  $imploded_data = implode('', $data);

  // This double loop replaces multiple event-scripts with the script from the
  // most important event.
  foreach ($prio_events as $event) {
    // Find needle in scripts-array.
    if (strpos($imploded_data, $event) !== FALSE) {
      // Find specific script and select it.
      foreach ($data as $script) {
        if (strpos($script, $event) !== FALSE) {
          // Replace the array with multiple event-scripts by the prio-event.
          $data = array($script);
          // Exit inner loop.
          continue;
        }
      }
      // Exit outer loop.
      continue;
    }
  }
}
