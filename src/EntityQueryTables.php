<?php

namespace Drupal\toolkit;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\Query\Sql\Tables;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * Override of Drupal\Core\Entity\Query\Sql\Tables.
 *
 * @see EntityQuerySql
 */
class EntityQueryTables extends Tables {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * @param \Drupal\Core\Database\Query\SelectInterface $sql_query
   */
  public function __construct(SelectInterface $sql_query) {
    parent::__construct($sql_query);
    $this->entityFieldManager = \Drupal::service('entity_field.manager');
  }

  /**
   * {@inheritdoc}
   */
  public function addField($field, $type, $langcode) {
    // Determine and load the entity type.
    $entity_type_id = $this->sqlQuery->getMetaData('entity_type');
    $entity_type = $this->entityManager->getDefinition($entity_type_id);

    // Check if the entity type is not translatable.
    if (!$entity_type->isTranslatable()) {
      // Check if the entity type is not revisionable.
      if (!$entity_type->isRevisionable()) {
        // Check if the field is part of the base table.
        if ($this->isFieldOnBaseTable($field, $entity_type)) {
          // Return the field with the base table to avoid unneeded joins.
          return "base_table.{$field}";
        }
      }
    }

    return parent::addField($field, $type, $langcode);
  }

  /**
   * Determine if a field is on the base table of the entity type.
   *
   * @param string $field
   *   The field name.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @return bool
   *   TRUE if the field is part of the entity base table, otherwise FALSE.
   */
  public function isFieldOnBaseTable(string $field, EntityTypeInterface $entity_type) {
    // Get base field definitions for this entity type.
    $base_field_definitions = $this->entityFieldManager
      ->getBaseFieldDefinitions($entity_type->id());

    // Check if this is a base field.
    if (isset($base_field_definitions[$field])) {
      // Check if the base and data tables are the same.
      if (!$entity_type->getDataTable()) {
        // Check for single cardinality.
        if ($base_field_definitions[$field]->getCardinality() == 1) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

}
