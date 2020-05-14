<?php
/**
 * @file
 *   Store all utility functions.
 */

use Drupal\Core\Entity\EntityInterface;

/**
 * Update config object by the given module and config object name.
 *
 * @param string $module
 *   The module name.
 * @param string $name
 *   The configuration name.
 * @param string $config_dir
 *   Optional. The module config directory name.
 *
 * @return bool
 *   TRUE if configuration data to the storage is written, FALSE otherwise.
 */
function module_update_config(string $module, string $name, string $config_dir = 'install') {
  // If given module doesn't exist.
  if (!\Drupal::moduleHandler()->moduleExists($module)) {
    // Stop here.
    return FALSE;
  }

  $path = drupal_get_path('module', $module) . "/config/{$config_dir}";
  $source = new FileStorage($path);
  $active_storage = \Drupal::service('config.storage');
  return $active_storage->write($name, $source->read($name));
}

/**
 * Revert/import a feature module.
 *
 * @param string $feature
 *   A feature module name.
 */
function revert_feature(string $feature) {
  // There seems to be no better way to do this.
  module_load_include('inc', 'features','drush/features.drush8');
  drush_set_context('DRUSH_AFFIRMATIVE', TRUE);
  drush_features_import($feature);
}

/**
 * Install an entity base field.
 *
 * @param string $entity_type_id
 *   The entity type ID.
 * @param string $field_name
 *   The field name to install.
 */
function entity_base_field_install(string $entity_type_id, string $field_name) {
  $manager = \Drupal::entityDefinitionUpdateManager();
  $entity_type = $manager->getEntityType($entity_type_id);
  $fields = $entity_type->getOriginalClass()::baseFieldDefinitions($entity_type);
  $manager->installFieldStorageDefinition($field_name, $entity_type_id, $entity_type_id, $fields[$field_name]);
}

/**
 * Uninstall an entity base field.
 *
 * @param string $entity_type_id
 *   The entity type ID.
 * @param string $field_name
 *   The field name to uninstall.
 */
function entity_base_field_uninstall(string $entity_type_id, string $field_name) {
  $manager = \Drupal::entityDefinitionUpdateManager();
  $definition = $manager->getFieldStorageDefinition($field_name, $entity_type_id);
  $manager->uninstallFieldStorageDefinition($definition);
}

/**
 * Helper function to get the URL of a file inside an entity field.
 *
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity to extract the file from.
 * @param string $field_name
 *   The name of the field that stores the file, or NULL, if a file is being
 *   passed in.
 * @param bool $absolute
 *   TRUE to return an absolute URL, otherwise FALSE for a relative URL.
 *
 * @return string|null
 *   An file URL, if the file exists, otherwise NULL.
 */
function entity_get_file_url(EntityInterface $entity, string $field_name = NULL, bool $absolute = TRUE) {
  global $base_url;

  $url = NULL;

  // Load the file from the field, if needed.
  $file = $field_name ? $entity->{$field_name}->entity : $entity;
  if (!$file) {
    return NULL;
  }

  // Get the file uri.
  $uri = $file->getFileUri();

  // Generate the URL.
  $url = file_create_url($uri);

  // Check if the URL should not be returned as absolute.
  if (!$absolute) {
    // Get proxy domain.
    $proxy_domain = toolkit_get_image_proxy_domain();

    // If file url has proxy domain.
    if ($proxy_domain && strpos($url, $proxy_domain) !== FALSE) {
      // Return a relative path without proxy domain.
      $url = str_replace($proxy_domain, '', $url);
    }
    else {
      // Return a relative path without base URL of the Drupal.
      $url = str_replace($base_url, '', $url);
    }
  }

  return $url;
}

/**
 * Check if an entity is using a given trait.
 *
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity to check.
 * @param string $trait
 *   The trait class.
 *
 *
 * @return boolean
 *   TRUE if the entity is using the given trait, otherwise FALSE.
 */
function entity_uses_trait(EntityInterface $entity, string $trait) {
  $traits = class_uses($entity);
  return isset($traits[$trait]);
}

/**
 * Check if an entity is publishable.
 *
 * This is determined if it's using Drupal\Core\Entity\EntityPublishedTrait.
 *
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity to check.
 *
 * @return boolean
 *   TRUE if the entity is publishable, otherwise FALSE.
 */
function entity_is_publishable(EntityInterface $entity) {
  return entity_uses_trait($entity, 'Drupal\Core\Entity\EntityPublishedTrait');
}

/**
 * Check if an entity has a live change during the save process.
 *
 * This can be used to determine if a given entity, during the save process,
 * such as via an entity update hook, is experiencing a live change. A live
 * change would be a change that the published site would see. For example,
 * a published entity that remains published, a published entity that becomes
 * unpublished, an unpublished entity that become published, a change in
 * moderation that results in either of the above scenarios, etc.
 *
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity to check.
 * @return boolean
 *   TRUE if the entity has encountered a live change, otherwise FALSE.
 */
function entity_has_live_change(EntityInterface $entity) {
  // Check if the entity is publishable.
  if (entity_is_publishable($entity)) {
    if ($entity->isPublished()) {
      // Published entities always are live changes.
      return TRUE;
    }
  }
  else {
    // All changes are considered live.
    return TRUE;
  }

  // Determine if the previous version was published.
  $original_published = !empty($entity->original) ? $entity->original->{$published_field}->value : NULL;

  // Check if the original was published.
  if ($original_published) {
    // If entity support translation.
    if ($entity_type->hasKey('langcode')) {
      // Check if the language differs.
      if ($entity->langcode->value != $entity->original->langcode->value) {
        // Since this operation is a translation, disregard the original status.
        $original_published = NULL;
      }
    }
  }

  // Check if moderation is enabled.
  if (\Drupal::hasService('content_moderation.moderation_information')) {
    // Load the moderation service.
    $moderation_info = \Drupal::service('content_moderation.moderation_information');

    // Check if the entity is being moderated.
    $moderated = $moderation_info->isModeratedEntity($entity);
  }
  else {
    $moderated = FALSE;
  }

  // Check if the entity is not being moderated.
  if (!$moderated) {
    // Index if the original version was published.
    if ($original_published) {
      return TRUE;
    }

    // Don't index unpublished entities.
    return FALSE;
  }
  else {
    // Check if this is the live revision.
    if ($moderation_info->isLiveRevision($entity)) {
      return TRUE;
    }

    // Check if the original version of the entity was published.
    if ($original_published) {
      // Check if this unpublished revision is the current state.
      if ($moderation_info->isLatestRevision($entity) && $entity->isDefaultRevision()) {
        return TRUE;
      }
    }

    return FALSE;
  }
}
