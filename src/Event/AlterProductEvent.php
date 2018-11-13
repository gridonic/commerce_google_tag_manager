<?php

namespace Drupal\commerce_google_tag_manager\Event;

use Drupal\commerce_google_tag_manager\Product;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event to alter the Enhanced Ecommerce product mapped from a
 * commerce product variation.
 */
class AlterProductEvent extends Event {

  /**
   * @var \Drupal\commerce_google_tag_manager\Product
   */
  private $product;

  /**
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  private $productVariation;

  /**
   * @param \Drupal\commerce_google_tag_manager\Product $product
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $productVariation
   */
  public function __construct(Product $product,
                              ProductVariationInterface $productVariation)
  {
    $this->product = $product;
    $this->productVariation = $productVariation;
  }

  /**
   * @return \Drupal\commerce_google_tag_manager\Product
   */
  public function getProduct() {
    return $this->product;
  }

  /**
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  public function getProductVariation() {
    return $this->productVariation;
  }
}
