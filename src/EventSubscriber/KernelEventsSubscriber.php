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
   * @var \Drupal\commerce_google_tag_manager\EventTrackerService
   */
  private $eventTrackerService;

  /**
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

  /**
   * @var \Drupal\commerce_checkout\CheckoutOrderManagerInterface
   */
  private $checkoutOrderManager;

  /**
   * @param \Drupal\commerce_google_tag_manager\EventTrackerService $eventTrackerService
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   * @param \Drupal\commerce_checkout\CheckoutOrderManagerInterface $checkoutOrderManager
   */
  public function __construct(EventTrackerService $eventTrackerService,
                              RouteMatchInterface $routeMatch,
                              CheckoutOrderManagerInterface $checkoutOrderManager) {
    $this->eventTrackerService = $eventTrackerService;
    $this->routeMatch = $routeMatch;
    $this->checkoutOrderManager = $checkoutOrderManager;
  }

  /**
   * @inheritdoc
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
      $this->eventTrackerService->checkoutStep($checkoutStepIndex, $order);
    }
  }

  /**
   * Check if the current request matches the conditions to track the checkout.
   *
   * @param \Symfony\Component\HttpKernel\Event\FinishRequestEvent $event
   *
   * @return bool
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
   *
   * @return int
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
