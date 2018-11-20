<?php

namespace Drupal\commerce_google_tag_manager\EventSubscriber;

use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_cart\Event\CartOrderItemRemoveEvent;
use Drupal\commerce_google_tag_manager\EventTrackerService;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event handler for commerce related events.
 */
class CommerceEventsSubscriber implements EventSubscriberInterface {

  /**
   * The Commerce GTM event tracker.
   *
   * @var \Drupal\commerce_google_tag_manager\EventTrackerService
   */
  private $eventTracker;

  /**
   * Constructs the CommerceEventsSubscriber object.
   *
   * @param \Drupal\commerce_google_tag_manager\EventTrackerService $event_tracker
   *   The Commerce GTM event tracker.
   */
  public function __construct(EventTrackerService $event_tracker) {
    $this->eventTracker = $event_tracker;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      CartEvents::CART_ENTITY_ADD => 'trackCartAdd',
      CartEvents::CART_ORDER_ITEM_REMOVE => 'trackCartRemove',
      'commerce_order.place.post_transition' => 'trackPurchase',
    ];
  }

  /**
   * Track the "addToCart" event.
   *
   * @param \Drupal\commerce_cart\Event\CartEntityAddEvent $event
   *   The add to cart event.
   */
  public function trackCartAdd(CartEntityAddEvent $event) {
    $this->eventTracker->addToCart($event->getOrderItem(), (int) $event->getQuantity());
  }

  /**
   * Track the "cartRemove" event.
   *
   * @param \Drupal\commerce_cart\Event\CartOrderItemRemoveEvent $event
   *   The cart event.
   */
  public function trackCartRemove(CartOrderItemRemoveEvent $event) {
    $this->eventTracker->removeFromCart($event->getOrderItem(), 1);
  }

  /**
   * Track the "purchase" event.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The workflow transition event.
   */
  public function trackPurchase(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();
    $this->eventTracker->purchase($order);
  }

}
