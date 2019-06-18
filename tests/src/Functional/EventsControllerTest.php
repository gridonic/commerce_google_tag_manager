<?php

namespace Drupal\Tests\commerce_google_tag_manager\Functional;

use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Drupal\Core\Url;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;

/**
 * @coversDefaultClass \Drupal\commerce_google_tag_manager\Controller\EventsController
 *
 * @group commerce
 * @group commerce_google_tag_manager
 * @group commerce_google_tag_manager_functional
 */
class EventsControllerTest extends CommerceBrowserTestBase {

  /**
   * The product to test againts.
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

    // Assert the current user is Anonymous.
    $this->drupalLogout();

    // Give anonymous users permission to access content.
    $user_role = Role::load(RoleInterface::ANONYMOUS_ID);
    $user_role->grantPermission('access content');
    $user_role->save();

    $variation = ProductVariation::create([
      'type'   => 'default',
      'sku'    => 'lorem-ipsum-120',
      'title'  => 'Lorem Ipsum',
      'price'  => new Price('120.00', 'USD'),
      'status' => TRUE,
    ]);

    $this->product = Product::create([
      'type'  => 'default',
      'title' => 'Lorem Ipsum',
    ]);
    $this->product->save();
    $this->product->addVariation($variation)->save();
  }

  /**
   * @covers ::getEvents
   */
  public function testGetEvents() {
    // Go to a product page to fire a EVENT_PRODUCT_DETAIL_VIEWS event.
    $this->drupalGet($this->product->toUrl()->toString());
    $this->assertSession()->statusCodeEquals(200);

    // Assert the previous event has been stored.
    $url = Url::fromRoute('commerce_google_tag_manager.events');
    $content = $this->drupalGet($url);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSame('[{"event":"productDetailViews","ecommerce":{"detail":{"actionField":{"list":""},"products":[{"name":"Lorem Ipsum","id":"1","price":"120.00","variant":"Lorem Ipsum"}]}}}]', (string) $content);

    // Assert stored events are flush after first fetch.
    $content = $this->drupalGet($url);
    $this->assertSame('[]', (string) $content);
  }

}
