<?php

namespace Drupal\events\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Drupal\node\NodeInterface;

/**
 * Event triggered when a "movie" node is created or updated.
 */
class MovieBudgetEvent extends Event {

  /**
   * The name of the event.
   *
   * @var string
   */
  const EVENT_NAME = 'budget_message';

  /**
   * The movie node.
   *
   * @var Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * Constructs the event.
   *
   * @param Drupal\node\NodeInterface $node
   *   The movie node.
   */
  public function __construct(NodeInterface $node) {
    $this->node = $node;
  }

  /**
   * Returns the node.
   *
   * @return Drupal\node\NodeInterface
   *   The movie node.
   */
  public function getNode() {
    return $this->node;
  }

}
