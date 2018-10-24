<?php

namespace Drupal\commerce_gtm_enhanced_ecommerce\Event;

use Drupal\commerce_order\Entity\OrderInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched when a checkout step is tracked.
 *
 * Event listeners may use this event to track checkout options.
 *
 * @package Drupal\commerce_gtm_enhanced_ecommerce\Event
 */
class TrackCheckoutStepEvent extends Event {

  /**
   * @var int
   */
  private $stepIndex;

  /**
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  private $order;

  /**
   * @param int $stepIndex
   *   The step index of the checkout step, starting at 1.
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The commerce order representing the current cart.
   */
  public function __construct($stepIndex, OrderInterface $order) {
    $this->stepIndex = $stepIndex;
    $this->order = $order;
  }

  /**
   * @return int
   */
  public function getStepIndex() {
    return $this->stepIndex;
  }

  /**
   * @return \Drupal\commerce_order\Entity\OrderInterface
   */
  public function getOrder() {
    return $this->order;
  }
}
