<?php

namespace Drupal\toolkit\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\Routing\Route;

/**
 * Parameter converter for converting a given field value to an entity.
 *
 * Rather than having to pass in an entity ID through a route, this converter
 * allows you to specify an entity type and field name to attempt to match the
 * param against. This should only be used for fields with unique values.
 */
class EntityFieldValueParamConverter implements ParamConverterInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return ($definition['type'] == 'entity_field_value');
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    // Load entity storage.
    $storage = $this->entityTypeManager
      ->getStorage($definition['entity_type_id']);

    // Build the entity query.
    $query = $storage
      ->getQuery()
      ->condition($definition['field_name'], $value);

    // Check if we are filtering on the entity bundle.
    if (!empty($definition['bundle'])) {
      $query->condition($storage->getEntityType()->getKeys()['bundle'], $definition['bundle']);
    }

    // Execute the query.
    $entity_ids = $query->execute();

    // Check if an ID was returned.
    if (!empty($entity_ids)) {
      // Load and return the entity.
      return $storage->load(reset($entity_ids));
    }

    return NULL;
  }

}
