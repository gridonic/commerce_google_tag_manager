<?php

namespace Drupal\Tests\commerce_google_tag_manager\Kernel;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_tax\Entity\TaxType;
use Drupal\Tests\commerce_google_tag_manager\Traits\InvokeMethodTrait;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product as CommerceProduct;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\profile\Entity\Profile;
use Drupal\commerce_order\Entity\Order;

/**
 * @coversDefaultClass \Drupal\commerce_google_tag_manager\EventTrackerService
 *
 * @group commerce
 * @group commerce_google_tag_manager
 * @group commerce_google_tag_manager_kernel
 */
class EventTrackerServicePurchaseTest extends CommerceKernelTestBase {
  use InvokeMethodTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'commerce_price_test',
    'entity_reference_revisions',
    'profile',
    'commerce_tax',
  ];

  /**
   * The Commerce GTM event tracker.
   *
   * @var \Drupal\commerce_google_tag_manager\EventTrackerService
   */
  private $eventTracker;

  /**
   * The tempstore object.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  private $tempStore;

  /**
   * The product to test against.
   *
   * @var \Drupal\commerce_product\Entity\
   */
  protected $product;

  /**
   * The order item.
   *
   * @var \Drupal\commerce_order\Entity\OrderItemInterface
   */
  protected $orderItem;

  /**
   * The second order item.
   *
   * @var \Drupal\commerce_order\Entity\OrderItemInterface
   */
  protected $orderItemAlt;

  /**
   * A sample user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * The customer profile.
   *
   * @var \Drupal\profile\Entity\ProfileInterface
   */
  protected $profile;

  /**
   * The loaded tax types.
   *
   * @var \Drupal\commerce_tax\Entity\TaxTypeInterface
   */
  protected $taxType;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('profile');
    $this->installSchema('system', ['key_value_expire']);
    $this->installConfig(['commerce_tax']);

    $this->eventTracker = $this->container->get('commerce_google_tag_manager.event_tracker');
    $this->tempStore = $this->container->get('tempstore.private')->get('commerce_google_tag_manager');

    $this->user = $this->createUser(['mail' => $this->randomString() . '@example.com']);

    $this->profile = Profile::create([
      'type' => 'customer',
      'address' => [
        'country_code' => 'US',
        'administrative_area' => 'SC',
      ],
    ]);
    $this->profile->save();

    // The default store is US-WI, so imagine that the US has VAT.
    $this->taxType = TaxType::create([
      'id' => 'us_vat',
      'label' => 'US VAT',
      'plugin' => 'custom',
      'configuration' => [
        'display_inclusive' => TRUE,
        'rates' => [
          [
            'id' => 'standard',
            'label' => 'Standard',
            'percentage' => '0.2',
          ],
        ],
        'territories' => [
          ['country_code' => 'US', 'administrative_area' => 'WI'],
          ['country_code' => 'US', 'administrative_area' => 'SC'],
        ],
      ],
    ]);
    $this->taxType->save();

    $this->product = CommerceProduct::create([
      'type'  => 'default',
      'title' => 'Lorem Ipsum',
    ]);
    $this->product->save();

    // The variations to test with.
    $variation = ProductVariation::create([
      'type' => 'default',
      'sku' => $this->randomString(10),
      'status' => TRUE,
      'price'  => new Price('11.00', 'USD'),
    ]);
    $variation->save();
    $this->product->addVariation($variation)->save();

    /** @var \Drupal\commerce_order\OrderItemStorageInterface $order_item_storage */
    $order_item_storage = $this->container->get('entity_type.manager')->getStorage('commerce_order_item');
    $this->orderItem = $order_item_storage->createFromPurchasableEntity($variation);
    $this->orderItem->save();

    $this->orderItemAlt = $order_item_storage->createFromPurchasableEntity($variation);
    $this->orderItemAlt->save();
  }

  /**
   * @covers ::purchase
   */
  public function testPurchaseOrder() {
    $profile = Profile::create([
      'type' => 'customer',
      'address' => [
        'country_code' => 'CH',
      ],
    ]);
    $profile->save();

    $order = Order::create([
      'type'            => 'default',
      'state'           => 'draft',
      'mail'            => $this->user->getEmail(),
      'uid'             => $this->user->id(),
      'ip_address'      => '127.0.0.1',
      'order_number'    => '6',
      'billing_profile' => $profile,
      'store_id'        => $this->store->id(),
      'order_items'     => [$this->orderItem],
    ]);
    $order->save();

    $this->assertEquals(new Price('11.00', 'USD'), $this->orderItem->getAdjustedTotalPrice());
    $this->assertEquals(new Price('11.00', 'USD'), $this->orderItem->getAdjustedUnitPrice());

    $this->assertEquals(new Price('11.00', 'USD'), $order->getTotalPrice());
    $this->assertEquals(new Price('11.00', 'USD'), $order->getSubtotalPrice());

    $order->recalculateTotalPrice();

    $this->assertEquals(new Price('11.00', 'USD'), $order->getTotalPrice());
    $this->assertEquals(new Price('11.00', 'USD'), $order->getSubtotalPrice());

    $this->invokeMethod($this->eventTracker, 'purchase', [$order]);
    $data = $this->tempStore->get('events');

    $this->assertSame([
      'fee6d54a92c4d3feab9fd1bc7af8943e' => [
        'event' => 'purchase',
        'ecommerce' => [
          'purchase' => [
            'actionField' => [
              'id' => '6',
              'affiliation' => 'Default store',
              'revenue' => '11.00',
              'shipping' => '0',
              'tax' => '0',
              'coupon' => '',
            ],
            'products' => [
              0 => [
                'name' => 'Lorem Ipsum',
                'id' => '1',
                'price' => '11.00',
                'variant' => 'Lorem Ipsum',
                'quantity' => 1,
              ],
            ],
          ],
        ],
      ],
    ], $data);
  }

  /**
   * @covers ::purchase
   */
  public function testPurchaseOrderWithTaxInclusiveOnOrder() {
    $this->store->set('prices_include_tax', TRUE);
    $this->store->save();

    $this->assertEquals(new Price('11.00', 'USD'), $this->orderItem->getAdjustedTotalPrice());
    $this->assertEquals(new Price('11.00', 'USD'), $this->orderItem->getAdjustedUnitPrice());

    $order = Order::create([
      'type'            => 'default',
      'state'           => 'draft',
      'mail'            => $this->user->getEmail(),
      'uid'             => $this->user->id(),
      'ip_address'      => '127.0.0.1',
      'order_number'    => '6',
      'billing_profile' => $this->profile,
      'store_id'        => $this->store->id(),
      'order_items'     => [$this->orderItem],
    ]);
    $order->save();

    $this->assertEquals(new Price('11.00', 'USD'), $order->getTotalPrice());
    $this->assertEquals(new Price('11.00', 'USD'), $order->getSubtotalPrice());

    $order->recalculateTotalPrice();

    $this->assertEquals(new Price('11.00', 'USD'), $order->getTotalPrice());
    $this->assertEquals(new Price('11.00', 'USD'), $order->getSubtotalPrice());

    $this->invokeMethod($this->eventTracker, 'purchase', [$order]);
    $data = $this->tempStore->get('events');

    $this->assertSame([
      '31951137eb8b9f4e8477bbc9ea62e072' => [
        'event' => 'purchase',
        'ecommerce' => [
          'purchase' => [
            'actionField' => [
              'id' => '6',
              'affiliation' => 'Default store',
              'revenue' => '11.00',
              'shipping' => '0',
              'tax' => '1.83',
              'coupon' => '',
            ],
            'products' => [
              0 => [
                'name' => 'Lorem Ipsum',
                'id' => '1',
                'price' => '11.00',
                'variant' => 'Lorem Ipsum',
                'quantity' => 1,
              ],
            ],
          ],
        ],
      ],
    ], $data);
  }

  /**
   * @covers ::purchase
   */
  public function testPurchaseOrderWithTaxExclusiveOnOrder() {
    $this->store->set('prices_include_tax', FALSE);
    $this->store->save();

    $order = Order::create([
      'type'            => 'default',
      'state'           => 'draft',
      'mail'            => $this->user->getEmail(),
      'uid'             => $this->user->id(),
      'ip_address'      => '127.0.0.1',
      'order_number'    => '6',
      'billing_profile' => $this->profile,
      'store_id'        => $this->store->id(),
      'order_items'     => [$this->orderItem],
    ]);
    $order->save();

    $this->assertEquals(new Price('13.2', 'USD'), $order->getTotalPrice());
    $this->assertEquals(new Price('13.2', 'USD'), $order->getSubtotalPrice());

    $order->recalculateTotalPrice();

    $this->assertEquals(new Price('13.2', 'USD'), $order->getTotalPrice());
    $this->assertEquals(new Price('13.2', 'USD'), $order->getSubtotalPrice());

    $this->invokeMethod($this->eventTracker, 'purchase', [$order]);
    $data = $this->tempStore->get('events');

    $this->assertSame([
      '32c4a9a074bd51142074d965d89398a4' => [
        'event' => 'purchase',
        'ecommerce' => [
          'purchase' => [
            'actionField' => [
              'id' => '6',
              'affiliation' => 'Default store',
              'revenue' => '13.20',
              'shipping' => '0',
              'tax' => '2.20',
              'coupon' => '',
            ],
            'products' => [
              0 => [
                'name' => 'Lorem Ipsum',
                'id' => '1',
                'price' => '11.00',
                'variant' => 'Lorem Ipsum',
                'quantity' => 1,
              ],
            ],
          ],
        ],
      ],
    ], $data);
  }

  /**
   * @covers ::purchase
   *
   * Tests the handling of tax-exempt customers with tax-inclusive prices.
   *
   * @see Drupal\Tests\commerce_tax\Kernel\OrderIntegrationTest::testTaxExemptPrices
   */
  public function testPurchaseOrderTaxExemptPrices() {
    $profile = Profile::create([
      'type' => 'customer',
      'address' => [
        'country_code' => 'CH',
      ],
    ]);
    $profile->save();

    $this->store->set('prices_include_tax', TRUE);
    $this->store->save();

    $this->assertEquals(new Price('11.00', 'USD'), $this->orderItem->getAdjustedTotalPrice());
    $this->assertEquals(new Price('11.00', 'USD'), $this->orderItem->getAdjustedUnitPrice());

    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = Order::create([
      'type'            => 'default',
      'state'           => 'draft',
      'mail'            => $this->user->getEmail(),
      'uid'             => $this->user->id(),
      'ip_address'      => '127.0.0.1',
      'order_number'    => '6',
      'billing_profile' => $profile,
      'store_id'        => $this->store->id(),
      'order_items'     => [$this->orderItem],
    ]);
    $order->save();

    // No taxes should be applied, but the price will be lower based on the
    // store billing zone (as so the default tax will be lower from the price).
    $this->assertEmpty($order->collectAdjustments());

    $this->assertEquals(new Price('9.17', 'USD'), $order->getTotalPrice());
    $this->assertEquals(new Price('9.17', 'USD'), $order->getSubtotalPrice());

    $order->recalculateTotalPrice();

    $this->assertEquals(new Price('9.17', 'USD'), $order->getTotalPrice());
    $this->assertEquals(new Price('9.17', 'USD'), $order->getSubtotalPrice());

    $this->invokeMethod($this->eventTracker, 'purchase', [$order]);
    $data = $this->tempStore->get('events');

    $this->assertSame([
      '8d7902747750b6d8ba873ebdd6122300' => [
        'event' => 'purchase',
        'ecommerce' => [
          'purchase' => [
            'actionField' => [
              'id' => '6',
              'affiliation' => 'Default store',
              'revenue' => '9.17',
              'shipping' => '0',
              'tax' => '0',
              'coupon' => '',
            ],
            'products' => [
              0 => [
                'name' => 'Lorem Ipsum',
                'id' => '1',
                // @todo Maybe we should rework the price resolver of product.
                // For now, it use the Commerce Price Resolver, but here we want
                // the order price (the lowered price) instead of field price.
                'price' => '11.00',
                'variant' => 'Lorem Ipsum',
                'quantity' => 1,
              ],
            ],
          ],
        ],
      ],
    ], $data);
  }

  /**
   * @covers ::purchase
   *
   * Test handling Taxes on Order item which are already included in the price.
   */
  public function testPurchaseOrderWithTaxInclusiveOnOrderItems() {
    $this->store->set('prices_include_tax', TRUE);
    $this->store->save();

    $this->assertEquals(new Price('11.00', 'USD'), $this->orderItem->getAdjustedTotalPrice());
    $this->assertEquals(new Price('11.00', 'USD'), $this->orderItem->getAdjustedUnitPrice());

    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = Order::create([
      'type'            => 'default',
      'state'           => 'draft',
      'mail'            => $this->user->getEmail(),
      'uid'             => $this->user->id(),
      'ip_address'      => '127.0.0.1',
      'order_number'    => '6',
      'billing_profile' => $this->profile,
      'store_id'        => $this->store->id(),
      'order_items'     => [$this->orderItem, $this->orderItemAlt],
    ]);
    $order->save();

    $this->orderItem->addAdjustment(new Adjustment([
      'type' => 'tax',
      'label' => 'Tax',
      // 2.75 USD.
      'amount' => $this->orderItem->getAdjustedUnitPrice()->multiply('0.25'),
      'percentage' => '0.25',
      'source_id' => '1',
      'included' => TRUE,
    ]));
    $this->orderItem->save();

    $this->assertEquals(new Price('11.00', 'USD'), $this->orderItem->getAdjustedTotalPrice());
    $this->assertEquals(new Price('11.00', 'USD'), $this->orderItem->getAdjustedUnitPrice());

    $this->orderItemAlt->addAdjustment(new Adjustment([
      'type' => 'tax',
      'label' => 'Tax',
      // 6.05 USD.
      'amount' => $this->orderItemAlt->getAdjustedUnitPrice()->multiply('0.55'),
      'percentage' => '0.55',
      'source_id' => '1',
      'included' => TRUE,
    ]));
    $this->orderItemAlt->save();

    $this->assertEquals(new Price('11.00', 'USD'), $this->orderItem->getAdjustedTotalPrice());
    $this->assertEquals(new Price('11.00', 'USD'), $this->orderItem->getAdjustedUnitPrice());

    // Only order-item taxes will be collected.
    $this->assertCount(2, $order->collectAdjustments());

    $this->invokeMethod($this->eventTracker, 'purchase', [$order]);
    $data = $this->tempStore->get('events');

    $this->assertEquals(new Price('22', 'USD'), $order->getTotalPrice());
    $this->assertEquals(new Price('22', 'USD'), $order->getSubtotalPrice());

    $order->recalculateTotalPrice();

    $this->assertEquals(new Price('22', 'USD'), $order->getTotalPrice());
    $this->assertEquals(new Price('22', 'USD'), $order->getSubtotalPrice());

    $this->assertSame([
      'a2508e22eb58b86593000e815192970f' => [
        'event' => 'purchase',
        'ecommerce' => [
          'purchase' => [
            'actionField' => [
              'id' => '6',
              'affiliation' => 'Default store',
              'revenue' => '22.00',
              'shipping' => '0',
              'tax' => '8.80',
              'coupon' => '',
            ],
            'products' => [
              0 => [
                'name' => 'Lorem Ipsum',
                'id' => '1',
                'price' => '11.00',
                'variant' => 'Lorem Ipsum',
                'quantity' => 1,
              ],
              1 => [
                'name' => 'Lorem Ipsum',
                'id' => '1',
                'price' => '11.00',
                'variant' => 'Lorem Ipsum',
                'quantity' => 1,
              ],
            ],
          ],
        ],
      ],
    ], $data);
  }

  /**
   * @covers ::purchase
   *
   * Test handling Taxes on Order item which are excluded from the price.
   */
  public function testPurchaseOrderWithTaxExclusiveOnOrderItems() {
    $this->store->set('prices_include_tax', FALSE);
    $this->store->save();

    $this->assertEquals(new Price('11.00', 'USD'), $this->orderItem->getAdjustedTotalPrice());
    $this->assertEquals(new Price('11.00', 'USD'), $this->orderItem->getAdjustedUnitPrice());
    $this->assertEquals(new Price('11.00', 'USD'), $this->orderItemAlt->getAdjustedTotalPrice());
    $this->assertEquals(new Price('11.00', 'USD'), $this->orderItemAlt->getAdjustedUnitPrice());

    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = Order::create([
      'type'            => 'default',
      'state'           => 'draft',
      'mail'            => $this->user->getEmail(),
      'uid'             => $this->user->id(),
      'ip_address'      => '127.0.0.1',
      'order_number'    => '6',
      'billing_profile' => $this->profile,
      'store_id'        => $this->store->id(),
      'order_items'     => [$this->orderItem, $this->orderItemAlt],
    ]);
    $order->save();

    $this->orderItem->addAdjustment(new Adjustment([
      'type' => 'tax',
      'label' => 'Tax',
      // 2.75 USD.
      'amount' => $this->orderItem->getAdjustedUnitPrice()->multiply('0.25'),
      'percentage' => '0.25',
      'source_id' => '1',
      'included' => FALSE,
    ]));
    $this->orderItem->save();

    $this->assertEquals(new Price('13.75', 'USD'), $this->orderItem->getAdjustedTotalPrice());
    $this->assertEquals(new Price('13.75', 'USD'), $this->orderItem->getAdjustedUnitPrice());

    $this->orderItemAlt->addAdjustment(new Adjustment([
      'type' => 'tax',
      'label' => 'Tax',
      // 6.05 USD.
      'amount' => $this->orderItemAlt->getAdjustedUnitPrice()->multiply('0.55'),
      'percentage' => '0.55',
      'source_id' => '1',
      'included' => FALSE,
    ]));
    $this->orderItemAlt->save();

    $this->assertEquals(new Price('17.05', 'USD'), $this->orderItemAlt->getAdjustedTotalPrice());
    $this->assertEquals(new Price('17.05', 'USD'), $this->orderItemAlt->getAdjustedUnitPrice());

    // Only order-item taxes will be collected.
    $this->assertCount(2, $order->collectAdjustments());

    $this->assertEquals(new Price('26.4', 'USD'), $order->getTotalPrice());
    $this->assertEquals(new Price('22', 'USD'), $order->getSubtotalPrice());

    $order->recalculateTotalPrice();

    $this->assertEquals(new Price('30.8', 'USD'), $order->getTotalPrice());
    $this->assertEquals(new Price('22', 'USD'), $order->getSubtotalPrice());

    $this->invokeMethod($this->eventTracker, 'purchase', [$order]);
    $data = $this->tempStore->get('events');

    $this->assertSame([
      'e4c5391625f71636d7ff637b725a4d8f' => [
        'event' => 'purchase',
        'ecommerce' => [
          'purchase' => [
            'actionField' => [
              'id' => '6',
              'affiliation' => 'Default store',
              'revenue' => '30.80',
              'shipping' => '0',
              'tax' => '8.80',
              'coupon' => '',
            ],
            'products' => [
              0 => [
                'name' => 'Lorem Ipsum',
                'id' => '1',
                'price' => '11.00',
                'variant' => 'Lorem Ipsum',
                'quantity' => 1,
              ],
              1 => [
                'name' => 'Lorem Ipsum',
                'id' => '1',
                'price' => '11.00',
                'variant' => 'Lorem Ipsum',
                'quantity' => 1,
              ],
            ],
          ],
        ],
      ],
    ], $data);
  }

}
