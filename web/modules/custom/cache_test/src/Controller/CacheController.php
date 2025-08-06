<?php

namespace Drupal\cache_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\Cache;

/**
 * Controller which displays the content of the '/cache-demo' page.
 */
class CacheController extends ControllerBase {

  /**
   * Variable which stores the current user.
   *
   * @var Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Role storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $roleStorage;

  /**
   * Constructs the controller.
   *
   * @param Drupal\Core\Session\AccountProxyInterface $user
   *   The account proxy interface.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(AccountProxyInterface $user, EntityTypeManagerInterface $entity_type_manager) {
    $this->currentUser = $user;
    $this->roleStorage = $entity_type_manager->getStorage('user_role');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Index function.
   *
   * @return array
   *   Returns the content of the page.
   */
  public function caching() {
    $user_name = $this->currentUser->getAccountName();
    $roles = $this->currentUser->getRoles();

    // Get the second role if available, fallback to first.
    $role_machine_name = $roles[1] ?? $roles[0];
    $role_label = $this->roleStorage->load($role_machine_name)->label();

    return [
      '#title' => $user_name,
      'display-role' => [
        '#markup' => $this->t('Welcome back, @roles <br>', ['@roles' => $role_label]),
        '#cache' => [
          'contexts' => ['user.roles'],
        ],
      ],
      'fixed' => [
        '#markup' => 'This is fixed ---> ' . time() . '<br>',
        '#cache' => [
          'max-age' => Cache::PERMANENT,
          'keys' => ['fixed'],
        ],
      ],
      'cache-1' => [
        '#markup' => 'Change after 10 seconds ---> ' . time() . '<br>',
        '#cache' => [
          'max-age' => 10,
          'keys' => ['cache-1'],
        ],
        'cache-2' => [
          '#markup' => 'Change after 20 seconds ---> ' . time() . '<br>',
          '#cache' => [
            'max-age' => 20,
            'keys' => ['cache-2'],
          ],
        ],
        'cache-3' => [
          '#markup' => 'Change contexts ---> ' . time() . '<br>',
          '#cache' => [
            'contexts' => ['url.query_args'],
            'keys' => ['cache-3'],
          ],
        ],
      ],
    ];
  }

}
