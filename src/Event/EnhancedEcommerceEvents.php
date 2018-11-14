<?php

namespace Drupal\commerce_google_tag_manager\Event;

/**
 * Defines events for the Commerce Google Tag Manager Enhanced Ecommerce module.
 */
final class EnhancedEcommerceEvents {

  /**
   * Event fired after mapping a commerce product to a product.
   *
   * This allows to change the mapping of fields or enhance products with custom
   * dimensions or metrics.
   *
   * @Event
   *
   * @see \Drupal\commerce_google_tag_manager\Event\AlterProductEvent
   */
  const ALTER_PRODUCT = 'commerce_google_tag_manager.alter_product';

  /**
   * Allows to alter the event data of each Enhanced Ecommerce event.
   *
   * Allows alteration before it gets pushed to the data layer.
   *
   * @Event
   *
   * @see \Drupal\commerce_google_tag_manager\Event\AlterEventDataEvent
   */
  const ALTER_EVENT_DATA = 'commerce_google_tag_manager.alter_event_data';

  /**
   * Event fired when tracking a checkout step.
   *
   * This allows event listeners to track additional checkout step options.
   *
   * @Event
   *
   * @see \Drupal\commerce_google_tag_manager\Event\TrackCheckoutStepEvent
   */
  const TRACK_CHECKOUT_STEP = 'commerce_google_tag_manager.track_checkout_step';

}
