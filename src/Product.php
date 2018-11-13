<?php

namespace Drupal\commerce_google_tag_manager;

/**
 * Represents a product in the domain of Google's Enhanced Ecommerce.
 */
class Product {

  /**
   * @var string
   */
  private $name;

  /**
   * @var string
   */
  private $id;

  /**
   * @var string
   */
  private $price;

  /**
   * @var string
   */
  private $brand;

  /**
   * @var string
   */
  private $category;

  /**
   * @var string
   */
  private $variant;

  /**
   * @var array
   */
  private $dimensions = [];

  /**
   * @var array
   */
  private $metrics = [];

  /**
   * Build the product data as array in the requested format by Google.
   *
   * @return array
   */
  public function toArray() {
    $data = [];

    foreach ($this as $property => $value) {
      if (is_array($value)) {
        foreach ($value as $i => $v) {
          $data[rtrim($property, 's') . ($i + 1)] = $v;
        }
      } else if ($value !== NULL) {
        $data[$property] = $value;
      }
    }

    return $data;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @param string $name
   *
   * @return Product
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  /**
   * @return string
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @param string $id
   *
   * @return Product
   */
  public function setId($id) {
    $this->id = $id;
    return $this;
  }

  /**
   * @return string
   */
  public function getPrice() {
    return $this->price;
  }

  /**
   * @param string $price
   *
   * @return Product
   */
  public function setPrice($price) {
    $this->price = $price;
    return $this;
  }

  /**
   * @return string
   */
  public function getBrand() {
    return $this->brand;
  }

  /**
   * @param string $brand
   *
   * @return Product
   */
  public function setBrand($brand) {
    $this->brand = $brand;
    return $this;
  }

  /**
   * @return string
   */
  public function getCategory() {
    return $this->category;
  }

  /**
   * @param string $category
   *
   * @return Product
   */
  public function setCategory($category) {
    $this->category = $category;
    return $this;
  }

  /**
   * @return string
   */
  public function getVariant() {
    return $this->variant;
  }

  /**
   * @param string $variant
   *
   * @return Product
   */
  public function setVariant($variant) {
    $this->variant = $variant;
    return $this;
  }

  /**
   * @return array
   */
  public function getDimensions() {
    return $this->dimensions;
  }

  /**
   * @param array $dimensions
   *
   * @return Product
   */
  public function setDimensions(array $dimensions) {
    $this->dimensions = $dimensions;
    return $this;
  }

  /**
   * @return array
   */
  public function getMetrics() {
    return $this->metrics;
  }

  /**
   * @param array $metrics
   *
   * @return Product
   */
  public function setMetrics(array $metrics) {
    $this->metrics = $metrics;
    return $this;
  }

  /**
   * Add a custom dimension;
   *
   * @param string $dimension
   *
   * @return Product
   */
  public function addDimension($dimension) {
    $this->dimensions[] = $dimension;
    return $this;
  }

  /**
   * Add a custom metric.
   *
   * @param string $metric
   *
   * @return Product
   */
  public function addMetric($metric) {
    $this->metrics[] = $metric;
    return $this;
  }

}
