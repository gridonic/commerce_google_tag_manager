<?php

namespace Drupal\commerce_gtm_enhanced_ecommerce\EventSubscriber;

use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_cart\Event\CartOrderItemRemoveEvent;
use Drupal\commerce_gtm_enhanced_ecommerce\EventTrackerService;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event handler for commerce related events.
 *
 * @package Drupal\commerce_gtm_enhanced_ecommerce\EventSubscriber
 */
class CommerceEventsSubscriber implements EventSubscriberInterface {

  /**
   * @var \Drupal\commerce_gtm_enhanced_ecommerce\EventTrackerService
   */
  private $ecommerceEventTrackerService;

  /**
   * @param \Drupal\commerce_gtm_enhanced_ecommerce\EventTrackerService $ecommerceEventTrackerService
   */
  public function __construct(EventTrackerService $ecommerceEventTrackerService) {
    $this->ecommerceEventTrackerService = $ecommerceEventTrackerService;
  }

  /**
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    return [
      CartEvents::CART_ENTITY_ADD => 'trackCartAdd',
      CartEvents::CART_ORDER_ITEM_REMOVE => 'trackCartRemove',
      'commerce_order.place.post_transition' => 'trackPurchase',
    ];
  }

  /**
   * @param \Drupal\commerce_cart\Event\CartEntityAddEvent $event
   */
  public function trackCartAdd(CartEntityAddEvent $event) {
    $this->ecommerceEventTrackerService->addToCart($event->getOrderItem(), (int) $event->getQuantity());
  }

  /**
   * @param \Drupal\commerce_cart\Event\CartOrderItemRemoveEvent $event
   */
  public function trackCartRemove(CartOrderItemRemoveEvent $event) {
    $this->ecommerceEventTrackerService->removeFromCart($event->getOrderItem(), 1);
  }

  /**
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   */
  public function trackPurchase(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();
    $this->ecommerceEventTrackerService->purchase($order);
  }

}
