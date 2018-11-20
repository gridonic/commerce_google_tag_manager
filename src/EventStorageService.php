<?php

namespace Drupal\commerce_google_tag_manager;

use Drupal\commerce_google_tag_manager\Event\AlterEventDataEvent;
use Drupal\commerce_google_tag_manager\Event\EnhancedEcommerceEvents;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Stores all tracked Enhanced Ecommerce events in a private tempstore.
 */
class EventStorageService {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private $eventDispatcher;

  /**
   * The tempstore object.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  private $tempStore;

  /**
   * Constructs the EventTrackerService service.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, EventDispatcherInterface $event_dispatcher) {
    $this->tempStore = $temp_store_factory->get('commerce_google_tag_manager');
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * Get all tracked Enhanced Ecommerce events.
   *
   * Note: If you send the events to Google Tag Manager, make
   * sure to flush event data afterwards to prevent double tracking.
   *
   * @return array
   *   All tracked Enhanced Ecommerce events.
   */
  public function getEvents() {
    $events = $this->tempStore->get('events') ?: [];

    return array_values($events);
  }

  /**
   * Add event data to the storage.
   *
   * Computes a hash from the given event data to prevent storing
   * the exact same event multiple times.
   *
   * @param array $event_data
   *   The event data to store.
   *
   * @return $this
   *   Return the EventStorageService object.
   */
  public function addEvent(array $event_data) {
    $events = (array) $this->tempStore->get('events') ?: [];
    $hash = $this->hash($event_data);

    if (!isset($events[$hash])) {
      $event = new AlterEventDataEvent($event_data);
      $this->eventDispatcher->dispatch(EnhancedEcommerceEvents::ALTER_EVENT_DATA, $event);
      $events[$this->hash($event_data)] = $event->getEventData();
      $this->tempStore->set('events', $events);
    }

    return $this;
  }

  /**
   * Delete all stored event data.
   *
   * @return bool
   *   The flush results.
   */
  public function flush() {
    return $this->tempStore->delete('events');
  }

  /**
   * Compute a hash from the given event data.
   *
   * @param array $event_data
   *   The event data to store.
   *
   * @return string
   *   The hash value.
   */
  private function hash(array $event_data) {
    return md5(json_encode($event_data));
  }

}
