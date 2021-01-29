<?php

namespace Drupal\custom_commerce_order\OrderProcessor;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderProcessorInterface;

/**
 * Applies a 5% discount per high quanity item because it is Thursday.
 */
class BonusOrderProcessor implements OrderProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function process(OrderInterface $order) {
    if (date('w') == 5) {
      foreach ($order->getItems() as $order_item) {
        if ($order_item->getQuantity() > 4) {
          $adjustment_amount = $order_item->getTotalPrice()->multiply('0.05');
          $order_item->addAdjustment(new Adjustment([
            'type' => 'custom',
            'label' => t('Thursday bonus'),
            'amount' => $adjustment_amount->multiply('-1'),
            'percentage' => '0.05',
          ]));
        }
      }
    }
  }

}
