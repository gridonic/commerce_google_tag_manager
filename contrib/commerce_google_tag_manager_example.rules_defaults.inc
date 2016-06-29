<?php

/**
 * @file
 * Default Rules configuration for Commerce Google Tag Manager Example.
 */

/**
 * Implements hook_default_rules_configuration().
 */
function commerce_google_tag_manager_example_default_rules_configuration() {
  $items = array();

  // Add to cart event.
  $items['commerce_google_tag_manager_add_to_cart'] = entity_import('rules_config', '{ "commerce_google_tag_manager_addtocart" : {
    "LABEL" : "Push GoogleTag Add to Cart dataLayer",
    "PLUGIN" : "reaction rule",
    "OWNER" : "rules",
    "TAGS" : [ "Commerce Google Tag Manager", "Commerce Cart" ],
    "REQUIRES" : [ "commerce_google_tag_manager", "commerce_cart" ],
    "ON" : { "commerce_cart_product_add" : [] },
    "DO" : [
      { "CommerceGoogleTagManagerActionAddToCart" : { "item" : [ "commerce-line-item" ], "count" : [ "quantity" ] } }
    ]
  }
}');

  // Remove from cart event.
  $items['commerce_google_tag_manager_remove_from_cart'] = entity_import('rules_config', '{ "commerce_google_tag_manager_remove_from_cart" : {
    "LABEL" : "Push GoogleTag Remove from Cart dataLayer",
    "PLUGIN" : "reaction rule",
    "OWNER" : "rules",
    "TAGS" : [ "Commerce Google Tag Manager", "Commerce Cart" ],
    "REQUIRES" : [ "commerce_google_tag_manager", "commerce_cart" ],
    "ON" : { "commerce_cart_product_remove" : [] },
    "DO" : [
      { "CommerceGoogleTagManagerActionRemoveFromCart" : { "item" : [ "commerce-line-item" ], "count" : [ "quantity" ] } }
    ]
  }
}');

  // Purchase event.
  $items['commerce_google_tag_manager_purchase'] = entity_import('rules_config', '{ "commerce_google_tag_manager_purchase" : {
    "LABEL" : "Push GoogleTag Purchase dataLayer",
    "PLUGIN" : "reaction rule",
    "OWNER" : "rules",
    "TAGS" : [ "Commerce Google Tag Manager", "Commerce Payment" ],
    "REQUIRES" : [ "commerce_google_tag_manager", "commerce_payment" ],
    "ON" : { "commerce_payment_order_paid_in_full" : [] },
    "DO" : [
      { "CommerceGoogleTagManagerActionPurchase" : { "order" : [ "commerce-order" ] } }
    ]
  }
}');

  // Checkout state change event.
  $items['commerce_google_tag_manager_checkout_state_changed'] = entity_import('rules_config', '{ "commerce_google_tag_manager_checkout_state_changed" : {
    "LABEL" : "Push GoogleTag Checkout-state-Changed dataLayer",
    "PLUGIN" : "reaction rule",
    "OWNER" : "rules",
    "TAGS" : [ "Commerce Checkout", "Commerce Google Tag Manager" ],
    "REQUIRES" : [ "rules", "commerce_google_tag_manager", "entity" ],
    "ON" : { "commerce_order_update" : [] },
    "IF" : [
      { "data_is" : { "data" : [ "commerce-order:state" ], "value" : "checkout" } },
      { "NOT data_is" : { "data" : [ "commerce-order:status" ], "value" : "checkout_complete" } },
      { "NOT data_is" : {
          "data" : [ "commerce-order:status" ],
          "value" : [ "commerce-order-unchanged:status" ]
        }
      }
    ],
    "DO" : [
      { "CommerceGoogleTagManagerActionCheckout" : { "order" : [ "commerce-order" ], "step" : [ "commerce-order:status" ] } }
    ]
  }
}');

  return $items;
}
