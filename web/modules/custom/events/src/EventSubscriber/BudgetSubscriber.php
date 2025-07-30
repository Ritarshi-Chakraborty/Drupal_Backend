<?php

namespace Drupal\events\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class which defines the budget message for each "Movie" node.
 */
class BudgetSubscriber implements EventSubscriberInterface {

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

  public function __construct(ConfigFactoryInterface $config_factory, MessengerInterface $messenger) {
    $this->configFactory = $config_factory;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [];
  }

  /**
   * Function which reacts to "Movie" insert or update.
   *
   * @param Drupal\node\NodeInterface $node
   *   Takes the NodeInterface as parameter.
   *
   * @return void
   *   Returns nothing.
   */
  public function onNodeSave(NodeInterface $node) {
    // Get config budget value.
    $budget_config = $this->configFactory->get('site_budget_config.settings');
    $config_budget = (float) $budget_config->get('budget');

    // Get node budget value.
    $node_budget = (float) $node->get('field_budget')->value;

    // Compare and show message.
    if ($node_budget < $config_budget) {
      $this->messenger->addMessage($this->t('The movie is under budget.'));
    }
    elseif ($node_budget > $config_budget) {
      $this->messenger->addMessage($this->t('The movie is over budget.'));
    }
    else {
      $this->messenger->addMessage($this->t('The movie is within budget.'));
    }
  }

}
