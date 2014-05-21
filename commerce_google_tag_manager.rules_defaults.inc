<?php
/**
 * @file
 * Rules integration file
 */

/**
 * Implements hook_default_rules_configuration().
 */
function commerce_google_tag_manager_default_rules_configuration() {
  $rule = rules_reaction_rule();
  $rule->label = t('Send transaction details via GTM on checkout completion');
  $rule->active = TRUE;
  $rule->event('commerce_checkout_complete')
          ->action('commerce_google_tag_manager_send_order', array('commerce_order:select' => 'commerce_order')
  );
  $configs['commerce_google_tag_manager_rule_ga'] = $rule;
  return $configs;
}
