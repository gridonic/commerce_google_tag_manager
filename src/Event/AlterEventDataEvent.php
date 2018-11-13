<?php

namespace Drupal\commerce_google_tag_manager\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event to alter Enhanced Ecommerce event data before being
 * sent to the Google Tag Manager.
 */
class AlterEventDataEvent extends Event {

  /**
   * @var array
   */
  private $eventData;

  /**
   * @param array $eventData
   */
  public function __construct(array $eventData) {
    $this->eventData = $eventData;
  }

  /**
   * @return array
   */
  public function getEventData() {
    return $this->eventData;
  }

  /**
   * @param array $eventData
   */
  public function setEventData(array $eventData) {
    $this->eventData = $eventData;
  }

}
