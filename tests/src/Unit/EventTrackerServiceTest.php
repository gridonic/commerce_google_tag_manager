<?php

namespace Drupal\Tests\commerce_google_tag_manager\Unit;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\commerce_store\Entity\StoreInterface;
use Drupal\commerce_order\PriceCalculatorResult;
use Drupal\Tests\UnitTestCase;
use Drupal\commerce_google_tag_manager\EventTrackerService;
use Drupal\commerce_google_tag_manager\EventStorageService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\commerce_order\PriceCalculatorInterface;
use Prophecy\Argument;

/**
 * Tests for the EventTracker service.
 *
 * @coversDefaultClass \Drupal\commerce_google_tag_manager\EventTrackerService
 *
 * @group commerce
 * @group commerce_google_tag_manager
 * @group commerce_google_tag_manager_unit
 */
class EventTrackerServiceTest extends UnitTestCase {

  /**
   * The Commerce GTM event tracker.
   *
   * @var \Drupal\commerce_google_tag_manager\EventTrackerService
   */
  private $eventTracker;

  /**
   * The commerce GTM event storage.
   *
   * @var \Drupal\commerce_google_tag_manager\EventStorageService
   */
  private $eventStorage;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->eventStorage = $this->getMockBuilder(EventStorageService::class)
      ->disableOriginalConstructor()
      ->getMock();

    $event_dispatcher = $this->prophesize(EventDispatcherInterface::class);
    $current_store = $this->prophesize(CurrentStoreInterface::class);
    $current_user = $this->prophesize(AccountInterface::class);
    $price_calculator = $this->prophesize(PriceCalculatorInterface::class);

    $store = $this->prophesize(StoreInterface::class);
    $current_store->getStore()->willReturn($store->reveal());

    $price = new Price('50.00', 'USD');
    $price_calc_result = new PriceCalculatorResult($price, $price);
    $price_calculator->calculate(Argument::any(), Argument::any(), Argument::any())->willReturn($price_calc_result);

    $this->eventTracker = new EventTrackerService($this->eventStorage, $event_dispatcher->reveal(), $current_store->reveal(), $current_user->reveal(), $price_calculator->reveal());
  }

  /**
   * @covers ::productImpressions
   *
   * @dataProvider productVariationProvider
   */
  public function testProductImpressions($product_variation, $product_data) {
    $this->eventStorage
      ->expects($this->once())
      ->method('addEvent')
      ->with([
        'event' => EventTrackerService::EVENT_PRODUCT_IMPRESSIONS,
        'ecommerce' => [
          'impressions' => [
            array_merge($product_data, ['list' => 'List Name']),
          ],
        ],
      ]);

    $this->eventTracker->productImpressions([$product_variation], 'List Name');
  }

  /**
   * @covers ::productDetailViews
   *
   * @dataProvider productVariationProvider
   */
  public function testProductDetailViews($product_variation, $product_data) {
    $this->eventStorage
      ->expects($this->once())
      ->method('addEvent')
      ->with([
        'event' => EventTrackerService::EVENT_PRODUCT_DETAIL_VIEWS,
        'ecommerce' => [
          'detail' => [
            'actionField' => ['list' => 'List Name'],
            'products' => [$product_data],
          ],
        ],
      ]);

    $this->eventTracker->productDetailViews([$product_variation], 'List Name');
  }

  /**
   * @covers ::productDetailViews
   *
   * @dataProvider productVariationProvider
   */
  public function testProductClick($product_variation, $product_data) {
    $this->eventStorage
      ->expects($this->once())
      ->method('addEvent')
      ->with([
        'event' => EventTrackerService::EVENT_PRODUCT_CLICK,
        'ecommerce' => [
          'click' => [
            'actionField' => ['list' => 'List Name'],
            'products' => [$product_data],
          ],
        ],
      ]);

    $this->eventTracker->productClick([$product_variation], 'List Name');
  }

  /**
   * @covers ::addToCart
   *
   * @dataProvider productVariationProvider
   */
  public function testAddToCart($product_variation, $product_data) {
    $order_item = $this->prophesize(OrderItemInterface::class);
    $order_item->getPurchasedEntity()->willReturn($product_variation);
    $order_item->getTotalPrice()->willReturn(new Price('50', 'CHF'));

    $this->eventStorage
      ->expects($this->once())
      ->method('addEvent')
      ->with([
        'event' => EventTrackerService::EVENT_ADD_CART,
        'ecommerce' => [
          'currencyCode' => 'CHF',
          'add' => [
            'products' => [
              array_merge($product_data, ['quantity' => 1]),
            ],
          ],
        ],
      ]);

    $this->eventTracker->addToCart($order_item->reveal(), 1);
  }

  /**
   * @covers ::addToCart
   */
  public function testAddToCartNoPurchasedEntity() {
    // Assert the event is trigger if the purchased entity is missing.
    $order_item = $this->prophesize(OrderItemInterface::class);
    $order_item->getTitle()->shouldBeCalledTimes(1)
      ->willReturn('Order item title');
    $order_item->getPurchasedEntity()->shouldBeCalledTimes(1)
      ->willReturn(NULL);
    $order_item->getPurchasedEntityId()->shouldBeCalledTimes(1)
      ->willReturn(NULL);
    $order_item->getTotalPrice()->shouldBeCalledTimes(2)
      ->willReturn(new Price('10', 'CHF'));

    $this->eventStorage
      ->expects($this->once())
      ->method('addEvent')
      ->with([
        'event' => EventTrackerService::EVENT_ADD_CART,
        'ecommerce' => [
          'currencyCode' => 'CHF',
          'add' => [
            'products' => [
              [
                'name' => 'Order item title',
                'price' => '10.00',
                'quantity' => 1,
              ],
            ],
          ],
        ],
      ]);

    $this->eventTracker->addToCart($order_item->reveal(), 1);
  }

  /**
   * @covers ::removeFromCart
   *
   * @dataProvider productVariationProvider
   */
  public function testRemoveFromCart($product_variation, $product_data) {
    $order_item = $this->prophesize(OrderItemInterface::class);
    $order_item->getPurchasedEntity()->willReturn($product_variation);
    $order_item->getTotalPrice()->willReturn(new Price('50', 'CHF'));

    $this->eventStorage
      ->expects($this->once())
      ->method('addEvent')
      ->with([
        'event' => EventTrackerService::EVENT_REMOVE_CART,
        'ecommerce' => [
          'remove' => [
            'products' => [
              array_merge($product_data, ['quantity' => 1]),
            ],
          ],
        ],
      ]);

    $this->eventTracker->removeFromCart($order_item->reveal(), 1);
  }

  /**
   * @covers ::checkoutStep
   *
   * @dataProvider productVariationProvider
   */
  public function testCheckoutStep($product_variation, $product_data) {
    $order_item = $this->prohesizeOrderItem($product_variation, new Price('50', 'CHF'), '6.00');
    $order = $this->prohesizeOrder([$order_item->reveal()], 123, 'My Shop', new Price('50', 'CHF'));

    $this->eventStorage
      ->expects($this->once())
      ->method('addEvent')
      ->with([
        'event' => EventTrackerService::EVENT_CHECKOUT,
        'ecommerce' => [
          'checkout' => [
            'actionField' => [
              'step' => 2,
            ],
            'products' => [
              array_merge($product_data, ['quantity' => 6]),
            ],
          ],
        ],
      ]);

    $this->eventTracker->checkoutStep(2, $order->reveal());
  }

  /**
   * @covers ::checkoutOption
   *
   * @dataProvider checkoutOptionDataProvider
   */
  public function testCheckoutOption($step_index, $checkout_option) {
    $this->eventStorage
      ->expects($this->once())
      ->method('addEvent')
      ->with([
        'event' => EventTrackerService::EVENT_CHECKOUT_OPTION,
        'ecommerce' => [
          'checkout_option' => [
            'actionField' => [
              'step' => $step_index,
              'option' => $checkout_option,
            ],
          ],
        ],
      ]);

    $this->eventTracker->checkoutOption($step_index, $checkout_option);
  }

  /**
   * @covers ::purchase
   *
   * @dataProvider productVariationProvider
   */
  public function testPurchase($product_variation, $product_data) {
    $order_item = $this->prohesizeOrderItem($product_variation, new Price('50', 'CHF'), '4.00');
    $order = $this->prohesizeOrder([$order_item], 321, 'My Shop', new Price('50', 'CHF'));

    $this->eventStorage
      ->expects($this->once())
      ->method('addEvent')
      ->with([
        'event' => EventTrackerService::EVENT_PURCHASE,
        'ecommerce' => [
          'purchase' => [
            'actionField' => [
              'id' => 321,
              'affiliation' => 'My Shop',
              'revenue' => '50.00',
              'shipping' => '0',
              'tax' => '0',
              'coupon' => '',
            ],
            'products' => [
              array_merge($product_data, ['quantity' => 4]),
            ],
          ],
        ],
      ]);

    $this->eventTracker->purchase($order->reveal());
  }

  /**
   * Get checkout option data as tupel (step,option).
   *
   * @return array
   *   The checkout options data.
   */
  public function checkoutOptionDataProvider() {
    return [
      [
        1,
        'Some option',
      ],
      [
        3,
        'Visa',
      ],
    ];
  }

  /**
   * Get a prophesized product variation with the GTM product data.
   *
   * @return array
   *   The product variation and corresponding GTM product data.
   */
  public function productVariationProvider() {
    $product = $this->prophesize(ProductInterface::class);
    $product->id()->willReturn(123);
    $product->getTitle()->willReturn('Product Title');

    $product_variation = $this->prophesize(ProductVariationInterface::class);
    $product_variation->getTitle()->willReturn('Product Variation Title');
    $product_variation->getPrice()->willReturn(new Price('50', 'CHF'));
    $product_variation->getProduct()->willReturn($product->reveal());

    return [
      [
        $product_variation->reveal(),
        [
          'name' => 'Product Title',
          'id' => '123',
          'price' => '50.00',
          'variant' => 'Product Variation Title',
        ],
      ],
    ];
  }

  /**
   * Prophesize a commerce order.
   *
   * @param array $order_items
   *   The prohesized order items.
   * @param int $order_number
   *   The order number.
   * @param string $store_name
   *   The name of the store.
   * @param \Drupal\commerce_price\Price $total_price
   *   The total price of the order.
   *
   * @return \Prophecy\Prophecy\ObjectProphecy
   *   The prohesized order entity.
   */
  private function prohesizeOrder(array $order_items, $order_number, $store_name, Price $total_price) {
    $store = $this->prophesize(StoreInterface::class);
    $store->getName()->willReturn($store_name);

    $order = $this->prophesize(OrderInterface::class);
    $order->getItems()->willReturn($order_items);
    $order->getOrderNumber()->willReturn($order_number);
    $order->getStore()->willReturn($store->reveal());
    $order->hasField('shipments')->willReturn(FALSE);
    $order->hasField('coupons')->willReturn(FALSE);
    $order->getTotalPrice()->willReturn($total_price);
    $order->collectAdjustments()->willReturn([]);

    return $order;
  }

  /**
   * Prophesize a commerce order item.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $product_variation
   *   The corresponding prophesized product variation.
   * @param \Drupal\commerce_price\Price $total_price
   *   The total price of the order item.
   * @param string $quantity
   *   The quantity of the order item.
   *
   * @return \Prophecy\Prophecy\ObjectProphecy
   *   The prophesized order item.
   */
  private function prohesizeOrderItem(ProductVariationInterface $product_variation, Price $total_price, $quantity) {
    $order_item = $this->prophesize(OrderItemInterface::class);
    $order_item->getPurchasedEntity()->willReturn($product_variation);
    $order_item->getTotalPrice()->willReturn($total_price);
    $order_item->getQuantity()->willReturn($quantity);

    return $order_item;
  }

}
