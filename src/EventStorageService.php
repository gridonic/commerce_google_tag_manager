<?php

namespace Drupal\commerce_google_tag_manager;

use Drupal\commerce_google_tag_manager\Event\AlterEventDataEvent;
use Drupal\commerce_google_tag_manager\Event\EnhancedEcommerceEvents;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TempStore\TempStoreException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Stores all tracked Enhanced Ecommerce events in a private tempstore.
 */
class EventStorageService {

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private $eventDispatcher;

  /**
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  private $tempStore;

  /**
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $privateTempStoreFactory
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   */
  public function __construct(PrivateTempStoreFactory $privateTempStoreFactory, EventDispatcherInterface $eventDispatcher) {
    $this->tempStore = $privateTempStoreFactory->get('commerce_google_tag_manager');
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * Get all tracked Enhanced Ecommerce events.
   *
   * Note: If you send the events to Google Tag Manager, make
   * sure to flush event data afterwards to prevent double tracking.
   *
   * @return array
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
   * @param array $eventData
   *
   * @return $this
   */
  public function addEvent(array $eventData) {
    $events = (array) $this->tempStore->get('events') ?: [];
    $hash = $this->hash($eventData);

    if (!isset($events[$hash])) {
      $event = new AlterEventDataEvent($eventData);
      $this->eventDispatcher->dispatch(EnhancedEcommerceEvents::ALTER_EVENT_DATA, $event);
      $events[$this->hash($eventData)] = $event->getEventData();
      $this->tempStore->set('events', $events);
    }

    return $this;
  }

  /**
   * Delete all stored event data.
   *
   * @return bool
   */
  public function flush() {
    return $this->tempStore->delete('events');
  }

  /**
   * Compute a hash from the given event data.
   *
   * @param array $eventData
   *
   * @return string
   */
  private function hash(array $eventData) {
    return md5(json_encode($eventData));
  }
}
