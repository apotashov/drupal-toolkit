<?php

namespace Drupal\toolkit;

use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Handles determining the contextual entity for the current page request.
 */
class ContextualEntity implements ContextualEntityInterface {

  /**
   * Current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new ContextualEntity object.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route_match
   *   The current route match service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(CurrentRouteMatch $current_route_match, ModuleHandlerInterface $module_handler) {
    $this->currentRouteMatch = $current_route_match;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {inheritdoc}
   */
  public function getContextualEntityInfo() {
    // Store the entity info.
    $info = [];

    // Load the contextual entities for this page request.
    $contextual_entities = $this->getContextualEntities();

    // Check if there was no contextual entity.
    if (!$contextual_entities) {
      // Iterate the route parameters.
      foreach ($this->currentRouteMatch->getParameters()->all() as $key => $parameter) {
        // Check if this parameter is an entity.
        if (is_object($parameter) && ($parameter instanceof EntityInterface)) {
          // Extract the entity type.
          $entity_type_id = $parameter->getEntityTypeId();

          // Check if we're viewing this entity.
          if ($this->currentRouteMatch->getRouteName() == "entity.{$entity_type_id}.canonical") {
            // Use this as the contextual entity.
            $contextual_entities[] = $parameter;
          }
          // If entity from route is a toolkit entity.
          elseif ($this->isEntityToolkit($parameter)) {
            // Also use this as the contextual entity.
            $contextual_entities[] = $parameter;
          }
        }
      }

      // If request has contextual entities.
      if ($contextual_entities) {
        // Update contextual entities.
        $this->setContextualEntity($contextual_entities);
      }
    }

    // Check if we have a contextual entity now.
    if ($contextual_entities) {
      foreach ($contextual_entities as $contextual_entity) {
        // Generate contextual information about this entity.
        $info[$contextual_entity->uuid()] = $this->getEntityInfo($contextual_entity);

        // Store info about the parent entities.
        // TODO: We may want to get references even if it's not a toolkit rather
        // than just parents.
        if ($this->isEntityToolkit($contextual_entity)) {
          foreach ($contextual_entity->getParents() as $parent) {
            $info[$parent->uuid()] = $this->getEntityInfo($parent);
          }
        }
        else {
          // Iterate the toolkit entity referenes.
          foreach ($this->getEntityReferences($contextual_entity) as $parent) {
            // Store the parent information.
            $info[$parent->uuid()] = $this->getEntityInfo($parent);

            // Store the info for the grandparents.
            foreach ($parent->getParents() as $grandparent) {
              $info[$grandparent->uuid()] = $this->getEntityInfo($grandparent);
            }
          }
        }
      }
    }

    return array_values($info);
  }

  /**
   * {inheritdoc}
   */
  public function setContextualEntity($entity = NULL, $reset = FALSE) {
    $contextual_entities = &drupal_static(__METHOD__, []);

    if ($reset) {
      $contextual_entities = [];
    }

    if ($entity !== NULL) {
      $entities = is_array($entity) ? $entity : [$entity];
      foreach ($entities as $contextual_entity) {
        assert($contextual_entity instanceof EntityInterface, 'Only entities can be set as contextual entities.');
        $contextual_entities[] = $contextual_entity;
      }
    }

    return $contextual_entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getContextualEntities() {
    return $this->setContextualEntity();
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityInfo(EntityInterface $entity) {
    // Build the basic entity info.
    $info = [
      'id' => $entity->id(),
      'uuid' => $entity->uuid(),
      'type' => $entity->getEntityTypeId(),
      'bundle' => $entity->bundle(),
      'label' => $entity->label(),
      'path' => $entity->toUrl('canonical')->toString(),
    ];

    // Gather extra data on a given entity.
    $info['data'] = $this->moduleHandler->invokeAll('contextual_entity_info', [$entity]);

    // If an entity is a toolkit entity.
    if ($this->isEntityToolkit($entity)) {
      // If entity has URL ID field.
      if ($url_id = $entity->getUrlId()) {
        // Include URL ID in extra data on a given entity.
        $info['data']['urlId'] = $url_id;
      }
    }

    return $info;
  }

  /**
   * Helper function to determine if an entity is a toolkit entity.
   *
   * A toolkit entity implements the ContentEntityInterface class.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity object.
   *
   * @return bool
   *   TRUE if the entity is toolkit, otherwise FALSE.
   */
  public function isEntityToolkit(EntityInterface $entity) {
    return $entity instanceof ContentEntityInterface;
  }

  /**
   * Helper function to extract all toolkit entity references for a given entity.
   *
   * This looks at config entity reference fields for references that contain
   * entities that implement ContentEntityInterface.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity object.
   *
   * @return array
   *   An array of entities, keyed by UUID.
   */
  public function getEntityReferences(EntityInterface $entity) {
    $references = [];

    // Iterate the entity fields.
    foreach ($entity->getFieldDefinitions() as $field_name => $field) {
      // Check for a config field.
      // TODO: Should we limit to config fields only?
      if (get_class($field) == 'Drupal\field\Entity\FieldConfig') {
        // Check if the field is an entity reference.
        if ($field->getType() == 'entity_reference') {
          // Check if a reference is available.
          if ($reference = $entity->get($field_name)->entity) {
            // Check if the parent is a toolkit entity.
            // TODO: We should see if we can determine this before loading the
            // entity based on the field settings.
            if ($this->isEntityToolkit($reference)) {
              // Store the reference.
              $references[$reference->uuid()] = $reference;
            }
          }
        }
      }
    }

    return $references;
  }

}
