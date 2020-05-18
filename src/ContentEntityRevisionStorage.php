<?php

namespace Drupal\toolkit;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for revisionable content entities.
 *
 * This extends the base storage class, adding required special handling for
 * toolkit content entities.
 */
class ContentEntityRevisionStorage extends SqlContentEntityStorage implements ContentEntityRevisionStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(EntityInterface $entity) {
    $entity_type = $entity->getEntityType();
    $revision_table = $entity_type->getRevisionTable();
    $revision_field = $entity_type->getKey('revision');
    $id_field = $entity_type->getKey('id');

    return $this->database
      ->select($revision_table)
      ->fields($revision_table, [$revision_field])
      ->condition($id_field, $entity->id())
      ->orderBy($revision_field)
      ->execute()
      ->fetchCol();
  }

}
