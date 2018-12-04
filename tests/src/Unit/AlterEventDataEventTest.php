<?php

namespace Drupal\Tests\commerce_google_tag_manager\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\commerce_google_tag_manager\Event\AlterEventDataEvent;
use Drupal\commerce_google_tag_manager\EventTrackerService;

/**
 * @coversDefaultClass \Drupal\commerce_google_tag_manager\Event\AlterEventDataEvent
 *
 * @group commerce
 * @group commerce_google_tag_manager
 * @group commerce_google_tag_manager_unit
 */
class AlterEventDataEventTest extends UnitTestCase {

  /**
   * The event to tests against.
   *
   * @var \Drupal\commerce_google_tag_manager\Event\AlterEventDataEvent
   */
  protected $alterEventData;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->alterEventData = new AlterEventDataEvent([]);
  }

  /**
   * @covers ::setEventData
   * @covers ::getEventData
   *
   * @dataProvider eventData
   */
  public function testSetEventDetailViews($event_data) {
    $this->alterEventData->setEventData($event_data);
    $this->assertSame($event_data, $this->alterEventData->getEventData());
  }

  /**
   * List of supported event data.
   *
   * @return array
   *   Examples of event data structure by event types.
   */
  public function eventData() {
    return [
      'Product detail views' => [
        [
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
      ],

      // @todo add product impression example.
      'Product impressions' => [
        [
          'event' => EventTrackerService::EVENT_PRODUCT_IMPRESSIONS,
          'ecommerce' => [],
        ],
      ],

      // @todo add product click example.
      'Product click' => [
        [
          'event' => EventTrackerService::EVENT_PRODUCT_CLICK,
          'ecommerce' => [],
        ],
      ],

      'Add to cart' => [
        [
          'event' => EventTrackerService::EVENT_ADD_CART,
          'ecommerce' => [
            'currencyCode' => 'CHF',
            'add' => [
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
        ],
      ],

      'Remove to cart' => [
        [
          'event' => EventTrackerService::EVENT_REMOVE_CART,
          'ecommerce' => [
            'remove' => [
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
        ],
      ],

      'Checkout' => [
        [
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
        ],
      ],

      // @todo add checkout option example.
      'Checkout option' => [
        [
          'event' => EventTrackerService::EVENT_CHECKOUT_OPTION,
          'ecommerce' => [],
        ],
      ],

      'Purchase' => [
        [
          'event' => EventTrackerService::EVENT_PURCHASE,
          'ecommerce' => [
            'purchase' => [
              'actionField' => [
                'id' => '1',
                'affiliation' => 'Commerce Website',
                'revenue' => '11.99',
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
        ],
      ],
    ];
  }

}
