<?php

namespace Drupal\Tests\commerce_google_tag_manager\Kernel;

use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;
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
    'commerce_checkout',
    'commerce_google_tag_manager',
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
   * The Google Tag Manager events structure to test with.
   *
   * @var array
   */
  protected $events;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installSchema('system', ['key_value_expire']);

    $this->tempStore = $this->container->get('tempstore.private')->get('commerce_google_tag_manager');
    $this->eventStorage = $this->container->get('commerce_google_tag_manager.event_storage');
    $this->events = [
      '0e05cdf318b5832a7caf62ad11d386f4' => [
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
      ],
    ];
  }

  /**
   * @covers ::getEvents
   */
  public function testGetEvents() {
    $this->tempStore->set('events', $this->events);
    $result = $this->eventStorage->getEvents();
    $this->assertSame([
      0 => [
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
      ],
    ], $result);
  }

  /**
   * @covers ::getEvents
   */
  public function testGetEventsEmpty() {
    $this->tempStore->set('events', NULL);
    $result = $this->eventStorage->getEvents();
    $this->assertInternalType('array', $result);
    $this->assertEmpty($result);
  }

  /**
   * @covers ::flush
   */
  public function testFlush() {
    $this->tempStore->set('events', $this->events);
    $this->eventStorage->flush();
    $result = $this->tempStore->get('events');
    $this->assertNull($result);
  }

}
