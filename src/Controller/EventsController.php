<?php

namespace Drupal\commerce_gtm_enhanced_ecommerce\Controller;

use Drupal\commerce_gtm_enhanced_ecommerce\EventStorageService;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * A controller to receive the tracked Enhanced Ecommerce events.
 *
 * Called via ajax on a page load to actually send the tracked events
 * (server-side) to Google Tag Manager.
 *
 * @package Drupal\commerce_gtm_enhanced_ecommerce
 */
class EventsController extends ControllerBase {

  /**
   * @var \Drupal\commerce_gtm_enhanced_ecommerce\EventStorageService
   */
  private $ecommerceEventStorageService;

  /**
   * @param \Drupal\commerce_gtm_enhanced_ecommerce\EventStorageService $ecommerceEventStorageService
   */
  public function __construct(EventStorageService $ecommerceEventStorageService) {
    $this->ecommerceEventStorageService = $ecommerceEventStorageService;
  }

  /**
   * @inheritdoc
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('commerce_gtm_enhanced_ecommerce.event_storage'));
  }

  /**
   * Get all tracked events as JSON.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getEvents() {
    $events = $this->ecommerceEventStorageService->getEvents();
    $this->ecommerceEventStorageService->flush();

    return new JsonResponse($events);
  }

}
