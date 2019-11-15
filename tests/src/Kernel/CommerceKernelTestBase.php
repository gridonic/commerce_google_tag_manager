<?php

namespace Drupal\Tests\commerce_google_tag_manager\Kernel;

use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase as DrupalCommerceKernelTestBase;

/**
 * Provides a base class for Commerce Google Tag Manager kernel tests.
 */
abstract class CommerceKernelTestBase extends DrupalCommerceKernelTestBase {

  /**
   * Modules to additionnaly enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_product',
    'commerce_order',
    'state_machine',
    'commerce_checkout',
    'commerce_google_tag_manager',
    'commerce_number_pattern',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('commerce_product_variation');
    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('commerce_order_item');
    $this->installConfig(['commerce_product', 'commerce_order']);
    $this->installSchema('commerce_number_pattern', ['commerce_number_pattern_sequence']);
  }

}
