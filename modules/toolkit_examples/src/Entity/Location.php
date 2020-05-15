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
 * Defines the Location entity.
 *
 * @ContentEntityType(
 *   id = "location",
 *   label = @Translation("Location"),
 *   label_collection = @Translation("Locations"),
 *   label_singular = @Translation("location"),
 *   label_plural = @Translation("locations"),
 *   label_count = @PluralTranslation(
 *     singular = "@count location",
 *     plural = "@count locations"
 *   ),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
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
 *   base_table = "location",
 *   admin_permission = "administer location entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "external_id" = "location_id",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/location/{location}",
 *     "add-form" = "/admin/structure/location/add",
 *     "edit-form" = "/admin/structure/location/{location}/edit",
 *     "delete-form" = "/admin/structure/location/{location}/delete",
 *     "collection" = "/admin/structure/location",
 *   },
 *   field_ui_base_route = "entity.location.collection"
 * )
 */
class Location extends ContentEntityBase implements ContentEntityInterface {

  use EntityUrlIdTrait;
  use EntityExternalIdTrait;
  use EntityContextualTrait;

  /**
   * {@inheritdoc}
   */
  public function getUrlIdPattern() {
    return "[location:name]";
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the location.'))
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
    $fields['location_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Code'))
      ->setDescription(t('The location ID.'))
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

    $fields += static::urlIdBaseFieldDefinitions($entity_type);

    return $fields;
  }

}
