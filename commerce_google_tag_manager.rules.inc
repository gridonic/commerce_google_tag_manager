<?php
/**
 * @file
 * Rules integration file
 */

/**
 * Implements hook_rules_action_info().
 */
function commerce_google_tag_manager_rules_action_info() {
  return array(
    // Add rules action which sends the transaction code.
    'commerce_google_tag_manager_send_order' => array(
      'label' => t('Send order to google analytics via GTM'),
      'parameter' => array(
        'commerce_order' => array(
          'type' => 'commerce_order',
          'label' => t('Order in checkout'),
        ),
      ),
      'callback' => array(
        'execute' => 'commerce_google_tag_manager_send_order',
      ),
    ),
  );
}
