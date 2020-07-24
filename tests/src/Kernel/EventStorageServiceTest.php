<?php

namespace Drupal\Tests\commerce_google_tag_manager\Kernel;

use Drupal\commerce_google_tag_manager\EventTrackerService;

/**
 * @coversDefaultClass \Drupal\commerce_google_tag_manager\EventStorageService
 *
 * @group commerce
 * @group commerce_google_tag_manager
 * @group commerce_google_tag_manager_kernel
 */
class EventStorageServiceTest extends CommerceKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'state_machine',
    'entity_reference_revisions',
    'profile',
  ];

  /**
   * The tempstore object.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  private $tempStore;

  /**
   * The Commerce GTM event storage.
   *
   * @var \Drupal\commerce_google_tag_manager\EventStorageService
   */
  private $eventStorage;

  /**
   * The Google Tag Manager Product Detail view event structure to test with.
   *
   * @var array
   */
  protected $detailEvent;

  /**
   * The Google Tag Manager Checkout event structure to test with.
   *
   * @var array
   */
  protected $checkoutEvent;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('profile');

    $this->installSchema('system', ['key_value_expire']);

    $this->tempStore = $this->container->get('tempstore.private')->get('commerce_google_tag_manager');
    $this->eventStorage = $this->container->get('commerce_google_tag_manager.event_storage');

    $this->detailEvent = [
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

    $this->checkoutEvent = [
      'event' => EventTrackerService::EVENT_CHECKOUT,
      'ecommerce' => [
        'checkout' => [
          'actionField' => [
            'step' => 1,
          ],
          'products' => [
            0 => [
              'name'    => 'Lorem Ipsum',
              'id'      => '1',
              'price'   => '11.99',
              'variant' => 'Lorem Ipsum',
              'quantity' => 1,
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * @covers ::getEvents
   */
  public function testGetEvents() {
    $this->tempStore->set('events', [
      '0e05cdf318b5832a7caf62ad11d386f4' => $this->detailEvent,
    ]);
    $result = $this->eventStorage->getEvents();
    $this->assertSame([0 => $this->detailEvent], $result);
  }

  /**
   * @covers ::getEvents
   */
  public function testGetEventsEmpty() {
    $this->tempStore->set('events', NULL);
    $result = $this->eventStorage->getEvents();
    $this->assertIsArray($result);
    $this->assertEmpty($result);
  }

  /**
   * @covers ::flush
   */
  public function testFlush() {
    $this->tempStore->set('events', [
      '0e05cdf318b5832a7caf62ad11d386f4' => $this->detailEvent,
    ]);
    $this->eventStorage->flush();
    $result = $this->tempStore->get('events');
    $this->assertNull($result);
  }

  /**
   * @covers ::addEvent
   *
   * Asserts usage of Private Temp Store for events queue.
   */
  public function testAddEvent() {
    $this->eventStorage->addEvent($this->detailEvent);

    $result = $this->tempStore->get('events');
    $this->assertIsArray($result);
    $this->assertSame([
      '0e05cdf318b5832a7caf62ad11d386f4' => $this->detailEvent,
    ], $result);
  }

  /**
   * @covers ::addEvent
   *
   * Asserts the events queue follows the FIFO (First in First out) pattern.
   */
  public function testAddEventFifoQueue() {
    $this->eventStorage->addEvent($this->detailEvent);
    $this->eventStorage->addEvent($this->checkoutEvent);
    $events = $this->tempStore->get('events');

    $this->assertSame([
      '0e05cdf318b5832a7caf62ad11d386f4' => $this->detailEvent,
      '5d92a6ab1f5bd49c7ac5a065302dcb16' => $this->checkoutEvent,
    ], $events);
  }

  /**
   * @covers ::addEvent
   *
   * Asserts strictly same event aren't added twice in the events queue.
   */
  public function testAddEventSameSkipped() {
    $this->eventStorage->addEvent($this->detailEvent);
    $this->eventStorage->addEvent($this->detailEvent);
    $events = $this->tempStore->get('events');
    $this->assertSame([
      '0e05cdf318b5832a7caf62ad11d386f4' => $this->detailEvent,
    ], $events);
  }

}
