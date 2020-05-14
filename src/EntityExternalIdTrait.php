<?php

namespace Drupal\toolkit;

/**
 * Provides a trait for storing an entity's external ID.
 *
 * The external ID field name must be stored as an entity key with the key
 * "external_id".
 */
trait EntityExternalIdTrait {

  /**
   * Return the parent entity reference field name.
   *
   * @return string
   *   The parent entity reference field name.
   */
  public function getExternalIdFieldName() {
    return $this->getEntityType()->getKey('external_id');
  }

  /**
   * Get the external ID.
   *
   * @return mixed
   *   The entity external ID, or NULL, if one is not set.
   */
  public function getExternalId() {
    if ($field_name = $this->getExternalIdFieldName()) {
      return $this->get($field_name)->value;
    }

    return NULL;
  }

  /**
   * Set the external ID.
   *
   * @param mixed $external_id
   *   The external ID.
   *
   * @return mixed
   *   The called entity.
   */
  public function setExternalId($external_id) {
    $this->set($this->getExternalIdFieldName(), $external_id);
    return $this;
  }

}
