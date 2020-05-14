<?php

namespace Drupal\toolkit;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class interface for revisionable toolkit content
 * entities.
 *
 * @ingroup toolkit
 */
interface ContentEntityRevisionStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of entity revision IDs for a specific toolkit content entity.
   *
   * @param \Drupal\toolkit\ContentEntityBase $entity
   *   The toolkit content entity.
   *
   * @return int[]
   *   toolkit content entity revision IDs (in ascending order).
   */
  public function revisionIds(ContentEntityBase $entity);

}
