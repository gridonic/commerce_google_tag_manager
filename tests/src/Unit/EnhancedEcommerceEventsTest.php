<?php

namespace Drupal\Tests\commerce_google_tag_manager\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\commerce_google_tag_manager\Event\EnhancedEcommerceEvents;

/**
 * @coversDefaultClass \Drupal\commerce_google_tag_manager\Event\EnhancedEcommerceEvents
 *
 * @group commerce
 * @group commerce_google_tag_manager
 * @group commerce_google_tag_manager_unit
 */
class EnhancedEcommerceEventsTest extends UnitTestCase {

  /**
   * @covers \Drupal\commerce_google_tag_manager\Event\EnhancedEcommerceEvents
   *
   * @dataProvider eventNames
   */
  public function testEventNames($event_name, $expected) {
    $this->assertEquals($expected, $event_name);
  }

  /**
   * List of supported event with expected names.
   *
   * @return array
   *   The list of CONST names & string expected value.
   */
  public function eventNames() {
    return [
      [
        EnhancedEcommerceEvents::ALTER_PRODUCT,
        'commerce_google_tag_manager.alter_product',
      ],
      [
        EnhancedEcommerceEvents::ALTER_PRODUCT_PURCHASED_ENTITY,
        'commerce_google_tag_manager.alter_product_purchased_entity',
      ],
      [
        EnhancedEcommerceEvents::ALTER_EVENT_DATA,
        'commerce_google_tag_manager.alter_event_data',
      ],
      [
        EnhancedEcommerceEvents::TRACK_CHECKOUT_STEP,
        'commerce_google_tag_manager.track_checkout_step',
      ],
    ];
  }

}
