<?php

namespace Drupal\commerce_gtm_enhanced_ecommerce;

use Drupal\commerce_gtm_enhanced_ecommerce\Event\AlterEventDataEvent;
use Drupal\commerce_gtm_enhanced_ecommerce\Event\EnhancedEcommerceEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Stores all tracked Enhanced Ecommerce events in the session.
 *
 * @package Drupal\commerce_gtm_enhanced_ecommerce
 */
class EventStorageService {

  const SESSION_KEY = 'commerce_gtm_enhanced_ecommerce_events';

  /**
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  private $session;

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private $eventDispatcher;

  /**
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   */
  public function __construct(RequestStack $requestStack, EventDispatcherInterface $eventDispatcher) {
    $this->session = $requestStack->getCurrentRequest()->getSession();
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
    return array_values($this->session->get(self::SESSION_KEY)) ?? [];
  }

  /**
   * Add event data to the storage.
   *
   * Computes a hash from the given event data to prevent storing
   * the same event multiple times.
   *
   * @param array $eventData
   *
   * @return $this
   */
  public function addEvent(array $eventData) {
    $events = $this->session->get(self::SESSION_KEY) ?? [];
    $hash = $this->hash($eventData);

    if (!isset($events[$hash])) {
      $event = new AlterEventDataEvent($eventData);
      $this->eventDispatcher->dispatch(EnhancedEcommerceEvents::ALTER_EVENT_DATA, $event);
      $events[$this->hash($eventData)] = $event->getEventData();
      $this->session->set(self::SESSION_KEY, $events);
    }

    return $this;
  }

  /**
   * Delete all stored event data.
   *
   * @return $this
   */
  public function flush() {
    $this->session->set(self::SESSION_KEY, []);

    return $this;
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
