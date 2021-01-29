<?php

namespace Drupal\commerce_custom_offer_type\Plugin\Commerce\PromotionOffer;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_price\Price;
use Drupal\commerce_promotion\Entity\PromotionInterface;
use Drupal\commerce_promotion\Plugin\Commerce\PromotionOffer\OrderItemPromotionOfferBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Discounts a set quantity of items to fixed amount.
 *
 * @CommercePromotionOffer(
 *   id = "commerce_custom_offer_type_discount_to_fixed",
 *   label = @Translation("Custom Discounts"),
 *   entity_type = "commerce_order_item",
 * )
 */
class OrderItemDiscountToFixed extends OrderItemPromotionOfferBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'quantity' => 1,
        'amount' => NULL,
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form += parent::buildConfigurationForm($form, $form_state);

    $quantity = $this->configuration['quantity'];
    $amount = $this->configuration['amount'];
    // A bug in the plugin_select form element causes $amount to be incomplete.
    if (isset($amount) && !isset($amount['number'], $amount['currency_code'])) {
      $amount = NULL;
    }

    $form['quantity'] = [
      '#type' => 'number',
      '#title' => $this->t('Quantity'),
      '#description' => $this->t('Number of items to discount for each matching product.'),
      '#default_value' => $quantity,
      '#min' => 1,
      // Set max to match length of quantity text box match amount text box.
      '#max' => 10000000,
      '#required' => TRUE,
    ];

    $form['amount'] = [
      '#type' => 'commerce_price',
      '#title' => $this->t('Amount'),
      '#default_value' => $amount,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue($form['#parents']);
    if (empty($values['quantity'])) {
      $form_state->setError($form, $this->t('Quantity must be a positive number.'));
    }
    if ($values['amount']['number'] < 0) {
      $form_state->setError($form, $this->t('Amount cannot be negative.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $values = $form_state->getValue($form['#parents']);
    $this->configuration['quantity'] = $values['quantity'];
    $this->configuration['amount'] = $values['amount'];
  }

  /**
   * {@inheritdoc}
   */
  public function apply(EntityInterface $entity, PromotionInterface $promotion) {
    $this->assertEntity($entity);

    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
    $order_item = $entity;
    $unit_price = $order_item->getUnitPrice();
    $quantity = $order_item->getQuantity();
    $target_amount = $this->getAmount();
    $offer_quantity = $this->configuration['quantity'];

    // Confirm that the currency codes are the same.
    if ($unit_price->getCurrencyCode() != $target_amount->getCurrencyCode()) {
      return;
    }
    // Don't raise the order item unit price.
    if ($target_amount->greaterThan($unit_price)) {
      return;
    }
    // Offer quantity cannot exceed the order item quantity.
    if ($offer_quantity > $quantity) {
      $offer_quantity = $quantity;
    }

    // Calculate per-item reduction amount.
    $adjustment_amount = $unit_price->subtract($target_amount);
    $adjustment_amount = $adjustment_amount->multiply($offer_quantity);
    // Adjustment amount is multiplied by quantity when applied, so we divide here.
    $adjustment_amount = $adjustment_amount->divide($quantity);

    $order_item->addAdjustment(new Adjustment([
      'type' => 'promotion',
      'label' => t('Discount'),
      'amount' => $adjustment_amount->multiply('-1'),
      'source_id' => $promotion->id(),
    ]));
  }

  /**
   * Gets the offer amount.
   *
   * @return \Drupal\commerce_price\Price|null
   *   The amount, or NULL if unknown.
   */
  protected function getAmount() {
    if (!empty($this->configuration['amount'])) {
      $amount = $this->configuration['amount'];
      return new Price($amount['number'], $amount['currency_code']);
    }
  }

}
