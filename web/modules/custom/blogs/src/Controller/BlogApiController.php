<?php

namespace Drupal\blogs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\taxonomy\Entity\Term;

class BlogApiController extends ControllerBase {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  public function getBlogs() {
    // Buid the query to get all the blogs.
    $blog_list = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'blog')
      ->condition('status', 1)
      ->accessCheck(TRUE);

    // Get the API config form data.
    $config = $this->configFactory->get('blog_config_form.settings');

    // Apply the value of `author role` to the query.
    $author_role = $config->get('role');
    if (!empty($author_role)) {
      // Exclude `authenticated` because it is a special system role & doesn't behave like normal roles.
      if ($author_role !== 'authenticated') {
        $blog_list->condition('uid.entity.roles', $author_role);
      }
    }

    // Apply the value of `author name` to the query.
    $author_name = $config->get('author');
    if (!empty($author_name)) {
      // First get the list of user ids matching the name.
      $user_ids = $this->entityTypeManager->getStorage('user')->getQuery()
        ->condition('name', $author_name . '%', 'LIKE')
        ->accessCheck(TRUE)
        ->execute();

      if (!empty($user_ids)) {
        $blog_list->condition('uid', $user_ids, 'IN');
      }
      else {
        // No matching authors found, return no blogs.
        return new JsonResponse(['blank' => 'No blogs found']);
      }
    }

    // Apply the value of `tag` to the query.
    $config_tag = $config->get('tag');
    if (!empty($config_tag)) {
      // First get the list of term ids matching the name.
      $term_ids = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery()
        ->condition('name', $config_tag)
        ->accessCheck(TRUE)
        ->execute();

      if (!empty($term_ids)) {
        $blog_list->condition('field_blog_tags.target_id', $term_ids, 'IN');
      }
      else {
        // No matching tags found, return no blogs.
        return new JsonResponse(['blank' => 'No blogs found']);
      }
    }

    // Apply the `date range` to the query.
    $start_date = $config->get('start_date');
    $end_date = $config->get('end_date');

    if (!empty($start_date)) {
      $start_timestamp = strtotime($start_date . ' 00:00:00');
      $blog_list->condition('created', $start_timestamp, '>=');
    }
    if (!empty($end_date)) {
      $end_timestamp = strtotime($end_date . ' 23:59:59');
      $blog_list->condition('created', $end_timestamp, '<=');
    }

    // Load all the blogs.
    $blog_ids = $blog_list->execute();
    $blogs = $this->entityTypeManager->getStorage('node')->loadMultiple($blog_ids);

    // Return a blank response if no blogs are found.
    if (empty($blogs)) {
      return new JsonResponse(['blank' => 'No blogs found']);
    }

    // Proceed if we get some blogs.
    $blog_data = [];
    foreach ($blogs as $blog) {
      // Get the list of associated tags.
      $blog_tags = [];
      $tags = $blog->get('field_blog_tags')->referencedEntities();
      foreach ($tags as $tag) {
        $blog_tags[] = [
          'id' => $tag->id(),
          'name' => $tag->getName(),
        ];
      }

      // Build the API response.
      $blog_data[] = [
        'title' => $blog->getTitle(),
        'published_datetime' => [
          'date' => date('Y-m-d', $blog->getCreatedTime()),
          'time' => date('H:i:s A', $blog->getCreatedTime()),
        ],
        'author' => $blog->getOwner()->getDisplayName(),
        'tags' => $blog_tags,
      ];
    }

    return new JsonResponse($blog_data);
  }

}
