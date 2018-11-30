<?php

namespace Drupal\Tests\commerce_google_tag_manager\Kernel;

use Drupal\commerce_google_tag_manager\Event\AlterProductEvent;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_google_tag_manager\Product;

/**
 * @coversDefaultClass \Drupal\commerce_google_tag_manager\Event\AlterProductEvent
 *
 * @group commerce
 * @group commerce_google_tag_manager
 * @group commerce_google_tag_manager_kernel
 */
class AlterProductEventTest extends CommerceKernelTestBase {

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
  protected $variation;

  /**
   * The GTM product to test against.
   *
   * @var \Drupal\commerce_google_tag_manager\Product
   */
  protected $product;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // The variations to test with.
    $this->variation = ProductVariation::create([
      'type'   => 'default',
      'sku'    => $this->randomString(10),
      'status' => TRUE,
    ]);
    $this->variation->save();

    $this->product = new Product();
    $this->product
      ->setName($this->randomString(16))
      ->setId(1)
      ->setVariant($this->variation->getTitle())
      ->setPrice('11.99');
  }

  /**
   * @covers ::getProduct
   */
  public function testGetProduct() {
    $event = new AlterProductEvent($this->product, $this->variation);
    $this->assertInstanceOf(Product::class, $event->getProduct());
  }

  /**
   * @covers ::getProductVariation
   */
  public function testGetProductVariation() {
    $event = new AlterProductEvent($this->product, $this->variation);
    $this->assertInstanceOf(ProductVariationInterface::class, $event->getProductVariation());
  }

}
