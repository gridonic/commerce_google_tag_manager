<?php

namespace Drupal\commerce_google_tag_manager\EventSubscriber;

use Drupal\commerce_checkout\CheckoutOrderManagerInterface;
use Drupal\commerce_google_tag_manager\EventTrackerService;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event handler for Kernel events.
 */
class KernelEventsSubscriber implements EventSubscriberInterface {

  /**
   * The Commerce GTM event tracker.
   *
   * @var \Drupal\commerce_google_tag_manager\EventTrackerService
   */
  private $eventTracker;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

  /**
   * The checkout order manager.
   *
   * @var \Drupal\commerce_checkout\CheckoutOrderManagerInterface
   */
  private $checkoutOrderManager;

  /**
   * Constructs KernelEventsSubscriber object.
   *
   * @param \Drupal\commerce_google_tag_manager\EventTrackerService $event_tracker
   *   The Commerce GTM event tracker.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\commerce_checkout\CheckoutOrderManagerInterface $checkout_order_manager
   *   The checkout order manager.
   */
  public function __construct(EventTrackerService $event_tracker,
                              RouteMatchInterface $route_match,
                              CheckoutOrderManagerInterface $checkout_order_manager) {
    $this->eventTracker = $event_tracker;
    $this->routeMatch = $route_match;
    $this->checkoutOrderManager = $checkout_order_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::FINISH_REQUEST => 'onFinishRequest',
    ];
  }

  /**
   * Tracks an Enhanced Ecommerce checkout event.
   *
   * @param \Symfony\Component\HttpKernel\Event\FinishRequestEvent $event
   *   The request.
   */
  public function onFinishRequest(FinishRequestEvent $event) {
    if (!$this->shouldTrackCheckout($event)) {
      return;
    }

    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $this->routeMatch->getParameter('commerce_order');
    if (!$order) {
      return;
    }

    $checkoutStepIndex = $this->getCheckoutStepIndex($order);
    if ($checkoutStepIndex) {
      $this->eventTracker->checkoutStep($checkoutStepIndex, $order);
    }
  }

  /**
   * Check if the current request matches the conditions to track the checkout.
   *
   * @param \Symfony\Component\HttpKernel\Event\FinishRequestEvent $event
   *   The request.
   *
   * @return bool
   *   Does this route should be tracked as "checkout".
   */
  private function shouldTrackCheckout(FinishRequestEvent $event) {
    if ($this->routeMatch->getRouteName() !== 'commerce_checkout.form') {
      return FALSE;
    }

    // Bail if we are not dealing with a master request or GET method.
    if (!$event->isMasterRequest() || !$event->getRequest()->isMethod('GET')) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Returns an index for the current checkout step, starting at index 1.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order entity.
   *
   * @return int
   *   Get the Checkout step number.
   */
  private function getCheckoutStepIndex(OrderInterface $order) {
    $checkoutFlow = $this->checkoutOrderManager->getCheckoutFlow($order);
    $checkoutFlowPlugin = $checkoutFlow->getPlugin();
    $steps = $checkoutFlowPlugin->getSteps();
    $requestedStepId = $this->routeMatch->getParameter('step');
    $currentStepId = $this->checkoutOrderManager->getCheckoutStepId($order, $requestedStepId);
    $currentStepIndex = array_search($currentStepId, array_keys($steps));

    if ($currentStepIndex === FALSE) {
      return 0;
    }

    return ++$currentStepIndex;
  }

}
