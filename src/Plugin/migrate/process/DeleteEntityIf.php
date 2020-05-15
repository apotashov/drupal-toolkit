<?php

namespace Drupal\toolkit\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\MigrateSkipRowException;

/**
 * Delete the entity, if a source property says to.
 *
 * This is used to delete items already imported.
 *
 *  Available configuration keys:
 * - entity_type: The entity type to delete.
 *
 * Examples:
 *
 * @code
 * process:
 *   deleted:
 *     plugin: delete_entity_if
 *     source: delete
 *     entity_type: node
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "delete_entity_if"
 * )
 */
class DeleteEntityIf extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The migration.
   *
   * @var \Drupal\migrate\Plugin\MigrationInterface
   */
  protected $migration;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migration = $migration;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Check if we should not delete.
    if (!$value) {
      // Skip this processing.
      throw new MigrateSkipProcessException();
    }

    // Load the ID map.
    $map = $row->getIdMap();

    // Load the entity storage.
    $storage = $this->entityTypeManager
      ->getStorage($this->configuration['entity_type']);

    // Attempt to load the entity.
    if (!empty($map['destid1']) && ($entity = $storage->load($map['destid1']))) {
      // Delete the entity.
      $entity->delete();
    }

    // This entity was deleted, so skip this entire row.
    throw new MigrateSkipRowException();
  }

}
