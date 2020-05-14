<?php

namespace Drupal\toolkit;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for revisionable content entities.
 *
 * This extends the base storage class, adding required special handling for
 * toolkit content entities.
 *
 * @ingroup toolkit
 */
class ContentEntityRevisionStorage extends SqlContentEntityStorage implements ContentEntityRevisionStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(ContentEntityBase $entity) {
    $entity_type_id = $entity->getEntityTypeId();
    return $this->database->query(
      "SELECT vid FROM {{$entity_type_id}_revision} WHERE id=:id ORDER BY vid",
      [':id' => $entity->id()]
    )->fetchCol();
  }

}
