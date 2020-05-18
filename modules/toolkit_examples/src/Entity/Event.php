<?php

namespace Drupal\toolkit_examples\Entity;

use Drupal\toolkit\EntityUrlIdTrait;
use Drupal\toolkit\EntityParentTrait;
use Drupal\toolkit\EntityExternalIdTrait;
use Drupal\toolkit\EntityContextualTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines the Event entity.
 *
 * @ContentEntityType(
 *   id = "event",
 *   label = @Translation("Event"),
 *   label_collection = @Translation("Events"),
 *   label_singular = @Translation("event"),
 *   label_plural = @Translation("events"),
 *   label_count = @PluralTranslation(
 *     singular = "@count event",
 *     plural = "@count events"
 *   ),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\toolkit\ContentEntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentDeleteEntityForm",
 *     },
 *     "access" = "Drupal\toolkit\ContentEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\toolkit\ContentEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "event",
 *   admin_permission = "administer event entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "parent" = "location",
 *     "external_id" = "code",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/event/{event}",
 *     "add-form" = "/admin/structure/event/add",
 *     "edit-form" = "/admin/structure/event/{event}/edit",
 *     "delete-form" = "/admin/structure/event/{event}/delete",
 *     "collection" = "/admin/structure/event",
 *   },
 *   field_ui_base_route = "entity.event.collection"
 * )
 */
class Event extends ContentEntityBase implements ContentEntityInterface {

  use EntityUrlIdTrait;
  use EntityParentTrait;
  use EntityExternalIdTrait;
  use EntityContextualTrait;

  /**
   * {@inheritdoc}
   */
  public function getUrlIdPattern() {
    return "[event:name]-[event:code]";
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the event.'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -10,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    // Unique field. Acts as the external ID.
    $fields['code'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Code'))
      ->setDescription(t('The event code.'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->addConstraint('UniqueField')
      ->setRequired(TRUE);

    // References a location entity. Marked as parent field.
    $fields['location'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Location'))
      ->setDescription(t('The location this event belongs to.'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'location')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_label',
        'settings' => [
          'link' => TRUE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields += static::urlIdBaseFieldDefinitions($entity_type);

    return $fields;
  }

}
