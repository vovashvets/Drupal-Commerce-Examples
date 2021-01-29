<?php
namespace Drupal\custom_commerce_cart\Form;

use Drupal\commerce_cart\Form\AddToCartForm;
use Drupal\Core\Form\FormStateInterface;

class CustomAddToCartForm extends AddToCartForm {

// Override any methods here

  protected function actions(array $form, FormStateInterface $form_state) {
    $actions['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Custom Add to cart'),
      '#submit' => ['::submitForm'],
      '#attributes' => [
        'class' => ['button--add-to-cart'],
      ],
    ];

    return $actions;
  }
}
