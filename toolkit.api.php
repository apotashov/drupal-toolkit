<?php

/**
 * @file
 * API documentation for the toolkit module.
 */

use Drupal\Core\Entity\EntityInterface;

/**
 * Implements hook_contextual_entity_info().
 *
 * Provide extra information about a given contextual entity. The data collected
 * in this hook will be added to the `data` array of each entity in the JS
 * settings.
 *
 * @see Drupal\toolkit\ContextualEntity
 *
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   An entity object.
 *
 * @return array
 *   Associative array with entity extra information.
 */
function hook_contextual_entity_info(EntityInterface $entity) {
  if ($entity->getEntityTypeId() == 'team') {
    return [
      'teamId' => $entity->team_id->value,
    ];
  }
}
