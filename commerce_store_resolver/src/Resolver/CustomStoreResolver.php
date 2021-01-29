<?php

namespace Drupal\commerce_store_resolver\Resolver;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\commerce_store\Resolver\StoreResolverInterface;
use Drupal\commerce_store\Entity\Store;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Returns the store for an ID set in a cookie.
 */
class CustomStoreResolver implements StoreResolverInterface {

  /**
   * The store storage.
   *
   * @var \Drupal\commerce_store\StoreStorageInterface
   */
  protected $storage;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new CookieStoreResolver object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RequestStack $request_stack) {
    $this->storage = $entity_type_manager->getStorage('commerce_store');
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve() {
//    $current_request = $this->requestStack->getCurrentRequest();
//    $store_id = $current_request->cookies->get('Drupal_visitor_store_id');

    $store_id = 5; // manually set Store ID

    if ($store_id) {
      $store = $this->storage->load($store_id);
      return $store;
    }
  }
}
