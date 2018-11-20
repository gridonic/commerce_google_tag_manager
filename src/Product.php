<?php

namespace Drupal\commerce_google_tag_manager;

/**
 * Represents a product in the domain of Google's Enhanced Ecommerce.
 */
class Product {

  /**
   * The product name.
   *
   * @var string
   */
  private $name;

  /**
   * Unique identifier.
   *
   * @var string
   */
  private $id;

  /**
   * The price.
   *
   * @var string
   */
  private $price;

  /**
   * The brand.
   *
   * @var string
   */
  private $brand;

  /**
   * The category.
   *
   * @var string
   */
  private $category;

  /**
   * The product variation.
   *
   * @var string
   */
  private $variant;

  /**
   * Collection of dimensions for GA.
   *
   * @var array
   */
  private $dimensions = [];

  /**
   * Collection of metrics for GA.
   *
   * @var array
   */
  private $metrics = [];

  /**
   * Build the product data as array in the requested format by Google.
   *
   * @return array
   *   Formated Product data as requested by Google.
   */
  public function toArray() {
    $data = [];

    foreach ($this as $property => $value) {
      if (is_array($value)) {
        foreach ($value as $i => $v) {
          $data[rtrim($property, 's') . ($i + 1)] = $v;
        }
      }
      elseif ($value !== NULL) {
        $data[$property] = $value;
      }
    }

    return $data;
  }

  /**
   * Get the product name.
   *
   * @return string
   *   The name.
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Set the product name.
   *
   * @param string $name
   *   The name.
   *
   * @return \Drupal\commerce_google_tag_manager\Product
   *   The Product object.
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  /**
   * Get the unique identifier.
   *
   * @return string
   *   The unique identifier.
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Set the unique identifier.
   *
   * @param string $id
   *   The identifier.
   *
   * @return \Drupal\commerce_google_tag_manager\Product
   *   The Product object.
   */
  public function setId($id) {
    $this->id = $id;
    return $this;
  }

  /**
   * Get the price.
   *
   * @return string
   *   The price.
   */
  public function getPrice() {
    return $this->price;
  }

  /**
   * Set the price.
   *
   * @param string $price
   *   The price.
   *
   * @return \Drupal\commerce_google_tag_manager\Product
   *   The Product object.
   */
  public function setPrice($price) {
    $this->price = $price;
    return $this;
  }

  /**
   * Get the brand.
   *
   * @return string
   *   The brand.
   */
  public function getBrand() {
    return $this->brand;
  }

  /**
   * Set the brand.
   *
   * @param string $brand
   *   The brand.
   *
   * @return \Drupal\commerce_google_tag_manager\Product
   *   The Product object.
   */
  public function setBrand($brand) {
    $this->brand = $brand;
    return $this;
  }

  /**
   * Get the category.
   *
   * @return string
   *   The category.
   */
  public function getCategory() {
    return $this->category;
  }

  /**
   * Set the category.
   *
   * @param string $category
   *   The category.
   *
   * @return \Drupal\commerce_google_tag_manager\Product
   *   The Product object.
   */
  public function setCategory($category) {
    $this->category = $category;
    return $this;
  }

  /**
   * Get the variation.
   *
   * @return string
   *   The variation.
   */
  public function getVariant() {
    return $this->variant;
  }

  /**
   * Set the variation.
   *
   * @param string $variant
   *   The variation.
   *
   * @return \Drupal\commerce_google_tag_manager\Product
   *   The Product object.
   */
  public function setVariant($variant) {
    $this->variant = $variant;
    return $this;
  }

  /**
   * Get the collection of dimensions.
   *
   * @return string[]
   *   Collection of dimensions.
   */
  public function getDimensions() {
    return $this->dimensions;
  }

  /**
   * Set dimensions.
   *
   * @param array $dimensions
   *   Collection of dimensions.
   *
   * @return \Drupal\commerce_google_tag_manager\Product
   *   The Product object.
   */
  public function setDimensions(array $dimensions) {
    $this->dimensions = $dimensions;
    return $this;
  }

  /**
   * Get the collection of metrics.
   *
   * @return string[]
   *   Collection of metrics.
   */
  public function getMetrics() {
    return $this->metrics;
  }

  /**
   * Set metrics.
   *
   * @param array $metrics
   *   Collection of metrics.
   *
   * @return \Drupal\commerce_google_tag_manager\Product
   *   The Product object.
   */
  public function setMetrics(array $metrics) {
    $this->metrics = $metrics;
    return $this;
  }

  /**
   * Add a custom dimension.
   *
   * @param string $dimension
   *   The dimension to add.
   *
   * @return \Drupal\commerce_google_tag_manager\Product
   *   The Product object.
   */
  public function addDimension($dimension) {
    $this->dimensions[] = $dimension;
    return $this;
  }

  /**
   * Add a custom metric.
   *
   * @param string $metric
   *   The metric to add.
   *
   * @return \Drupal\commerce_google_tag_manager\Product
   *   The Product object.
   */
  public function addMetric($metric) {
    $this->metrics[] = $metric;
    return $this;
  }

}
