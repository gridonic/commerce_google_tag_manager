<?php

namespace Drupal\Tests\commerce_google_tag_manager\Kernel\EventSubscriber;

use Drupal\Tests\commerce_google_tag_manager\Kernel\CommerceKernelTestBase;
use Drupal\commerce_google_tag_manager\EventSubscriber\CommerceEventsSubscriber;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartOrderItemRemoveEvent;
use Drupal\Tests\commerce_cart\Traits\CartManagerTestTrait;

/**
 * @coversDefaultClass \Drupal\commerce_google_tag_manager\EventSubscriber\CommerceEventsSubscriber
 *
 * @group commerce
 * @group commerce_google_tag_manager
 * @group commerce_google_tag_manager_kernel
 */
class CartEventTest extends CommerceKernelTestBase {
  use CartManagerTestTrait;

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
   * The cart manager.
   *
   * @var \Drupal\commerce_cart\CartManager
   */
  protected $cartManager;

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProvider
   */
  protected $cartProvider;

  /**
   * A product variation to test with.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariation
   */
  protected $variation;

  /**
   * A sample user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private $eventDispatcher;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->variation = ProductVariation::create([
      'type'   => 'default',
      'sku'    => strtolower($this->randomMachineName()),
      'title'  => $this->randomString(),
      'price'  => new Price('1.00', 'USD'),
      'status' => TRUE,
    ]);

    $user = $this->createUser();
    $this->user = $this->reloadEntity($user);

    $this->installCommerceCart();

    // Remove original CommerceEventsSubscriber which should be Mocked later.
    $this->eventDispatcher = $this->container->get('event_dispatcher');
    $this->eventDispatcher->removeSubscriber($this->container->get('commerce_google_tag_manager.commerce_events_subscriber'));
  }

  /**
   * @covers ::trackCartAdd
   */
  public function testTrackCartAdd() {
    // Create a partial-mocked version of CommerceEventsSubscriber.
    $subscriber = $this->getMockBuilder(CommerceEventsSubscriber::class)
      ->disableOriginalConstructor()
      ->setMethods(['trackCartAdd'])
      ->getMock();

    // Assert the trackCartAdd will be called only once.
    $subscriber->expects($this->once())
      ->method('trackCartAdd')
      ->with($this->isInstanceOf(CartEntityAddEvent::class));

    // Add the new mocked CommerceEventsSubscriber.
    $this->eventDispatcher->addSubscriber($subscriber);

    // Add to cart, which should fire a CartEvents::CART_ENTITY_ADD.
    $cart = $this->cartProvider->createCart('default', $this->store, $this->user);
    $this->cartManager->addEntity($cart, $this->variation);
  }

  /**
   * @covers ::trackCartRemove
   */
  public function testTrackCartRemove() {
    // Create a partial-mocked version of CommerceEventsSubscriber.
    $subscriber = $this->getMockBuilder(CommerceEventsSubscriber::class)
      ->disableOriginalConstructor()
      ->setMethods(['trackCartAdd', 'trackCartRemove'])
      ->getMock();

    // Assert the trackCartRemove will be called only once.
    $subscriber->expects($this->once())
      ->method('trackCartRemove')
      ->with($this->isInstanceOf(CartOrderItemRemoveEvent::class));

    // Add the new mocked CommerceEventsSubscriber.
    $this->eventDispatcher->addSubscriber($subscriber);

    // Remove to cart, which should fire a CartEvents::CART_ORDER_ITEM_REMOVE.
    $cart = $this->cartProvider->createCart('default', $this->store, $this->user);
    $order_item = $this->cartManager->addEntity($cart, $this->variation);
    $this->cartManager->removeOrderItem($cart, $order_item);
  }

}
