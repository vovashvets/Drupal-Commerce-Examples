<?php

namespace Drupal\custom_commerce_order\EventSubscriber;

use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sends an email when the order transitions to Fulfillment.
 */
class OrderFulfillmentSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * Constructs a new OrderFulfillmentSubscriber object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   */
  public function __construct(
    LanguageManagerInterface $language_manager,
    MailManagerInterface $mail_manager
  ) {
    $this->languageManager = $language_manager;
    $this->mailManager = $mail_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      'commerce_order.fulfill.post_transition' => ['sendEmail', -100],
    ];
    return $events;
  }

  /**
   * Sends the email.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event-       *   The transition event.
   */
  public function sendEmail(WorkflowTransitionEvent $event) {
    // Create the email.
    $order = $event->getEntity();
    $to = $order->getEmail();
    $params = [
      'from' => $order->getStore()->getEmail(),
      'subject' => $this->t(
        'Regarding your order [#@number]',
        ['@number' => $order->getOrderNumber()]
      ),
      'body' => ['#markup' => $this->t(
        'Your order with #@number that you have placed with us has been processed and is awaiting fulfillment.',
        ['@number' => $order->getOrderNumber()]
      )],
    ];

    // Set the language that will be used in translations.
    if ($customer = $order->getCustomer()) {
      $langcode = $customer->getPreferredLangcode();
    }
    else {
      $langcode = $this->languageManager->getDefaultLanguage()->getId();
    }

    // Send the email.
    $this->mailManager->mail('commerce', 'receipt', $to, $langcode, $params);
  }

}
