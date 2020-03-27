<?php

namespace Drupal\commerce_google_tag_manager\Event;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_google_tag_manager\Product;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event to alter the Enhanced Ecommerce product.
 *
 * Event to alter product mapped from a purchased entity.
 */
class AlterProductPurchasedEntityEvent extends Event {

  /**
   * The Commerce GTM product class.
   *
   * @var \Drupal\commerce_google_tag_manager\Product
   */
  private $product;

  /**
   * The order item.
   *
   * @var \Drupal\commerce_order\Entity\OrderItemInterface
   */
  private $orderItem;

  /**
   * The purchased entity if available.
   *
   * @var \Drupal\commerce\PurchasableEntityInterface|null
   */
  private $purchasedEntity;

  /**
   * Constructs a AlterProductPurchasedEntityEvent object.
   *
   * @param \Drupal\commerce_google_tag_manager\Product $product
   *   The Commerce GTM product class.
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $order_item
   *   The order item.
   * @param \Drupal\commerce\PurchasableEntityInterface|null $purchased_entity
   *   (optional) The purchased entity.
   */
  public function __construct(Product $product, OrderItemInterface $order_item, PurchasableEntityInterface $purchased_entity = NULL) {
    $this->product = $product;
    $this->orderItem = $order_item;
    $this->purchasedEntity = $purchased_entity;
  }

  /**
   * Get the product.
   *
   * @return \Drupal\commerce_google_tag_manager\Product
   *   The Commerce GTM product object.
   */
  public function getProduct() {
    return $this->product;
  }

  /**
   * Get the order item.
   *
   * @return \Drupal\commerce_order\Entity\OrderItemInterface
   *   The order item.
   */
  public function getOrderItem() {
    return $this->orderItem;
  }

  /**
   * Get the purchased entity or null if not available.
   *
   * @return \Drupal\commerce\PurchasableEntityInterface|null
   *   The purchased entity.
   */
  public function getPurchasedEntity() {
    return $this->purchasedEntity;
  }

}
