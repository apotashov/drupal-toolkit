<?php

namespace Drupal\toolkit;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface ContextualEntityInterface.
 */
interface ContextualEntityInterface {

  /**
   * Get an array of contextual entity information.
   *
   * @return array
   *   An array of entity information arrays for all entities that are
   *   considered contextual for the current page request.
   */
  public function getContextualEntityInfo();

  /**
   * Set the contextual entities for this page request.
   *
   * The contextual entity is used to gather entity information and provide it
   * to the JS-layer for making web service calls.
   *
   * @param \Drupal\Core\Entity\EntityInterface|\Drupal\Core\Entity\EntityInterface[]|null $entity
   *   Either a single entity OR an array of entities OR NULL.
   * @param bool $reset
   *   TRUE to reset all contextual entities.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\Core\Entity\EntityInterface[]|null
   *   Either a single entity OR an array of entities OR NULL.
   */
  public function setContextualEntity($entity = NULL, $reset = FALSE);

  /**
   * Get the contextual entities for this page request.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\Core\Entity\EntityInterface[]|null
   *   Either a single entity OR an array of entities OR NULL.
   */
  public function getContextualEntities();

  /**
   * Helper function to gather basic information about an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity object.
   *
   * @return array
   *   An array of information about the entity.
   */
  public function getEntityInfo(EntityInterface $entity);

}
