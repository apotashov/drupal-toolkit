<?php

namespace Drupal\toolkit;

/**
 * Provides a trait for storing a reference to this entity's parent entity.
 *
 * The parent field name must be stored as an entity key with the key "parent".
 * Using this trait allows you to not only easily determine the parent of an
 * entity, but also easily move up the entire family tree. If an entity using
 * this trait is the contextual entity (see service) for a given page request,
 * the entity's parents will be included as contextual entities.
 */
trait EntityParentTrait {

  /**
   * Return the parent entity reference field name.
   *
   * @return string
   *   The parent entity reference field name.
   */
  public function getParentReferenceFieldName() {
    return $this->getEntityType()->getKey('parent');
  }

  /**
   * Get the parent entity reference entity type ID.
   *
   * This should automatically derive a value using getParentReferenceFieldName().
   *
   * @return string|null
   *   The parent entity reference target entity type ID, or NULL if there is not
   *   one defined.
   */
  public function getParentReferenceEntityTypeId() {
    // Get the parent reference field name.
    if ($parent_field_name = $this->getParentReferenceFieldName()) {
      return $this->getFieldDefinition($parent_field_name)->getSetting('target_type');
    }
    return NULL;
  }

  /**
   * Get the parent entity, if one is defined and present, either one or infinite
   * levels up the relationship tree.
   *
   * @param string|null $parent_entity_type
   *   The parent entity type to search for. If omitted, the type used in
   *   getParentReferenceFieldName() will be used which is the immediate parent
   *   of this entity. If you specific a different type, this function will look
   *   at parent's parent until the target entity type is found.
   *
   * @return mixed|null
   *   The parent entity, if found, or NULL.
   */
  public function getParent(string $parent_entity_type = NULL) {
    // Get the parent reference field name.
    $parent_field_name = $this->getParentReferenceFieldName();

    // Stop if this entity does not have a parent.
    if (!$parent_field_name) {
      return NULL;
    }

    // Attempt to load the parent entity.
    if ($parent = $this->get($parent_field_name)->entity) {
      // Check if this is the correct type.
      if (!$parent_entity_type || ($parent->getEntityTypeId() == $parent_entity_type)) {
        return $parent;
      }

      // Continue searching.
      return $parent->getParent($parent_entity_type);
    }

    return NULL;
  }

  /**
   * Get all of the parent entities.
   *
   * @return array
   *   An array of parent entities.
   */
  public function getParents() {
    $parents = [];

    // Get the parent reference field name.
    $parent_field_name = $this->getParentReferenceFieldName();

    // Stop if this entity does not have a parent.
    if (!$parent_field_name) {
      return [];
    }

    // Attempt to load the parent entity.
    if ($parent = $this->get($parent_field_name)->entity) {
      // Add this parent.
      $parents[] = $parent;

      // Continue searching.
      if ($grand_parents = $parent->getParents()) {
        $parents = array_merge($parents, $grand_parents);
      }
    }

    return $parents;
  }

  /**
   * Set the parent.
   *
   * @param mixed $parent
   *   The parent entity or entity ID.
   *
   * @return mixed
   *   The called entity.
   */
  public function setParent($parent) {
    $this->set($this->getParentReferenceFieldName(), $parent);
    return $this;
  }

}
