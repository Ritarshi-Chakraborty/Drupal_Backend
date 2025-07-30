<?php

namespace Drupal\events\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\events\Event\MovieBudgetEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Reacts to MovieBudgetEvent and checks the budget.
 */
class MovieBudgetSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The configuartion factory service.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactoy;

  /**
   * The messenger service.
   *
   * @var Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs the subscriber.
   *
   * @param Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory service.
   * @param Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MessengerInterface $messenger) {
    $this->configFactory = $config_factory;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      MovieBudgetEvent::EVENT_NAME => 'onMovieBudgetCheck',
    ];
  }

  /**
   * Function which compares the movie budget with the config budget.
   *
   * @param Drupal\events\Event\MovieBudgetEvent $event
   *   The movie budget event.
   *
   * @return void
   *   Returns nothing.
   */
  public function onMovieBudgetCheck(MovieBudgetEvent $event) {
    $node = $event->getNode();
    // Get config budget value.
    $budget_config = $this->configFactory->get('site_budget_config.settings');
    $config_budget = (float) $budget_config->get('budget');
    // Get node budget value.
    $node_budget = (float) $node->get('field_budget')->value;

    // Compare and show message.
    if ($node_budget < $config_budget) {
      $message = $this->t('The movie is under budget.');
    }
    elseif ($node_budget > $config_budget) {
      $message = $this->t('The movie is over budget.');
    }
    else {
      $message = $this->t('The movie is within budget.');
    }

    // Display the message.
    $this->messenger->addStatus($message);
  }

}
