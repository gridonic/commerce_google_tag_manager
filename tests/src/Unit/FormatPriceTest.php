<?php

namespace Drupal\Tests\commerce_google_tag_manager\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\commerce_google_tag_manager\EventTrackerService;
use Drupal\commerce_google_tag_manager\EventStorageService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Tests\commerce_google_tag_manager\Traits\InvokeMethodTrait;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\commerce_order\PriceCalculatorInterface;

/**
 * Tests the formatPrice of EventTrackerService class.
 *
 * @coversDefaultClass \Drupal\commerce_google_tag_manager\EventTrackerService
 *
 * @group commerce
 * @group commerce_google_tag_manager
 * @group commerce_google_tag_manager_unit
 */
class FormatPriceTest extends UnitTestCase {
  use InvokeMethodTrait;

  /**
   * The Commerce GTM event tracker.
   *
   * @var \Drupal\commerce_google_tag_manager\EventTrackerService
   */
  private $eventTracker;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $event_storage = $this->prophesize(EventStorageService::class);
    $event_dispatcher = $this->prophesize(EventDispatcherInterface::class);

    $current_store = $this->prophesize(CurrentStoreInterface::class);
    $current_user = $this->prophesize(AccountInterface::class);
    $price_calculator = $this->prophesize(PriceCalculatorInterface::class);

    $store = $this->prophesize(StoreInterface::class);
    $current_store->getStore()->willReturn($store->reveal());

    $this->eventTracker = new EventTrackerService($event_storage->reveal(), $event_dispatcher->reveal(), $current_store->reveal(), $current_user->reveal(), $price_calculator->reveal());
  }

  /**
   * @covers ::formatPrice
   *
   * @dataProvider pricesProvider
   */
  public function testFormatPrice($price, $expected) {
    $result = $this->invokeMethod($this->eventTracker, 'formatPrice', [$price]);
    $this->assertEquals($expected, $result);
  }

  /**
   * Prices to format.
   *
   * @return array
   *   The prices.
   */
  public function pricesProvider() {
    return [
      // Default & standard behavior.
      [
        0,
        0,
      ],
      [
        12,
        12,
      ],
      [
        11.99,
        11.99,
      ],
      // Number should be truncat to 2 decimals maximum.
      [
        123.123,
        123.12,
      ],
      // Number should not be rounded up.
      [
        11.999,
        11.99,
      ],
      // No Thousands separators should be present in the output.
      [
        43123,
        43123,
      ],
      [
        43123.987,
        43123.98,
      ],
    ];
  }

}
