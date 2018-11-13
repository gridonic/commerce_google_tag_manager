<?php

namespace Drupal\Tests\commerce_gtm_enhanced_ecommerce\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\commerce_gtm_enhanced_ecommerce\EventTrackerService;
use Drupal\commerce_gtm_enhanced_ecommerce\EventStorageService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Tests\commerce_gtm_enhanced_ecommerce\Traits\InvokeMethodTrait;

/**
 * Tests the formatPrice of EventTrackerService class.
 *
 * @coversDefaultClass \Drupal\commerce_gtm_enhanced_ecommerce\EventTrackerService
 *
 * @group commerce
 * @group commerce_gtm_enhanced_ecommerce
 * @group commerce_gtm_enhanced_ecommerce_unit
 */
class formatPriceTest extends UnitTestCase {
  use InvokeMethodTrait;

  /**
   * @var \Drupal\commerce_gtm_enhanced_ecommerce\EventTrackerService
   */
  private $ecommerceEventTrackerService;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $event_storage = $this->prophesize(EventStorageService::class);
    $event_dispatcher = $this->prophesize(EventDispatcherInterface::class);

    $this->ecommerceEventTrackerService = new EventTrackerService($event_storage->reveal(), $event_dispatcher->reveal());
  }

  /**
   * ::covers formatPrice.
   *
   * @dataProvider pricesProvider
   */
  public function testFormatPrice($price, $expected) {
    $result = $this->invokeMethod($this->ecommerceEventTrackerService, 'formatPrice', [$price]);
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
      # Default & standard behavior.
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
      # Number should be truncat to 2 decimals maximum.
      [
        123.123,
        123.12,
      ],
      # Number should not be rounded up.
      [
        11.999,
        11.99,
      ],
      # No Thousands separators should be present in the output.
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

