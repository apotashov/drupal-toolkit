<?php

namespace Drupal\toolkit_examples\Entity;

use Drupal\toolkit\EntityLastUpdatedByTrait;
use Drupal\toolkit\EntityParentTrait;
use Drupal\toolkit\EntityCreatedTrait;
use Drupal\toolkit\EntityPublishedDateTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogEntityTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the Article entity.
 *
 * @ContentEntityType(
 *   id = "article",
 *   label = @Translation("Article"),
 *   label_collection = @Translation("Articles"),
 *   label_singular = @Translation("article"),
 *   label_plural = @Translation("articles"),
 *   label_count = @PluralTranslation(
 *     singular = "@count article",
 *     plural = "@count articles"
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\toolkit\ContentEntityRevisionStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\toolkit\Form\RevisionableContentEntityForm",
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentDeleteEntityForm",
 *     },
 *     "access" = "Drupal\toolkit\ContentEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\toolkit\ContentEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "article",
 *   admin_permission = "administer article entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "parent" = "event",
 *     "revision" = "vid",
 *     "published" = "status",
 *     "langcode" = "langcode",
 *     "owner" = "user_id",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/article/{article}",
 *     "add-form" = "/admin/content/article/add",
 *     "edit-form" = "/admin/content/article/{article}/edit",
 *     "delete-form" = "/admin/content/article/{article}/delete",
 *     "collection" = "/admin/content/article",
 *     "version-history" = "/article/{article}/revisions",
 *     "revision" = "/article/{article}/revisions/{article_revision}/view",
 *     "revision_revert" = "/article/{article}/revisions/{article_revision}/revert",
 *     "revision_delete" = "/article/{article}/revisions/{article_revision}/delete",
 *   },
 *   field_ui_base_route = "entity.article.collection",
 * )
 */
class Article extends ContentEntityBase implements ContentEntityInterface, EntityPublishedInterface, RevisionLogInterface, EntityOwnerInterface {

  use EntityPublishedTrait;
  use RevisionLogEntityTrait;
  use EntityParentTrait;
  use EntityOwnerTrait;
  use EntityLastUpdatedByTrait;
  use EntityCreatedTrait;
  use EntityChangedTrait;
  use EntityPublishedDateTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the article.'))
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
      ->setRequired(TRUE)
      ->setRevisionable(TRUE);

    // References an event entity. Marked as parent field.
    $fields['event'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Event'))
      ->setDescription(t('The event this article belongs to.'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'event')
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
      ->setDisplayConfigurable('view', TRUE)
      ->setRevisionable(TRUE);

    $fields['body'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Body'))
      ->setDescription(t('The article body.'))
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => 4,
        'settings' => [
          'rows' => 8,
        ],
      ])
      ->setDisplayOptions('view', [
        'type' => 'string',
        'weight' => 4,
        'label' => 'hidden',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE)
      ->setRevisionable(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields += static::revisionLogBaseFieldDefinitions($entity_type);
    $fields += static::publishedBaseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);
    $fields += static::lastUpdatedByBaseFieldDefinitions($entity_type);
    $fields += static::publishedDateBaseFieldDefinitions($entity_type);

    $fields['user_id']->setRevisionable(TRUE);
    $fields['last_updated_by']->setRevisionable(TRUE);

    $fields['status']->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'settings' => [
        'display_label' => TRUE,
      ],
      'weight' => 20,
    ])
    ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
