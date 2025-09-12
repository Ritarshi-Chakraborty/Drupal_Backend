<?php

namespace Drupal\blogs\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;

/**
 * Provides a 'Related Blogs' Block
 * 
 * @Block(
 *  id = "related_blogs_block",
 *  admin_label = @Translation("Related Blogs")
 * )
 */
class RelatedBlogsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The route match interface.
   *
   * @var Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function build() {
    // Get the node id from the route.
    $node = $this->routeMatch->getParameter('node');
    $nid = $node->id();

    // Get the author id.
    $authorId = $node->getOwnerId();

    // Get the database connection.
    $connection = Database::getConnection();
    // Build the query.
    $query = $connection->select('node_field_data', 'n')
      ->fields('n', ['nid', 'title'])
      ->condition('n.uid', $authorId)
      ->condition('n.type', 'blog')
      ->condition('n.nid', $nid, '!=');

    // Join the table on the blog where like_status = 1.
    $query->leftJoin('node__like_dislike_count', 'ldc', 'ldc.blog = n.nid AND ldc.like_status = 1');
    // Add a condition to count only likes (like_status = 1).
    $query->addExpression('COUNT(ldc.like_status)', 'likes_count');
    // Group by node ID and title, since we're using aggregation.
    $query->groupBy('n.nid');
    $query->groupBy('n.title');
    // Order by the likes count in descending order (most likes first).
    $query->orderBy('likes_count', 'DESC');

    // Get the node list.
    $nodes = $query->execute()->fetchAll();
    $items = [];

    // Build the list.
    foreach ($nodes as $node) {
      $items[] = [
        '#type' => 'link',
        '#title' => $node->title,
        '#url' => Url::fromRoute('entity.node.canonical', ['node' => $node->nid]),
      ];
    }

    $build = [
      '#theme' => 'item_list',
      '#items' => $items,
      '#cache' => [
        'contexts' => [
          'url.path',
        ],
      ],
    ];

    return $build;
  }

}
