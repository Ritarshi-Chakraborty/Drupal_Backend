<?php

namespace Drupal\custom_schedule\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\node\Entity\Node;

/**
 * Deletes article nodes queued for deletion.
 *
 * @QueueWorker(
 *   id = "article_delete_queue",
 *   title = @Translation("Article Delete Queue Worker"),
 *   cron = {"time" = 30}
 * )
 */
class ArticleDeleteQueueWorker extends QueueWorkerBase {

  /**
   * {@inheritDoc}
   */
  public function processItem($nid) {
    $node = Node::load($nid);
    if ($node) {
      if ($node->getType() === 'article') {
        $title = $node->getTitle();
        $node->delete();
        \Drupal::logger('custom_schedule')->notice('Node @title with id @nid got deleted', [
          '@title' => $title,
          '@nid' => $nid,
        ]);
      }
    }
  }

}
