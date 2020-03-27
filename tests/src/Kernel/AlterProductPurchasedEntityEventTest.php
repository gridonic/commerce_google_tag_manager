<?php

namespace Drupal\Tests\commerce_google_tag_manager\Kernel;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_google_tag_manager\Event\AlterProductPurchasedEntityEvent;
use Drupal\commerce_google_tag_manager\Product;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_product\Entity\ProductVariation;

/**
 * @coversDefaultClass \Drupal\commerce_google_tag_manager\Event\AlterProductPurchasedEntityEvent
 *
 * @group commerce
 * @group commerce_google_tag_manager
 * @group commerce_google_tag_manager_kernel
 */
class AlterProductPurchasedEntityEventTest extends CommerceKernelTestBase {

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
   * The variations to test with.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  protected $purchasedEntity;

  /**
   * The GTM product to test against.
   *
   * @var \Drupal\commerce_google_tag_manager\Product
   */
  protected $product;

  /**
   * The order item to test with.
   *
   * @var \Drupal\commerce_order\Entity\OrderItemInterface
   */
  protected $orderItem;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // The purchased entity to test with.
    $this->purchasedEntity = ProductVariation::create([
      'type'   => 'default',
      'sku'    => $this->randomString(10),
      'status' => TRUE,
    ]);
    $this->purchasedEntity->save();

    $order_item_storage = $this->container->get('entity_type.manager')
      ->getStorage('commerce_order_item');
    $this->orderItem = $order_item_storage->createFromPurchasableEntity($this->purchasedEntity, [
      'quantity' => 1,
    ]);

    $this->product = new Product();
    $this->product
      ->setName($this->randomString(16))
      ->setId(1)
      ->setVariant($this->purchasedEntity->getTitle())
      ->setPrice('11.99');
  }

  /**
   * @covers ::getProduct
   */
  public function testGetProduct() {
    $event = new AlterProductPurchasedEntityEvent($this->product, $this->orderItem, $this->purchasedEntity);
    $this->assertInstanceOf(Product::class, $event->getProduct());
  }

  /**
   * @covers ::getOrderItem
   */
  public function testGetOrderItem() {
    $event = new AlterProductPurchasedEntityEvent($this->product, $this->orderItem, $this->purchasedEntity);
    $this->assertInstanceOf(OrderItemInterface::class, $event->getOrderItem());
  }

  /**
   * @covers ::getPurchasedEntity
   */
  public function testGetPurchasedEntity() {
    $event = new AlterProductPurchasedEntityEvent($this->product, $this->orderItem, $this->purchasedEntity);
    $this->assertInstanceOf(PurchasableEntityInterface::class, $event->getPurchasedEntity());
    $event = new AlterProductPurchasedEntityEvent($this->product, $this->orderItem, NULL);
    $this->assertNull($event->getPurchasedEntity());
  }

}
