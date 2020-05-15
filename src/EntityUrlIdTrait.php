<?php

namespace Drupal\toolkit;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Provides a trait for generating a URL ID for a given entity.
 *
 * A URL ID is a unique, seo-friendly string that can be used as a route
 * parameter in place of an entity ID which allows for seo-friendly paths
 * without the usage of path auto aliases.
 *
 * As an example, say we have a page that can be filtered by entities of a given
 * type. The page is a video hub, located at /videos. We want to be able to
 * provide URLs for each page filtered by athlete entities. If we use the
 * traditional path alias method, we'd have to generate and somehow maintain
 * N path aliases for each of N athletes; for this, and all pages where we want
 * to filter by athletes (plus other entity types). With a URL ID, we do not
 * need that. Here we use token and pathauto to define a pattern to generate
 * the URL ID; ie, [athlete:name]-[athlete:athlete_id]. It is then stored in
 * a field on each entity. The route can now be built like:
 * /videos/athlete/{athlete} and the entity_field_value ParamConverter can be
 * used to convert the URL ID to an entity.
 */
trait EntityUrlIdTrait {

  /**
   * Return the pattern for the URL ID.
   *
   * @return string
   *   The URL ID pattern. This can include tokens.
   */
  abstract public function getUrlIdPattern();

  /**
   * Provides a URL ID base field definitions for an entity type.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   An array of base field definitions for the entity type, keyed by field
   *   name.
   */
  public static function urlIdBaseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['url_id'] = BaseFieldDefinition::create('string')
      ->addConstraint('UniqueField');

    return $fields;
  }

  /**
   * Generates the URL ID field value.
   *
   * @return mixed
   *   A generated URL ID.
   */
  public function generateUrlId() {
    // Generate URL ID.
    $url_id = \Drupal::token()
      ->replace($this->getUrlIdPattern(), [$this->getEntityTypeId() => $this]);

    // Lowercase the string.
    $url_id = strtolower($url_id);

    // Check if the pathauto alias cleaner service is available.
    if (\Drupal::hasService('pathauto.alias_cleaner')) {
      // Clean the string to be used.
      $url_id = \Drupal::service('pathauto.alias_cleaner')
        ->cleanString($url_id);
    }

    return $url_id;
  }

  /**
   * Return the value of URL ID field.
   *
   * @return mixed
   *   URL ID value, or NULL, if no value is set.
   */
  public function getUrlId() {
    return $this->url_id->value;
  }

  /**
   * Set the URL ID.
   *
   * @param mixed $url_id
   *   The URL ID, or NULL, to generate a new one.
   *
   * @return mixed
   *   The called entity.
   */
  public function setUrlId($url_id = NULL) {
    $this->set('url_id', $url_id ? $url_id : $this->generateUrlId());
    return $this;
  }

}
