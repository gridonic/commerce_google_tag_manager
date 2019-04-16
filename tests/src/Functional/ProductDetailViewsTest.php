<?php

namespace Drupal\Tests\commerce_google_tag_manager\Functional;

use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;

/**
 * Cover hook_entity_view() implemented by commerce_google_tag_manager.module.
 *
 * @group commerce
 * @group commerce_google_tag_manager
 * @group commerce_google_tag_manager_functional
 */
class ProductDetailViewsTest extends CommerceBrowserTestBase {

  /**
   * The temp store holding the Enhanced Ecommerce event data.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStore;

  /**
   * The product to test against.
   *
   * @var \Drupal\commerce_product\Entity\
   */
  protected $product;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'commerce_product',
    'google_tag',
    'commerce_google_tag_manager',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->tempStore = $this->container->get('tempstore.private')->get('commerce_google_tag_manager');

    $this->product = Product::create([
      'type'  => 'default',
      'title' => 'Lorem Ipsum',
    ]);
    $this->product->save();
  }

  /**
   * Cover commerce_google_tag_manager_commerce_product_view.
   */
  public function testProductDetailViews() {
    $variation = ProductVariation::create([
      'type'   => 'default',
      'sku'    => 'lorem-ipsum-120',
      'title'  => 'Lorem Ipsum',
      'price'  => new Price('120.00', 'USD'),
      'status' => TRUE,
    ]);

    $this->product->addVariation($variation)->save();

    $this->drupalGet($this->product->toUrl()->toString());
    $this->assertResponse(200);

    $events = $this->tempStore->get('events');
    $this->assertSame([
      'f8e84d8ee071e2fb885d0dc755dd73ab' => [
        'event' => 'productDetailViews',
        'ecommerce' => [
          'detail' => [
            'actionField' => [
              'list' => '',
            ],
            'products' => [
              0 => [
                'name'    => 'Lorem Ipsum',
                'id'      => '1',
                'price'   => '120.00',
                'variant' => 'Lorem Ipsum',
              ],
            ],
          ],
        ],
      ],
    ], $events);
  }

  /**
   * Cover commerce_google_tag_manager_commerce_product_view.
   *
   * Test that the module does not track the productDetailViews event if
   * no default variation exists.
   */
  public function testMissingDefaultVariation() {
    $this->drupalGet($this->product->toUrl()->toString());
    $this->assertResponse(200);

    $events = $this->tempStore->get('events');
    $this->assertNull($events);
  }

}
