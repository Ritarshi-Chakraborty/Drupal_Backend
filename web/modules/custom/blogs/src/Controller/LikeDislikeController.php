<?php

namespace Drupal\blogs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Cache\Cache;

/**
 * Controller class for handling like and dislike actions on blog posts.
 */
class LikeDislikeController extends ControllerBase {

  /**
   * The current user.
   *
   * @var Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The database connection.
   *
   * @var Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritDoc}
   */
  public function __construct(AccountProxyInterface $user) {
    $this->connection = Database::getConnection();
    $this->currentUser = $user;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }

  /**
   * Handles the "like" action for a blog post.
   *
   * @param int $id
   *   The node ID of the blog post.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response with the result of the action.
   */
  public function like($id) {
    $uid = $this->currentUser->id();
    // Check if the user is anonymous.
    if ($uid == 0) {
      return new JsonResponse([
        'status' => 'nochange',
        'message' => 'Please login to like/dislike a post!'
      ]);
    }

    // Check if a record exists with the current credentials.
    $query = $this->connection->select('node__like_dislike_count', 'n')
      ->fields('n')
      ->condition('n.blog', $id)
      ->condition('n.user', $uid)
      ->execute();
    $record = $query->fetchAssoc();

    // Update the record if it exists.
    if($record) {
      // Check if the record's like status is already 1.
      if($record['like_status'] == 1) {
        return new JsonResponse([
          'status' => 'nochange',
          'message' => 'You have already liked this post!'
        ]);
      }
      // Otherwise, update like to 1 and dislike to 0.
      else {
        $this->connection->update('node__like_dislike_count')
          ->fields([
            'like_status' => 1,
            'dislike_status' => 0,
          ])
          ->condition('blog', $id)
          ->condition('user', $uid)
          ->execute();
      }
    }
    // Insert a new record if no record exist.
    else {
      $this->connection->insert('node__like_dislike_count')
        ->fields([
          'user' => $uid,
          'blog' => $id,
          'like_status' => 1,
          'dislike_status' => 0,
        ])
        ->execute();
    }

    // Invalidate the page and render cache for this node.
    Cache::invalidateTags(['node:' . $id]);
    // Get the updated like and dislike count.
    $count = $this->getCount($id);

    return new JsonResponse([
      'status' => 'success',
      'message' => 'You have liked this post!',
      'likes' => $count['likes'],
      'dislikes' => $count['dislikes']
    ]);
  }

  /**
   * Handles the "dislike" action for a blog post.
   *
   * @param int $id
   *   The node ID of the blog post.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response with the result of the action.
   */
  public function dislike($id) {
    $uid = $this->currentUser->id();
    // Check if the user is anonymous.
    if ($uid == 0) {
      return new JsonResponse([
        'status' => 'nochange',
        'message' => 'Please login to like/dislike a post!'
      ]);
    }

    // Check if a record exists with the current credentials.
    $query = $this->connection->select('node__like_dislike_count', 'n')
      ->fields('n')
      ->condition('n.blog', $id)
      ->condition('n.user', $uid)
      ->execute();
    $record = $query->fetchAssoc();

    // Update the record if it exists.
    if($record) {
      // Check if the record's dislike status is already 1.
      if($record['dislike_status'] == 1) {
        return new JsonResponse([
          'status' => 'nochange',
          'message' => 'You have already disliked this post!'
        ]);
      }
      // Otherwise, update like to 0 and dislike to 1.
      else {
        $this->connection->update('node__like_dislike_count')
          ->fields([
            'like_status' => 0,
            'dislike_status' => 1,
          ])
          ->condition('blog', $id)
          ->condition('user', $uid)
          ->execute();
      }
    }
    // Insert a new record if no record exist.
    else {
      $this->connection->insert('node__like_dislike_count')
        ->fields([
          'user' => $uid,
          'blog' => $id,
          'like_status' => 0,
          'dislike_status' => 1,
        ])
        ->execute();
    }

    // Invalidate the page and render cache for this node.
    Cache::invalidateTags(['node:' . $id]);
    // Get the updated like and dislike count.
    $count = $this->getCount($id);

    return new JsonResponse([
      'status' => 'success',
      'message' => 'You have disliked this post!',
      'likes' => $count['likes'],
      'dislikes' => $count['dislikes']
    ]);
  }

  /**
   * Retrieves the total like and dislike count for a blog post.
   *
   * @param int $nid
   *   The node ID of the blog post.
   *
   * @return array
   *   An associative array containing the number of likes and dislikes.
   */
  protected function getCount($nid) {
    // Get the number of likes for the current node.
    $likes = $this->connection->select('node__like_dislike_count')
      ->condition('blog', $nid)
      ->condition('like_status', 1)
      ->countQuery()
      ->execute()
      ->fetchField();

    // Get the number of dislikes for the current node.
    $dislikes = $this->connection->select('node__like_dislike_count')
      ->condition('blog', $nid)
      ->condition('dislike_status', 1)
      ->countQuery()
      ->execute()
      ->fetchField();

    return [
      'likes' => (int) $likes,
      'dislikes' => (int) $dislikes,
    ];
  }
}
