<?php

namespace Drupal\commerce_gtm_enhanced_ecommerce\Event;

use Drupal\commerce_gtm_enhanced_ecommerce\Product;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event to alter the Enhanced Ecommerce product mapped from a
 * commerce product variation.
 *
 * @package Drupal\commerce_gtm_enhanced_ecommerce\Event
 */
class AlterProductEvent extends Event {

  /**
   * @var \Drupal\commerce_gtm_enhanced_ecommerce\Product
   */
  private $product;

  /**
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  private $productVariation;

  /**
   * @param \Drupal\commerce_gtm_enhanced_ecommerce\Product $product
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $productVariation
   */
  public function __construct(Product $product,
                              ProductVariationInterface $productVariation)
  {
    $this->product = $product;
    $this->productVariation = $productVariation;
  }

  /**
   * @return \Drupal\commerce_gtm_enhanced_ecommerce\Product
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
