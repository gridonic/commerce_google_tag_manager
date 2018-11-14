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
   * The Commerce GTM event storage.
   *
   * @var \Drupal\commerce_google_tag_manager\EventStorageService
   */
  private $eventStorage;

  /**
   * Constructs the EventsController object.
   *
   * @param \Drupal\commerce_google_tag_manager\EventStorageService $event_storage
   *   The Commerce GTM event storage.
   */
  public function __construct(EventStorageService $event_storage) {
    $this->eventStorage = $event_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('commerce_google_tag_manager.event_storage'));
  }

  /**
   * Get all tracked events as JSON.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The event format as JSON.
   */
  public function getEvents() {
    $events = $this->eventStorage->getEvents();
    $this->eventStorage->flush();

    return new JsonResponse($events);
  }

}
