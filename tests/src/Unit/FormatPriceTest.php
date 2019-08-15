<?php

namespace Drupal\Tests\commerce_google_tag_manager\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\commerce_google_tag_manager\EventTrackerService;

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

  /**
   * @covers ::formatPrice
   *
   * @dataProvider pricesProvider
   */
  public function testFormatPrice($price, $expected) {
    $result = EventTrackerService::formatPrice($price);
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
