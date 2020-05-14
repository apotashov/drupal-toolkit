<?php

/**
 * @file
 * Contains views hook implementations.
 */

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Implements hook_views_data_alter().
 */
function toolkit_views_data_alter(array &$data) {
  // This was copied from verf but adds our custom entity entity reference
  // autocomplete filter.
  $manager = \Drupal::entityTypeManager();
  $field_config_storage = $manager->getStorage('field_config');
  /** @var \Drupal\field\FieldConfigInterface[] $field_configs */
  $field_configs = $field_config_storage->loadByProperties([
    'field_type' => 'entity_reference',
  ]);
  foreach ($field_configs as $field_config) {
    $table_name = $field_config->getTargetEntityTypeId() . '__' . $field_config->getName();
    $column_name = $field_config->getName() . '_target_id';
    if (isset($data[$table_name][$column_name])) {
      $data[$table_name][$column_name . '_era'] = toolkit_views_era_filter(
        $field_config->label(),
        $column_name,
        $table_name,
        $field_config->getName(),
        $field_config->getTargetEntityTypeId(),
        $field_config->getSetting('target_type'),
        $field_config->getSetting('handler')
      );
    }

    $table_name = $field_config->getTargetEntityTypeId() . '_revision__' . $field_config->getName();
    if (isset($data[$table_name][$column_name])) {
      $data[$table_name][$column_name . '_era'] = toolkit_views_era_filter(
        $field_config->label(),
        $column_name,
        $table_name,
        $field_config->getName(),
        $field_config->getTargetEntityTypeId(),
        $field_config->getSetting('target_type'),
        $field_config->getSetting('handler')
      );
    }
  }

  foreach ($manager->getDefinitions() as $entity_type) {
    if (!$entity_type->isSubclassOf(FieldableEntityInterface::class)) {
      continue;
    }

    // We cannot alter Views data if we cannot map fields to tables.
    $entity_storage = $manager->getStorage($entity_type->id());
    if (!($entity_storage instanceof SqlEntityStorageInterface)) {
      continue;
    }

    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $base_field_definitions */
    $base_field_definitions = \Drupal::service('entity_field.manager')
      ->getBaseFieldDefinitions($entity_type->id());
    $table_mapping = $entity_storage->getTableMapping();

    // Loop through all of this entity type's stored fields.
    foreach ($table_mapping->getTableNames() as $table_name) {
      foreach ($table_mapping->getFieldNames($table_name) as $field_name) {
        // Skip fields that are not base fields.
        if (!isset($base_field_definitions[$field_name])) {
          continue;
        }

        // Only add to entity_reference fields.
        $base_field_storage_definition = $base_field_definitions[$field_name]->getFieldStorageDefinition();
        if ($base_field_storage_definition->getType() !== 'entity_reference') {
          continue;
        }

        $settings = $base_field_storage_definition->getSettings();

        // Get the name of the entity that this ER field referenced.
        $target_type = $base_field_storage_definition->getSetting('target_type');
        // Get the target entity plugin definition.
        $target_type_definition = $manager->getDefinition($target_type);
        // Get the maximum number of items allowed for the field.
        $cardinality = $base_field_storage_definition->getCardinality();
        // Provide different $column_name depending on whether referenced entity
        // is config(or maximum number of values is allowed) or content entity.
        if ($target_type_definition instanceof ConfigEntityType || $cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
          $column_name = $field_name . '_target_id';
        }
        else {
          $column_name = $table_mapping->getFieldColumnName($base_field_storage_definition, $field_name);
        }

        $data[$table_name][$column_name . '_era'] = toolkit_views_era_filter(
          $base_field_storage_definition->getLabel(),
          $column_name,
          $table_name,
          $field_name,
          $entity_type->id(),
          $settings['target_type'],
          $base_field_storage_definition->getSetting('handler')
        );
      }
    }
  }
}

/**
 * Helper function to build an entity reference autocomplete view filter.
 *
 * @param string $label
 *   The filter label.
 * @param string $column_name
 *   The column name.
 * @param string $table_name
 *   The table name.
 * @param string $field_name
 *   The field name.
 * @param string $entity_type_id
 *   The entity type ID.
 * @param string $target_entity_type_id
 *   The target entity type ID.
 * @param string $field_handler
 *   The field handler.
 * @param string $group
 *   The filter group.
 * @param string $help
 *   The filter help text.
 */
function toolkit_views_era_filter($label, $column_name, $table_name, $field_name, $entity_type_id, $target_entity_type_id, $field_handler, $group = 'Entity reference autocomplete', $help = '') {
  return [
    'title' => $label,
    'group' => $group,
    'help' => $help,
    'filter' => [
      'field' => $column_name,
      'table' => $table_name,
      'id' => 'entity_reference_autocomplete',
      'additional fields' => [],
      'field_name' => $field_name,
      'entity_type' => $entity_type_id,
      'target_entity_type_id' => $target_entity_type_id,
      'field_handler' => $field_handler,
      'allow empty' => TRUE,
    ],
  ];
}
