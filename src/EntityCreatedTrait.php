<?php

namespace Drupal\toolkit;

/**
 * Provides a trait for entities with a created date.
 */
trait EntityCreatedTrait {

  /**
   * Gets the creation timestamp.
   *
   * @return int
   *   Creation timestamp of the entity.
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * Sets the creation timestamp.
   *
   * @param int $timestamp
   *   The creation timestamp.
   *
   * @return mixed
   *   The called entity.
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

}
