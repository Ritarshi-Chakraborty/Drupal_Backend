<?php

namespace Drupal\ticket_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\Pager\PagerParameters;
use Symfony\Component\HttpFoundation\Request;
use Drupal\node\Entity\Node;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\Cache;

/**
 * Create, update and return JSON data of `ticket` content types.
 */
class TicketsApiController extends ControllerBase {

  /**
   * The entity type manager service.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The pager manager service.
   *
   * @var Drupal\Core\Pager\PagerManagerInterface
   */
  protected $pagerManager;

  /**
   * The pager parameter service.
   *
   * @var Drupal\Core\Pager\PagerParameters
   */
  protected $pagerParameters;

  /**
   * The config factory service.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The account interface service.
   *
   * @var Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, PagerManagerInterface $pagerManager, PagerParameters $pagerParameters, ConfigFactoryInterface $configFactory, AccountProxyInterface $currentUser) {
    $this->entityTypeManager = $entityTypeManager;
    $this->pagerManager = $pagerManager;
    $this->pagerParameters = $pagerParameters;
    $this->configFactory = $configFactory;
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('pager.manager'),
      $container->get('pager.parameters'),
      $container->get('config.factory'),
      $container->get('current_user')
    );
  }

  /**
   * Returns a JSON response of all `ticket` nodes.
   *
   * @param Symfony\Component\HttpFoundation\Request $request
   *   The Request service.
   *
   * @return Drupal\Core\Cache\CacheableJsonResponse
   *   A cacheble JSON response containing ticket list or an error message.
   */
  public function getList(Request $request) {
    $limit = 2;
    $page = max((int) $request->query->get('page', 1), 1);
    $offset = ($page - 1) * $limit;

    // Get total items first (without pagination)
    $total_items = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'ticket')
      ->accessCheck(TRUE)
      ->count()
      ->execute();

    $total_pages = (int) ceil($total_items / $limit);

    $ticket_list = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'ticket')
      ->range($offset, $limit)
      ->accessCheck(TRUE)
      ->execute();

    $tickets = $this->entityTypeManager->getStorage('node')->loadMultiple($ticket_list);
    // Check if any `ticket` node exists.
    if (empty($tickets)) {
      return new CacheableJsonResponse(['blank' => 'No tickets found'], 204);
    }

    $ticket_data = [];
    foreach ($tickets as $ticket) {

      $ticket_data[] = [
        'title' => $ticket->getTitle(),
        'id' => $ticket->id(),
      ];
    }

    $response = new CacheableJsonResponse([
      'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_items' => $total_items,
        'per_page' => $limit,
      ],
      'ticket_list' => $ticket_data,
    ], 200);

    $response->addCacheableDependency([
      '#cache' => [
        'tags' => ['ticket_list'],
        'contexts' => ['url.query_args'],
        'max-age' => 0,
      ],
    ]);

    return $response;
  }

  /**
   * Returns the details of a particular ticket.
   *
   * @param int $id
   *   The node id of the ticket.
   *
   * @return Drupal\Core\Cache\CacheableJsonResponse
   *   A cacheble JSON response containing ticket list or an error message.
   */
  public function getDetail($id) {
    $ticket_list = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'ticket')
      ->condition('nid', $id)
      ->accessCheck(TRUE)
      ->execute();

    $tickets = $this->entityTypeManager->getStorage('node')->loadMultiple($ticket_list);
    // Check if any `ticket` node exists.
    if (empty($tickets)) {
      return new CacheableJsonResponse(['blank' => 'No ticket found'], 200);
    }

    $ticket_data = [];
    foreach ($tickets as $ticket) {
      $users = [];
      $user_list = $ticket->get('field_assigned_to')->getValue();

      foreach ($user_list as $user) {
        $user_entity = $this->entityTypeManager->getStorage('user')->load($user['target_id']);
        if ($user_entity) {
          $users[] = [
            'name' => $user_entity->getDisplayName(),
            'id' => $user_entity->id(),
          ];
        }
      }

      $ticket_data[] = [
        'title' => $ticket->getTitle(),
        'id' => $ticket->id(),
        'url' => $ticket->toUrl()->toString(),
        'assigned_to' => $users,
        'status' => $ticket->get('field_status')->value,
        'priority' => $ticket->get('field_priority')->value,
        'description' => $ticket->get('field_description')->value,
      ];

      $cache_tags[] = 'node:' . $ticket->id();
    }

    $response = new CacheableJsonResponse($ticket_data, 200);
    $response->addCacheableDependency([
      '#cache' => [
        'tags' => $cache_tags,
        'max-age' => 0,
      ],
    ]);

    return $response;
  }

  /**
   * Creates a ticket.
   *
   * @param Symfony\Component\HttpFoundation\Request $request
   *   The Request service.
   *
   * @return Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the success or error message.
   */
  public function createTicket(Request $request) {
    $data = json_decode($request->getContent(), TRUE);
    // Check if the title has been passed.
    if (empty($data['title'])) {
      return new JsonResponse(['error' => 'Title is required'], 400);
    }
    // Check if the description has been passed.
    if (empty($data['description'])) {
      return new JsonResponse(['error' => 'Description is required'], 400);
    }

    // Check if the email has been passed.
    if (!empty($data['email'])) {
      // Email validation.
      if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return new JsonResponse(['error' => 'Invalid email format'], 400);
      }
      $email = $data['email'];
    }
    else {
      // Set email to default site email.
      $site_email = $this->configFactory->get('system.site')->get('mail');
      $email = $site_email;
    }

    // Handle status field.
    if (!empty($data['status'])) {
      $allowed_statuses = ['open', 'closed', 'resolved', 'in_progress'];
      if (!in_array($data['status'], $allowed_statuses)) {
        return new JsonResponse(['error' => 'Invalid status. Allowed values are: open, closed, resolved, in_progress.'], 400);
      }
      $status = $data['status'];
    }
    else {
      $status = 'open';
    }

    // Handle priority field.
    if (!empty($data['priority'])) {
      $allowed_statuses = ['low', 'medium', 'high', 'urgent'];
      if (!in_array($data['priority'], $allowed_statuses)) {
        return new JsonResponse(['error' => 'Invalid priority. Allowed values are: low, medium, high, urgent.'], 400);
      }
      $priority = $data['priority'];
    }
    else {
      $priority = 'open';
    }

    // Handle assigned_to.
    if (!empty($data['assigned_to'])) {
      $user = $this->entityTypeManager->getStorage('user')->load($data['assigned_to']);
      if (!$user) {
        return new JsonResponse(['error' => 'Invalid assigned_to user ID'], 400);
      }
      $assigned_to = $user->id();
    }
    else {
      // Default assigned user.
      $assigned_to = 1;
    }

    // Get current user ID.
    $author_uid = $this->currentUser->id();

    // Create the node.
    $node = Node::create([
      'type' => 'ticket',
      'title' => $data['title'],
      'field_description' => $data['description'],
      'field_priority' => $priority,
      'field_reporter_email' => $email,
      'field_status' => $status,
      'field_assigned_to' => $assigned_to,
      'uid' => $author_uid,
    ]);

    $node->save();
    return new JsonResponse(['message' => 'Ticket created successfully', 'nid' => $node->id()], 201);
  }

  /**
   * Updates the status of a ticket node.
   *
   * @param int $id
   *   The node ID of the ticket.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response indicating success or error.
   */
  public function updateStatus($id, Request $request) {
    // Get the data from the body.
    $data = json_decode($request->getContent(), TRUE);
    // Check if status has been passed.
    if (empty($data['status'])) {
      return new JsonResponse(['error' => 'Status is required.'], 400);
    }

    // Load the ticket node.
    $ticket = $this->entityTypeManager->getStorage('node')->load($id);
    // Check if ticket of the Id exists of type `ticket`.
    if (!$ticket || $ticket->bundle() !== 'ticket') {
      return new JsonResponse(['error' => 'Ticket not found.'], 404);
    }
    // Get the old status of the ticket.
    $old_status = $ticket->get('field_status')->value;

    $allowed_statuses = ['open', 'closed', 'resolved', 'in_progress'];
    $new_status = $data['status'];
    // Check if the status passed is a valid one.
    if (!in_array($new_status, $allowed_statuses)) {
      return new JsonResponse(['error' => 'Invalid status. Allowed values are: open, closed, resolved, in_progress.'], 400);
    }

    // Update status.
    $ticket->set('field_status', $new_status);
    $ticket->save();

    // Invalidate cache tags.
    Cache::invalidateTags([
      'ticket_list',
      'node:' . $ticket->id(),
    ]);

    return new JsonResponse([
      'message' => 'Status updated successfully.',
      'ticket_id' => $ticket->id(),
      'old_status' => $old_status,
      'new_status' => $new_status,
    ], 200);
  }

}
