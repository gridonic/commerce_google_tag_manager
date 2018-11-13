<?php

namespace Drupal\commerce_google_tag_manager;

use Drupal\commerce_google_tag_manager\Event\AlterProductEvent;
use Drupal\commerce_google_tag_manager\Event\EnhancedEcommerceEvents;
use Drupal\commerce_google_tag_manager\Event\TrackCheckoutStepEvent;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Track different events from Google's Enhanced Ecommerce.
 *
 * @see https://developers.google.com/tag-manager/enhanced-ecommerce
 */
class EventTrackerService {

  const EVENT_PRODUCT_IMPRESSIONS = 'productImpressions';
  const EVENT_PRODUCT_DETAIL_VIEWS = 'productDetailViews';
  const EVENT_PRODUCT_CLICK = 'productClick';
  const EVENT_ADD_CART = 'addToCart';
  const EVENT_REMOVE_CART = 'removeFromCart';
  const EVENT_CHECKOUT = 'checkout';
  const EVENT_CHECKOUT_OPTION = 'checkoutOption';
  const EVENT_PURCHASE = 'purchase';

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private $eventDispatcher;

  /**
   * @var \Drupal\commerce_google_tag_manager\EventStorageService
   */
  private $eventStorageService;

  /**
   * @param \Drupal\commerce_google_tag_manager\EventStorageService $eventStorageService
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   */
  public function __construct(EventStorageService $eventStorageService,
                              EventDispatcherInterface $eventDispatcher
                              ) {
    $this->eventDispatcher = $eventDispatcher;
    $this->eventStorageService = $eventStorageService;
  }

  /**
   * Track product impressions.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface[] $productVariations
   *   The commerce product variation entities being viewed.
   * @param string $list
   *   The name of the list showing the products.
   */
  public function productImpressions(array $productVariations, $list = '') {
    $productsData = array_map(function ($productVariation) use ($list) {
      return array_merge(
        $this->buildProductFromProductVariation($productVariation)->toArray(),
        ['list' => $list]);
    }, $productVariations);

    $data = [
      'event' => self::EVENT_PRODUCT_IMPRESSIONS,
      'ecommerce' => [
        'impressions' => $productsData,
      ],
    ];

    $this->eventStorageService->addEvent($data);
  }

  /**
   * Track product detail views.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariation[] $productVariations
   *   The commerce product variations being viewed.
   * @param string $list
   *   An optional name of a list.
   */
  public function productDetailViews(array $productVariations, $list = '') {
    $data = [
      'event' => self::EVENT_PRODUCT_DETAIL_VIEWS,
      'ecommerce' => [
        'detail' => [
          'actionField' => ['list' => $list],
          'products' => $this->buildProductsFromProductVariations($productVariations),
        ],
      ],
    ];

    $this->eventStorageService->addEvent($data);
  }

  /**
   * Track a "product click" event.
   *
   * @param array $productVariations
   *   A commerce product variation that was clicked.
   * @param string $list
   *   An optional name of a list.
   */
  public function productClick(array $productVariations, $list = '') {
    $data = [
      'event' => self::EVENT_PRODUCT_CLICK,
      'ecommerce' => [
        'click' => [
          'actionField' => ['list' => $list],
          'products' => $this->buildProductsFromProductVariations($productVariations),
        ],
      ],
    ];

    $this->eventStorageService->addEvent($data);
  }

  /**
   * Track the "addToCart" event.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $orderItem
   *   The oder item added to the cart.
   * @param int $quantity
   *   Quantity added to cart.
   */
  public function addToCart(OrderItemInterface $orderItem, $quantity) {
    $product = $this->buildProductFromOrderItem($orderItem);

    $data = [
      'event' => self::EVENT_ADD_CART,
      'ecommerce' => [
        'currencyCode' => $orderItem->getTotalPrice()->getCurrencyCode(),
        'add' => [
          'products' => [
            array_merge($product->toArray(), ['quantity' => $quantity])
          ],
        ],
      ],
    ];

    $this->eventStorageService->addEvent($data);
  }

  /**
   * Track the "removeFromCart" event.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $orderItem
   *   The commerce order item removed from the cart.
   * @param int $quantity
   *   The removed quantity.
   */
  public function removeFromCart(OrderItemInterface $orderItem, $quantity) {
    $data = [
      'event' => self::EVENT_REMOVE_CART,
      'ecommerce' => [
        'remove' => [
          'products' => $this->buildProductsFromOrderItems([$orderItem]),
        ],
      ],
    ];

    $this->eventStorageService->addEvent($data);
  }

  /**
   * Track a checkout step.
   *
   * @param int $stepIndex
   *   The index of the checkout step (1-based).
   * @param OrderInterface $order
   *   The commerce order representing the cart.
   */
  public function checkoutStep($stepIndex, OrderInterface $order) {
    $data = [
      'event' => self::EVENT_CHECKOUT,
      'ecommerce' => [
        'checkout' => [
          'actionField' => [
            'step' => $stepIndex,
          ],
          'products' => $this->buildProductsFromOrderItems($order->getItems()),
        ],
      ],
    ];

    $this->eventStorageService->addEvent($data);

    // Throw an event to add possible checkout step options by event listeners.
    $event = new TrackCheckoutStepEvent($stepIndex, $order);
    $this->eventDispatcher->dispatch(EnhancedEcommerceEvents::TRACK_CHECKOUT_STEP, $event);
  }

  /**
   * Track a checkout option.
   *
   * This allows to track additional metadata for any checkout step.
   *
   * @param $stepIndex
   *   The index of the checkout step (1-based).
   * @param $checkoutOption
   *   The option to track with the given step.
   */
  public function checkoutOption($stepIndex, $checkoutOption) {
    $data = [
      'event' => self::EVENT_CHECKOUT_OPTION,
      'ecommerce' => [
        'checkout_option' => [
          'actionField' => [
            'step' => $stepIndex,
            'option' => $checkoutOption,
          ],
        ],
      ],
    ];

    $this->eventStorageService->addEvent($data);
  }

  /**
   * Track a purchase of the given order entity.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   A commerce order entity.
   */
  public function purchase(OrderInterface $order) {
    $data = [
      'event' => self::EVENT_PURCHASE,
      'ecommerce' => [
        'purchase' => [
          'actionField' => [
            'id' => $order->getOrderNumber(),
            'affiliation' => $order->getStore()->getName(),
            'revenue' => $this->formatPrice((float) $order->getTotalPrice()->getNumber()),
            'shipping' => $this->formatPrice($this->calculateShipping($order)),
            'coupon' => $this->getCouponCode($order),
          ],
          'products' => $this->buildProductsFromOrderItems($order->getItems()),
        ],
      ],
    ];

    // TODO: Add tax
    $this->eventStorageService->addEvent($data);
  }

  /**
   * Build the Enhanced Ecommerce product from a given commerce order item.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $orderItem
   *   A commerce order item.
   *
   * @return \Drupal\commerce_google_tag_manager\Product
   *   The Enhanced Ecommerce product.
   */
  private function buildProductFromOrderItem(OrderItemInterface $orderItem) {
    $purchasedEntity = $orderItem->getPurchasedEntity();

    if ($purchasedEntity instanceof ProductVariationInterface) {
      $product = $this->buildProductFromProductVariation($purchasedEntity);
    } else {
      // The purchased entity is not a product variation.
      $product = (new Product())
        ->setName($orderItem->getTitle())
        ->setId($purchasedEntity->id())
        ->setPrice($this->formatPrice((float) $orderItem->getTotalPrice()->getNumber()));
    }

    return $product;
  }

  /**
   * Build the Enhanced Ecommerce product from a given commerce product variation.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $productVariation
   *   A commerce product variation.
   * @return \Drupal\commerce_google_tag_manager\Product
   *   The Enhanced Ecommerce product.
   */
  private function buildProductFromProductVariation(ProductVariationInterface $productVariation) {
    $product = new Product();
    $product
      ->setName($productVariation->getProduct()->getTitle())
      ->setId($productVariation->getProduct()->id())
      ->setVariant($productVariation->getTitle())
      ->setPrice($this->formatPrice((float) $productVariation->getPrice()->getNumber()));

    $event = new AlterProductEvent($product, $productVariation);
    $this->eventDispatcher->dispatch(EnhancedEcommerceEvents::ALTER_PRODUCT, $event);

    return $product;
  }

  /**
   * Build the Enhanced Ecommerce products from given commerce order items.
   *
   * @param array $orderItems
   *   The commerce order items.
   * @return array
   *   An array of EnhancedEcommerce products.
   */
  private function buildProductsFromOrderItems(array $orderItems) {
    return array_map(function($orderItem) {
      return array_merge(
        $this->buildProductFromOrderItem($orderItem)->toArray(),
        ['quantity' => (int) $orderItem->getQuantity()]
      );
    }, $orderItems);
  }

  /**
   * Build the Enhanced Ecommerce products from given commerce product variations.
   *
   * @param array $productVariations
   *   The commerce product variations.
   * @return array
   *   An array of EnhancedEcommerce products.
   */
  private function buildProductsFromProductVariations(array $productVariations) {
    return array_map(function($productVariation) {
      return $this
        ->buildProductFromProductVariation($productVariation)
        ->toArray();
    }, $productVariations);
  }

  /**
   * @param float $price
   *
   * @return string
   */
  private function formatPrice($price) {
    if ($price == 0) {
      return '0';
    }

    // Truncate decimals without rounding.
    $number = bcdiv((float) $price, 1, 2);

    // Format the number as requested by Google's Enhanced Ecommerce.
    return number_format($number, 2, '.', '');
  }

  /**
   * Calculate the total shipping costs from the given order.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *
   * @return float
   */
  private function calculateShipping(OrderInterface $order) {
    if ($order->hasField('shipments') && !$order->get('shipments')->isEmpty()) {
      $total = 0;
      foreach ($order->get('shipments')->referencedEntities() as $shipment) {
        /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
        $total += (float) $shipment->getAmount()->getNumber();
      }

      return $total;
    }

    return 0;
  }

  /**
   * Get the coupon code(s) used with the given commerce order.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *
   * @return string
   */
  private function getCouponCode(OrderInterface $order) {
    if (!$order->hasField('coupons') || $order->get('coupons')->isEmpty()) {
      return '';
    }

    $couponCodes = array_map(function ($coupon) {
      /** @var \Drupal\commerce_promotion\Entity\CouponInterface $coupon */
      return $coupon->getCode();
    }, $order->get('coupons')->referencedEntities());

    if (count($couponCodes) === 1) {
      return $couponCodes[0];
    }

    return implode(', ', $couponCodes);
  }

}
