<?php

namespace Drupal\Tests\commerce_google_tag_manager\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\commerce_google_tag_manager\EventStorageService;
use Drupal\Tests\commerce_google_tag_manager\Traits\InvokeMethodTrait;
use Drupal\commerce_google_tag_manager\EventTrackerService;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TempStore\PrivateTempStore;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\commerce_google_tag_manager\Event\EnhancedEcommerceEvents;
use Drupal\commerce_google_tag_manager\Event\AlterEventDataEvent;

/**
 * @coversDefaultClass \Drupal\commerce_google_tag_manager\EventStorageService
 *
 * @group commerce
 * @group commerce_google_tag_manager
 * @group commerce_google_tag_manager_unit
 */
class EventStorageServiceTest extends UnitTestCase {
  use InvokeMethodTrait;

  /**
   * The Commerce GTM event storage.
   *
   * @var \Drupal\commerce_google_tag_manager\EventStorageService
   */
  private $eventStorage;

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
   * Event to alter Enhanced Ecommerce event data.
   *
   * @var \Drupal\commerce_google_tag_manager\Event\AlterEventDataEvent
   */
  private $alterEventDataEvent;

  /**
   * The Google Tag Manager event structure to test with.
   *
   * @var array
   */
  protected $event;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->tempStore       = $this->getMockBuilder(PrivateTempStoreFactory::class)
      ->disableOriginalConstructor()->getMock();
    $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)
      ->disableOriginalConstructor()->getMock();

    $this->eventStorage = new EventStorageService($this->tempStore, $this->eventDispatcher);

    $this->event = [
      'event' => EventTrackerService::EVENT_PRODUCT_DETAIL_VIEWS,
      'ecommerce' => [
        'detail' => [
          'actionField' => [
            'list' => '',
          ],
          'products' => [
            0 => [
              'name'    => 'Lorem Ipsum',
              'id'      => '1',
              'price'   => '11.99',
              'variant' => 'Lorem Ipsum',
            ],
          ],
        ],
      ],
    ];
    $this->alterEventDataEvent = new AlterEventDataEvent($this->event);
  }

  /**
   * @covers ::hash
   */
  public function testHash() {
    $result = $this->invokeMethod($this->eventStorage, 'hash', [$this->event]);
    $this->assertEquals('0e05cdf318b5832a7caf62ad11d386f4', $result);
  }

  /**
   * When addEvent called on a new event, we should fire an Event Alter.
   *
   * This should occure only when the added event has not already been added.
   * This will then dispatch EnhancedEcommerceEvents::ALTER_EVENT_DATA.
   *
   * @covers ::addEvent
   */
  public function testAddEventDispatchAlter() {
    // Mock stored events, make sure the event has never been added.
    $stored_events = $this->getMockBuilder(PrivateTempStore::class)
      ->disableOriginalConstructor()->getMock();
    $stored_events->expects($this->once())
      ->method('get')
      ->with('events')
      ->willReturn([]);

    // Re-Mock the Private Temp Store & The Symfony Event Dispatcher.
    $this->tempStore->expects($this->once())
      ->method('get')
      ->with('commerce_google_tag_manager')
      ->willReturn($stored_events);
    $this->eventDispatcher->expects($this->once())
      ->method('dispatch')
      ->with(EnhancedEcommerceEvents::ALTER_EVENT_DATA, $this->alterEventDataEvent);

    $this->eventStorage = new EventStorageService($this->tempStore, $this->eventDispatcher);

    $this->eventStorage->addEvent($this->event);
  }

  /**
   * When addEvent called on a un-new event, we should not fire an Event Alter.
   *
   * This should occure only when the added event has already been added.
   * This will then not dispatch EnhancedEcommerceEvents::ALTER_EVENT_DATA.
   *
   * @covers ::addEvent
   */
  public function testAddEventShouldDispatchAlterOnlyOnce() {
    // Mock stored events, make sure the event has already been added.
    $stored_events = $this->getMockBuilder(PrivateTempStore::class)
      ->disableOriginalConstructor()->getMock();
    $stored_events->expects($this->once())
      ->method('get')
      ->with('events')
      ->willReturn(['0e05cdf318b5832a7caf62ad11d386f4' => $this->event]);

    // Re-Mock the Private Temp Store & The Symfony Event Dispatcher.
    $this->tempStore->expects($this->once())
      ->method('get')
      ->with('commerce_google_tag_manager')
      ->willReturn($stored_events);
    $this->eventDispatcher->expects($this->never())
      ->method('dispatch');

    $this->eventStorage = new EventStorageService($this->tempStore, $this->eventDispatcher);

    $this->eventStorage->addEvent($this->event);
  }

}
