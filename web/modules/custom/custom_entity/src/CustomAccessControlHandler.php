<?php

namespace Drupal\custom_entity;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;

/**
 * Access control for the content entity.
 */
class CustomAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritDoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    $admin_permission = $this->entityType->getAdminPermission();
    if ($account->hasPermission($admin_permission)) {
      return AccessResult::allowed();
    }
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view_movie');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit_movie');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete_movie');
    }
    return AccessResult::neutral();
  }

}
