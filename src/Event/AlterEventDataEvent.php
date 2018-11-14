<?php

namespace Drupal\commerce_google_tag_manager\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event to alter Enhanced Ecommerce event data.
 *
 * Allow you to alter the event  before being sent to the Google Tag Manager.
 */
class AlterEventDataEvent extends Event {

  /**
   * The event data.
   *
   * @var array
   *   The event data.
   */
  private $eventData;

  /**
   * Constructs a AlterEventDataEvent object.
   *
   * @param array $event_data
   *   The event data.
   */
  public function __construct(array $event_data) {
    $this->eventData = $event_data;
  }

  /**
   * Get event data.
   *
   * @return array
   *   The event data.
   */
  public function getEventData() {
    return $this->eventData;
  }

  /**
   * Set event data.
   *
   * @param array $event_data
   *   The event data.
   */
  public function setEventData(array $event_data) {
    $this->eventData = $event_data;
  }

}
