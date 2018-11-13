<?php

namespace Drupal\commerce_google_tag_manager\Controller;

use Drupal\commerce_google_tag_manager\EventStorageService;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * A controller to receive the tracked Enhanced Ecommerce events.
 *
 * Called via ajax on a page load to actually send the tracked events
 * (server-side) to Google Tag Manager.
 */
class EventsController extends ControllerBase {

  /**
   * @var \Drupal\commerce_google_tag_manager\EventStorageService
   */
  private $eventStorageService;

  /**
   * @param \Drupal\commerce_google_tag_manager\EventStorageService $eventStorageService
   */
  public function __construct(EventStorageService $eventStorageService) {
    $this->eventStorageService = $eventStorageService;
  }

  /**
   * @inheritdoc
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('commerce_google_tag_manager.event_storage'));
  }

  /**
   * Get all tracked events as JSON.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getEvents() {
    $events = $this->eventStorageService->getEvents();
    $this->eventStorageService->flush();

    return new JsonResponse($events);
  }

}
