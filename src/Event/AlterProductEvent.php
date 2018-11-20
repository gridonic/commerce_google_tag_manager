<?php

namespace Drupal\commerce_google_tag_manager\Event;

use Drupal\commerce_google_tag_manager\Product;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event to alter the Enhanced Ecommerce product.
 *
 * Event to alter product mapped from a commerce product variation.
 */
class AlterProductEvent extends Event {

  /**
   * The Commerce GTM product class.
   *
   * @var \Drupal\commerce_google_tag_manager\Product
   */
  private $product;

  /**
   * The Commerce production variation.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  private $productVariation;

  /**
   * Constructs a AlterProductEvent object.
   *
   * @param \Drupal\commerce_google_tag_manager\Product $product
   *   The Commerce GTM product class.
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $product_variation
   *   The Commerce production variation.
   */
  public function __construct(Product $product,
                              ProductVariationInterface $product_variation) {
    $this->product = $product;
    $this->productVariation = $product_variation;
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
   * Get the product variation.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface
   *   The product variation object.
   */
  public function getProductVariation() {
    return $this->productVariation;
  }

}
