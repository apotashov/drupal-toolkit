<?php

namespace Drupal\toolkit;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides a trait for storing the date when the entity was first published.
 *
 * This requires the entity also use EntityPublishedTrait.
 */
trait EntityPublishedDateTrait {

  /**
   * Provides base field definitions for storing when the entity was published.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   An array of base field definitions for the entity type, keyed by field
   *   name.
   */
  public static function publishedDateBaseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['published_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Published date'))
      ->setSetting('datetime_type', 'datetime');

    return $fields;
  }

  /**
   * Get the published date.
   *
   * @return string|null
   *   The published date string, or NULL, if one is not set.
   */
  public function getPublishedDate() {
    return $this->published_date->value;
  }

  /**
   * Set the published date.
   *
   * @param string|null $published_date
   *   The published date string, or NULL, to use the current time.
   * @param bool $force
   *   TRUE if the published date should be set even if the entity is not
   *   published, otherwise FALSE. Defaults to FALSE.
   *
   * @return mixed
   *   The called entity.
   */
  public function setPublishedDate(string $published_date = NULL, $force = FALSE) {
    // Check if the entity is published and published date is empty, or we are
    // forcing an update.
    if (($this->isPublished() && !$this->getPublishedDate()) || $force) {
      // Check if we don't have a published date to use.
      if (!$published_date) {
        // Get the current request datetime converted to UTC.
        $published_date = \Drupal::service('toolkit.time')
          ->getRequestDatetimeUtc();
      }

      // Store the datetime.
      $this->set('published_date', $published_date);
    }

    return $this;
  }

}
