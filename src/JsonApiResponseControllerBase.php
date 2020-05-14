<?php

namespace Drupal\toolkit;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\jsonapi\ResourceResponse;

/**
 * Base controller class for delivering custom JSON:API resources.
 */
abstract class JsonApiResponseControllerBase extends ControllerBase {

  /**
   * The JSON API markup generator service.
   *
   * @var \Drupal\toolkit\JsonApiGeneratorInterface
   */
  protected $jsonApiGenerator;

  /**
   * Constructs a new CustomJsonApiResponseControllerBase object.
   *
   * @param \Drupal\toolkit\JsonApiGeneratorInterface $json_api_generator
   *   The JSON API markup generator service.
   */
  public function __construct(JsonApiGeneratorInterface $json_api_generator) {
    $this->jsonApiGenerator = $json_api_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('toolkit.jsonapi_generator')
    );
  }

  /**
   * Get the entities to return in the response.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of loaded entities.
   */
  abstract public function getEntities();

  /**
   * Get the cache contexts for the request.
   *
   * @return array
   *   An array of cache context strings.
   */
  public function getCacheContexts() {
    return ['url.path'];
  }

  /**
   * Get the cache tags for the request.
   *
   * All cache tags for the entities and entity type will automatically be
   * included. This function is used for additional tags.
   *
   * @return array
   *   An array of cache tag strings.
   */
  public function getCacheTags() {
    return [];
  }

  /**
   * Get links to return in the response.
   *
   * @return \Drupal\jsonapi\JsonApiResource\Link[]
   *   An associated array of key names and JSON:API Link objects.
   */
  public function getLinks() {
    return [];
  }

  /**
   * Get additional response headers.
   *
   * @return array
   *   An array of headers, keyed by header name.
   */
  public function getHeaders() {
    return [];
  }

  /**
   * Get metadata to include in the response.
   *
   * @return array
   *   An associative array of metadata.
   */
  public function getMetadata() {
    return [];
  }

  /**
   * Get the includes for the response.
   *
   * @return array
   *   An array of includes.
   */
  public function getIncludes() {
    return [];
  }

  /**
   * Deliver entities in a JSON API response.
   *
   * @return \Drupal\jsonapi\ResourceResponse
   *   A ResourceResponse reponse object.
   */
  public function response() {
    $this->jsonApiGenerator->reset();
    $this->jsonApiGenerator->setEntities($this->getEntities());
    $this->jsonApiGenerator->setLinks($this->getLinks());
    $this->jsonApiGenerator->setMetadata($this->getMetadata());
    $this->jsonApiGenerator->setIncludes($this->getIncludes());

    // Build a return the response.
    $response = new ResourceResponse(
      $this->jsonApiGenerator->generate(FALSE),
      200,
      $this->getHeaders()
    );

    // Add the response cacheability.
    $response->addCacheableDependency(
      $this->jsonApiGenerator
    );
    $response->addCacheableDependency((new CacheableMetadata())
      ->addCacheContexts($this->getCacheContexts())
      ->addCacheTags($this->getCacheTags()));

    return $response;
  }

}
