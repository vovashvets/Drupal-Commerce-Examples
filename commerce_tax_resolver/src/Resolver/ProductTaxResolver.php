<?php

namespace Drupal\commerce_tax_resolver\Resolver;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_tax\Resolver\TaxRateResolverInterface;
use Drupal\commerce_tax\TaxZone;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Returns the tax zone's default tax rate.
 */
class ProductTaxResolver implements TaxRateResolverInterface {

  /**
   * {@inheritdoc}
   */
  public function resolve(TaxZone $zone, OrderItemInterface $order_item, ProfileInterface $customer_profile) {
    $rates = $zone->getRates();

    // Get the purchased entity.
    $item = $order_item->getPurchasedEntity();

    // Get the corresponding product.
    $product = $item->getProduct();

    $product_type = $product->bundle();

    // Take a rate depending on the product type.
    switch ($product_type) {
      case 'default':
        $rate_id = 'reduced';
        break;
      default:
        // The rate for other product type can be resolved using the default tax
        // rate resolver.
        return NULL;
    }

    foreach ($rates as $rate) {
      if ($rate->getId() == $rate_id) {
        return $rate;
      }
    }

    // If no rate has been found, let's others resolvers try to get it.
    return NULL;
  }

}
