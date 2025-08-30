<?php

namespace Drupal\product_cart\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for the Thank You page after a "Buy Now" action.
 */
class ThankYouController extends ControllerBase {

  /**
   * The entity type manager service.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritDoc}
   */
  public function __construct(AccountProxyInterface $current_user, EntityTypeManagerInterface $entityTypeManager,) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Displays the Thank You page with product details.
   *
   * Retrieves the product ID from the session, loads the product node,
   * and displays relevant purchase information including images.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current HTTP request.
   *
   * @return array
   *   A render array representing the Thank You message or error.
   */
  public function thankYou(Request $request) {
    $session = $request->getSession();
    $nid = $session->get('buy_now');

    if (!$nid) {
      return [
        '#markup' => $this->t('Access denied or session expired.'),
        '#cache' => [
          'max-age' => 0,
          'contexts' => ['session'],
        ],
      ];
    }

    // Clean up immediately.
    $session->remove('buy_now');
    $session->save();

    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    if (!$node) {
      return [
        '#markup' => $this->t('Product not found.'),
        '#cache' => [
          'max-age' => 0,
          'contexts' => ['session'],
        ],
      ];
    }

    $title = $node->label();
    $username = $this->currentUser->getDisplayName();

    $image_render_array = [];
    if ($node->hasField('field_product_images') && !$node->get('field_product_images')->isEmpty()) {
      foreach ($node->get('field_product_images') as $image_item) {
        $file_entity = $image_item->entity;
        if ($file_entity) {
          $image_uri = $file_entity->getFileUri();
          $alt = $image_item->alt;

          $image_render_array[] = [
            '#theme' => 'image',
            '#uri' => $image_uri,
            '#alt' => $alt,
            '#title' => $alt,
            '#style_name' => 'medium',
          ];
        }
      }
    }

    return [
      '#theme' => 'item_list',
      '#items' => [
        $this->t('Product: @title', ['@title' => $title]),
        $this->t('Quantity: 1'),
        $this->t('Purchased by: @user', ['@user' => $username]),
        $image_render_array,
      ],
      '#cache' => [
        'max-age' => 0,
        'contexts' => ['session'],
      ],
    ];
  }

}
