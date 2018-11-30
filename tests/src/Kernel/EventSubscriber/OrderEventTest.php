<?php

namespace Drupal\Tests\commerce_google_tag_manager\Kernel\EventSubscriber;

use Drupal\Tests\commerce_google_tag_manager\Kernel\CommerceKernelTestBase;
use Drupal\commerce_google_tag_manager\EventSubscriber\CommerceEventsSubscriber;
use Drupal\commerce_price\Price;
use Drupal\profile\Entity\Profile;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\state_machine\Event\WorkflowTransitionEvent;

/**
 * @coversDefaultClass \Drupal\commerce_google_tag_manager\EventSubscriber\CommerceEventsSubscriber
 *
 * @group commerce
 * @group commerce_google_tag_manager
 * @group commerce_google_tag_manager_kernel
 */
class OrderEventTest extends CommerceKernelTestBase {

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

    // Remove original CommerceEventsSubscriber which should be Mocked later.
    $this->eventDispatcher = $this->container->get('event_dispatcher');
    $this->eventDispatcher->removeSubscriber($this->container->get('commerce_google_tag_manager.commerce_events_subscriber'));
  }

  /**
   * @covers ::trackPurchase
   */
  public function testTrackPurchase() {
    // Create a partial-mocked version of CommerceEventsSubscriber.
    $subscriber = $this->getMockBuilder(CommerceEventsSubscriber::class)
      ->disableOriginalConstructor()
      ->setMethods(['trackPurchase'])
      ->getMock();

    // Assert the trackPurchase will be called only once.
    $subscriber->expects($this->once())
      ->method('trackPurchase')
      ->with($this->isInstanceOf(WorkflowTransitionEvent::class));

    // Add the new mocked CommerceEventsSubscriber.
    $this->eventDispatcher->addSubscriber($subscriber);

    // Place the order which should fire a commerce_order.place.post_transition.
    $transition = $this->order->getState()->getTransitions();
    $this->order->getState()->applyTransition($transition['place']);
    $this->order->save();
  }

}
