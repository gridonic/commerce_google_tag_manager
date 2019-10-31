<?php

namespace Drupal\Tests\commerce_google_tag_manager\Kernel;

use Drupal\Tests\commerce_google_tag_manager\Traits\InvokeMethodTrait;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product as CommerceProduct;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_google_tag_manager\Product;

/**
 * @coversDefaultClass \Drupal\commerce_google_tag_manager\EventTrackerService
 *
 * @group commerce
 * @group commerce_google_tag_manager
 * @group commerce_google_tag_manager_kernel
 */
class EventTrackerServiceTest extends CommerceKernelTestBase {
  use InvokeMethodTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'commerce_price_test',
    'entity_reference_revisions',
    'profile',
  ];

  /**
   * The Commerce GTM event tracker.
   *
   * @var \Drupal\commerce_google_tag_manager\EventTrackerService
   */
  private $eventTracker;

  /**
   * The product to test against.
   *
   * @var \Drupal\commerce_product\Entity\
   */
  protected $product;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('profile');

    $this->eventTracker = $this->container->get('commerce_google_tag_manager.event_tracker');

    $this->product = CommerceProduct::create([
      'type'  => 'default',
      'title' => 'Lorem Ipsum',
    ]);
    $this->product->save();
  }

  /**
   * @covers ::buildProductFromProductVariation
   */
  public function testBuildProductFromProductVariation() {
    // The variations to test with.
    $variation = ProductVariation::create([
      'type' => 'default',
      'sku' => $this->randomString(10),
      'status' => TRUE,
      'price'  => new Price('11.00', 'USD'),
    ]);
    $variation->save();
    $this->product->addVariation($variation)->save();

    $result = $this->invokeMethod($this->eventTracker, 'buildProductFromProductVariation', [$variation]);
    $this->assertInstanceOf(Product::class, $result);
    $this->assertEquals($result->getPrice(), 11);
  }

  /**
   * @covers ::buildProductFromProductVariation
   *
   * @uses \Drupal\commerce_price_test\TestPriceResolver
   */
  public function testBuildProductFromProductVariationCalculatedPrice() {
    // The variations to test with.
    $variation = ProductVariation::create([
      'type' => 'default',
      // Only product starting with TEST_ will pass
      // the Drupal\commerce_price_test\TestPriceResolver.
      'sku' => 'TEST_CALCULATED_PRICE2',
      'status' => TRUE,
      'price' => new Price('11.00', 'USD'),
    ]);
    $variation->save();
    $this->product->addVariation($variation)->save();

    $result = $this->invokeMethod($this->eventTracker, 'buildProductFromProductVariation', [$variation]);
    $this->assertInstanceOf(Product::class, $result);
    $this->assertEquals($result->getPrice(), 8);
  }

  /**
   * @covers ::buildProductFromProductVariation
   */
  public function testBuildProductFromProductVariationNoPrice() {
    // The variations to test with.
    $variation = ProductVariation::create([
      'type' => 'default',
      'sku' => $this->randomString(10),
      'status' => TRUE,
    ]);
    $variation->save();
    $this->product->addVariation($variation)->save();

    $this->expectException(\TypeError::class);
    $this->expectExceptionMessage('Argument 1 passed to Drupal\commerce_order\PriceCalculatorResult::__construct() must be an instance of Drupal\commerce_price\Price, null given');

    $this->invokeMethod($this->eventTracker, 'buildProductFromProductVariation', [$variation]);
  }

}
