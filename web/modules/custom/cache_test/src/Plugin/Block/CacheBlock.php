<?php

namespace Drupal\cache_test\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * A demo block to test caching.
 *
 * @Block(
 *  id = "cache_block",
 *  admin_label = @Translation("Cache Block")
 * )
 */
class CacheBlock extends BlockBase {

  /**
   * {@inheritDoc}
   */
  public function build() {
    $random_number = rand(1000, 9999);
    $build = [
      '#markup' => $this->t("The random number is @num", ['@num' => $random_number]),
    ];

    $metadata = new BubbleableMetadata();
    $metadata->setCacheTags(['node:1', 'user_list']);
    $metadata->applyTo($build);

    return $build;
  }

}
