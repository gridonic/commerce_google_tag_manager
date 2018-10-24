<?php

namespace Drupal\commerce_gtm_enhanced_ecommerce\Event;

/**
 * Defines events for the Commerce Google Tag Manager Enhanced Ecommerce module.
 */
final class EnhancedEcommerceEvents {

  /**
   * Event fired after mapping a commerce product to a product
   * represented in the domain of Enhanced Ecommerce. This allows
   * to change the mapping of fields or enhance products with custom
   * dimensions or metrics.
   *
   * @Event
   *
   * @see \Drupal\commerce_postfinance\Event\AlterProductEvent
   */
  const ALTER_PRODUCT = 'commerce_gtm_enhanced_ecommerce.alter_product';

  /**
   * Allows to alter the event data of each Enhanced Ecommerce event
   * before it gets pushed to the data layer.
   *
   * @Event
   *
   * @see \Drupal\commerce_gtm_enhanced_ecommerce\Event\AlterEventDataEvent
   */
  const ALTER_EVENT_DATA = 'commerce_gtm_enhanced_ecommerce.alter_event_data';

  /**
   * Event fired when tracking a checkout step. This allows event listeners
   * to track additional checkout step options.
   *
   * @Event
   *
   * @see \Drupal\commerce_gtm_enhanced_ecommerce\Event\TrackCheckoutStepEvent
   */
  const TRACK_CHECKOUT_STEP = 'commerce_gtm_enhanced_ecommerce.track_checkout_step';

}
