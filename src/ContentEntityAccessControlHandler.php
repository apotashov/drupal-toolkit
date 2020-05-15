<?php

namespace Drupal\toolkit;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\user\Entity\User;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Access controller for content entities.
 */
class ContentEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    // Format the entity type ID for the permission strings.
    $entity_type_id_formatted = str_replace('_', ' ', $entity->getEntityTypeId());

    // Check the operation.
    switch ($operation) {
      case 'view':
        // Check if this entity type can be published/unpublished.
        if (entity_is_publishable($entity)) {
          // Check if the entity is published.
          if ($entity->isPublished()) {
            $result = $this->allowedIfHasPermissionOrAdmin($account, "view published {$entity_type_id_formatted} entities");
          }
          else {
            $result = $this->allowedIfHasPermissionOrAdmin($account, "view unpublished {$entity_type_id_formatted} entities");
          }
          $result->addCacheableDependency($entity);
        }
        else {
          $result = $this->allowedIfHasPermissionOrAdmin($account, "view {$entity_type_id_formatted} entities");
        }
        return $result;

      case 'update':
        return $this->allowedIfHasPermissionOrAdmin($account, "edit {$entity_type_id_formatted} entities");

      case 'delete':
        return $this->allowedIfHasPermissionOrAdmin($account, "delete {$entity_type_id_formatted} entities");
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkFieldAccess($operation, FieldDefinitionInterface $field_definition, AccountInterface $account, FieldItemListInterface $items = NULL) {
    // Always allow admin access.
    if ($this->userHasAdminPermission($account)) {
      return AccessResult::allowed()
        ->cachePerPermissions();
    }

    // Check if this field is admin-only.
    if ($field_definition->getSetting('admin_only')) {
      // Restrict access to admins.
      return AccessResult::forbidden()
        ->cachePerPermissions();
    }

    // Check if this field is admin-only for editing.
    if ($field_definition->getSetting('admin_edit_only') && ($operation == 'edit')) {
      // Restrict access to admins.
      return AccessResult::forbidden()
        ->cachePerPermissions();
    }

    return AccessResult::allowed();
  }

  /**
   * Provide an access result for a permission or entity admin permission.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account to check access for.
   * @param string $permission
   *   The permission to check for.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   An access result.
   */
  public function allowedIfHasPermissionOrAdmin(AccountInterface $account, string $permission) {
    $permissions = [
      $permission,
    ];

    if ($admin_permission = $this->getAdminPermission()) {
      $permissions[] = $admin_permission;
    }

    return AccessResult::allowedIfHasPermissions($account, $permissions, 'OR');
  }

  /**
   * Determine if a user has the admin permission of this entity type.
   *
   * @param Drupal\Core\Session\AccountInterface $account
   *   The account to check.
   *
   * @return bool
   *   TRUE if the user has the admin permission, otherwiese FALSE.
   */
  public function userHasAdminPermission(AccountInterface $account) {
    return $account->hasPermission($this->getAdminPermission());
  }

  /**
   * Get the admin permission name for this entity type.
   *
   * @return string
   *   The admin permission name.
   */
  public function getAdminPermission() {
    return $this->entityType->getAdminPermission();
  }

  /**
   * Helper function to determine if a given user has a give role.
   *
   * @param Drupal\Core\Session\AccountInterface $account
   *   The account to check.
   * @param string $role
   *   The name of the role.
   *
   * @return bool
   *   TRUE if the user has the role, otherwise FALSE.
   */
  public function userHasRole(AccountInterface $account, string $role) {
    return $this->userLoad($account)
      ->hasRole($role);
  }

  /**
   * Helper function to load a user entity from an account interface.
   *
   * @param Drupal\Core\Session\AccountInterface $account
   *   The account to load.
   *
   * @return Drupal\user\Entity\User
   *   The account user entity.
   */
  public function userLoad(AccountInterface $account) {
    return User::load($account->id());
  }

}
