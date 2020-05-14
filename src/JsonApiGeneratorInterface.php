<?php

namespace Drupal\toolkit;

use Drupal\Core\Cache\CacheableDependencyInterface;

/**
 * Defines an interface for service that generates JSON API markup.
 */
interface JsonApiGeneratorInterface extends CacheableDependencyInterface {

  /**
   * Get the entities for generating JSON API markup.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of entities.
   */
  public function getEntities();

  /**
   * Set the entities for generating JSON API markup.
   *
   * @param array $entities
   *   An array of entities.
   */
  public function setEntities(array $entities);

  /**
   * Get links to include in the generated JSON API markup.
   *
   * @return \Drupal\jsonapi\JsonApiResource\Link[]
   *   An associated array of key names and JSON:API Link objects.
   */
  public function getLinks();

  /**
   * Set links to include in the generated JSON API markup.
   *
   * @param \Drupal\jsonapi\JsonApiResource\Link[] $links
   *   An associated array of key names and JSON:API Link objects.
   */
  public function setLinks(array $links);

  /**
   * Get metadata to include in the JSON API markup.
   *
   * @return array
   *   An associative array of metadata.
   */
  public function getMetadata();

  /**
   * Set metadata to include in the JSON API markup.
   *
   * @param array $metaData
   *   An associative array of metadata.
   */
  public function setMetadata(array $metaData);

  /**
   * Get the includes to include in the JSON API markup.
   *
   * @return array
   *   An array of includes.
   */
  public function getIncludes();

  /**
   * Set the includes to include in the JSON API markup.
   *
   * @param array $includes
   *   An array of includes.
   */
  public function setIncludes(array $includes);

  /**
   * Set if the generator is dynamic or not.
   *
   * The generator should be set to dynamic if the entities being provided are
   * dynamically-generated, for example, a list of the most recently updated
   * nodes.
   *
   * @param bool $dynamic
   *   (optional) TRUE if the generator should be dynamic, FALSE otherwise.
   */
  public function setDynamic(bool $dynamic = FALSE);

  /**
   * Check if the generator is dynamic or not.
   *
   * @return bool
   *   TRUE if the generator is dynamic, FALSE otherwise.
   */
  public function isDynamic();

  /**
   * Resets all of the service variables to their defaults (which is empty).
   */
  public function reset();

  /**
   * Generate JSON API markup.
   *
   * @param bool $json_markup
   *   (optional) TRUE if json markup should be generated, FALSE if end up with
   *   an instance of JsonApiDocumentTopLevel.
   *
   * @return \Drupal\jsonapi\JsonApiResource\JsonApiDocumentTopLevel|array|string|int|float|bool|null
   *   The JSON API normalization data.
   */
  public function generate(bool $json_markup = TRUE);

}
