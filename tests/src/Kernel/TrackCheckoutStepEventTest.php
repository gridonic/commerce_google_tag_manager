<?php

namespace Drupal\Tests\commerce_google_tag_manager\Kernel;

use Drupal\commerce_google_tag_manager\Event\TrackCheckoutStepEvent;
use Drupal\commerce_price\Price;
use Drupal\profile\Entity\Profile;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;

/**
 * @coversDefaultClass \Drupal\commerce_google_tag_manager\Event\TrackCheckoutStepEvent
 *
 * @group commerce
 * @group commerce_google_tag_manager
 * @group commerce_google_tag_manager_kernel
 */
class TrackCheckoutStepEventTest extends CommerceKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'entity_reference_revisions',
    'profile',
  ];

  /**
   * A sample order.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $order;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('profile');

    $user = $this->createUser(['mail' => $this->randomString() . '@example.com']);

    $variation = ProductVariation::create([
      'type'   => 'default',
      'sku'    => strtolower($this->randomMachineName()),
      'title'  => $this->randomString(),
      'price'  => new Price('1.00', 'USD'),
      'status' => TRUE,
    ]);

    $product = Product::create([
      'type'  => 'default',
      'title' => $this->randomString(),
    ]);
    $product->save();
    $product->addVariation($variation)->save();

    $profile = Profile::create([
      'type' => 'customer',
    ]);
    $profile->save();
    $profile = $this->reloadEntity($profile);

    /** @var \Drupal\commerce_order\OrderItemStorageInterface $order_item_storage */
    $order_item_storage = $this->container->get('entity_type.manager')->getStorage('commerce_order_item');
    $order_item = $order_item_storage->createFromPurchasableEntity($variation);
    $order_item->save();
    $order = Order::create([
      'type'            => 'default',
      'state'           => 'draft',
      'mail'            => $user->getEmail(),
      'uid'             => $user->id(),
      'ip_address'      => '127.0.0.1',
      'order_number'    => '6',
      'billing_profile' => $profile,
      'store_id'        => $this->store->id(),
      'order_items'     => [$order_item],
    ]);
    $order->save();
    $this->order = $this->reloadEntity($order);
  }

  /**
   * @covers ::getStepIndex
   */
  public function testGetStepIndex() {
    $event = new TrackCheckoutStepEvent(1, $this->order);
    $this->assertIsInt($event->getStepIndex());
    $this->assertEquals(1, $event->getStepIndex());
  }

  /**
   * @covers ::getOrder
   */
  public function testGetOrder() {
    $event = new TrackCheckoutStepEvent(1, $this->order);
    $this->assertInstanceOf(OrderInterface::class, $event->getOrder());
    $this->assertSame($this->order, $event->getOrder());
  }

}
