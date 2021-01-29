<?php

namespace Drupal\commerce_custom_log\EventSubscriber;

use Drupal\commerce_cart\Event\CartEmptyEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmptyCartEventSubscriber implements EventSubscriberInterface {

  /**
   * The log storage.
   *
   * @var \Drupal\commerce_log\LogStorageInterface
   */
  protected $logStorage;

  /**
   * Constructs a new CartEventSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->logStorage = $entity_type_manager->getStorage('commerce_log');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      CartEvents::CART_EMPTY => ['onCartEmpty', 100]
    ];
    return $events;
  }

  /**
   * Creates a log when user deleted all products from the cart.
   *
   * @param \Drupal\commerce_cart\Event\CartEmptyEvent $event
   *   The cart event.
   */
  public function onCartEmpty(CartEmptyEvent $event) {
    $cart = $event->getCart();
    $this->logStorage->generate($cart, 'cart_empty', [NULL
    ])->save();
  }
}
