<?php

namespace Drupal\toolkit;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Storage handler interface for revisionable content entities.
 */
interface ContentEntityRevisionStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of entity revision IDs for a specific content entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The content entity.
   *
   * @return int[]
   *   Content entity revision IDs (in ascending order).
   */
  public function revisionIds(EntityInterface $entity);

}
