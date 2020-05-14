<?php

namespace Drupal\toolkit;

use Drupal\Core\Cache\CacheableDependencyTrait;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\jsonapi\JsonApiResource\JsonApiDocumentTopLevel;
use Drupal\jsonapi\JsonApiResource\LinkCollection;
use Drupal\jsonapi\JsonApiResource\NullIncludedData;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\jsonapi\JsonApiResource\ResourceObjectData;
use Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface;
use Drupal\jsonapi\IncludeResolver;
use Drupal\jsonapi\Serializer\Serializer;

/**
 * JSON API markup generator service.
 */
class JsonApiGenerator implements JsonApiGeneratorInterface {

  use CacheableDependencyTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The JSON:API resource type repository.
   *
   * @var \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface
   */
  protected $resourceTypeRepository;

  /**
   * The JSON:API include resolver.
   *
   * @var \Drupal\jsonapi\IncludeResolver
   */
  protected $includeResolver;

  /**
   * The decorated JSON:API serializer service.
   *
   * @var \Drupal\jsonapi\Serializer\Serializer
   */
  protected $serializer;

  /**
   * An array of entities.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  private $entities = [];

  /**
   * An associated array of key names and JSON:API Link objects.
   *
   * @var \Drupal\jsonapi\JsonApiResource\Link[]
   */
  private $links = [];

  /**
   * An associative array of metadata.
   *
   * @var array
   */
  private $metaData = [];

  /**
   * An array of includes.
   *
   * @var array
   */
  private $includes = [];

  /**
   * Boolean to indicate if the generator is dynamic or not.
   *
   * @var bool
   */
  protected $dynamic = FALSE;

  /**
   * Constructs a new JsonApiGenerator object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ResourceTypeRepositoryInterface $jsonapi_resource_type_repository, IncludeResolver $jsonapi_include_resolver, Serializer $jsonapi_serializer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->resourceTypeRepository = $jsonapi_resource_type_repository;
    $this->includeResolver = $jsonapi_include_resolver;
    $this->serializer = $jsonapi_serializer;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntities() {
    return $this->entities;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntities(array $entities) {
    $this->entities = $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getLinks() {
    return $this->links;
  }

  /**
   * {@inheritdoc}
   */
  public function setLinks(array $links) {
    $this->links = $links;
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata() {
    return $this->metaData;
  }

  /**
   * {@inheritdoc}
   */
  public function setMetadata(array $metaData) {
    $this->metaData = $metaData;
  }

  /**
   * {@inheritdoc}
   */
  public function getIncludes() {
    return $this->includes;
  }

  /**
   * {@inheritdoc}
   */
  public function setIncludes(array $includes) {
    $this->includes = $includes;
  }

  /**
   * {@inheritdoc}
   */
  public function setDynamic(bool $dynamic = FALSE) {
    $this->dynamic = $dynamic;
  }

  /**
   * {@inheritdoc}
   */
  public function isDynamic() {
    return (bool) $this->dynamic;
  }

  /**
   * Resets all of the variables back to their defaults (which is empty).
   */
  public function reset() {
    $this->setEntities([]);
    $this->setLinks([]);
    $this->setMetadata([]);
    $this->setIncludes([]);
    $this->setDynamic();
    $this->setCacheability(new CacheableMetadata());
  }

  /**
   * {@inheritdoc}
   */
  public function generate(bool $json_markup = TRUE) {
    $collection_data = [];

    // Load all entities to be returned.
    $entities = $this->getEntities();

    // Initialize the response cacheability.
    $cacheability = (new CacheableMetadata())
      ->addCacheContexts($this->getCacheContexts())
      ->addCacheTags($this->getCacheTags());

    // Iterate the entities.
    foreach ($entities as $entity) {
      // If list of entities supplied is dynamic.
      if ($this->isDynamic()) {
        // Add the cache list tags for the entity type, because after adding or
        // editing any entity this list might change.
        $cacheability->addCacheTags($this->entityTypeManager
          ->getDefinition($entity->getEntityTypeId())
          ->getListCacheTags());
      }

      // Add the cache tags for the individual entity.
      $cacheability->addCacheTags($entity->getCacheTags());

      // Check entity access.
      $access = $entity->access('view', NULL, TRUE);

      // Add entity access to the cacheability.
      $cacheability->addCacheableDependency($access);

      // Check if access was granted.
      if ($access->isAllowed()) {
        // Load the JSON API resource type for this entity.
        $resource_type = $this->resourceTypeRepository->get($entity->getEntityTypeId(), $entity->bundle());

        // Create a resource object using the entity.
        $collection_data[$entity->uuid()] = ResourceObject::createFromEntity($resource_type, $entity);
      }
    }

    // Convert the data in to resource object data.
    $data = new ResourceObjectData($collection_data);

    // Generate the links.
    $links = new LinkCollection($this->getLinks());

    // Gather the metadata.
    $metadata = $this->getMetadata();

    // Build the includes.
    $includes = !$this->getIncludes() ? new NullIncludedData() : $this->includeResolver->resolve($data, implode(',', $this->getIncludes()));

    // Iterate the includes.
    foreach ($includes as $include) {
      // Add each include as a cache dependency.
      $cacheability->addCacheableDependency($include);
    }

    // Set cacheability.
    $this->setCacheability($cacheability);

    // Instantiates a JsonApiDocumentTopLevel object.
    $jsonapi_doc_object = new JsonApiDocumentTopLevel($data, $includes, $links, $metadata);

    // If we need to deliver JsonApiDocumentTopLevel object.
    if ($json_markup === FALSE) {
      // Do so.
      return $jsonapi_doc_object;
    }

    // Normalizes an object into a set of arrays/scalars.
    $normalize = $this->serializer
      ->normalize(
        $jsonapi_doc_object,
        'api_json',
        [
          'resource_type' => NULL,
          'account' => NULL,
        ]
      );

    // Gets the decorated normalization.
    return $normalize->getNormalization();
  }

}
