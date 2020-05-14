<?php

namespace Drupal\toolkit\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\Cache;

/**
 * Provides an entity type canonical condition.
 *
 * This provides a condition if entities of a certain type are being viewed on
 * their canonical route.
 *
 * @Condition(
 *   id = "entity_type_canonical",
 *   label = @Translation("Entity type canonical"),
 * )
 */
class EntityTypeCanonical extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a EntityTypeCanonical condition plugin.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param array $configuration
   *   The plugin configuration.
   * @param mixed $plugin_id
   *   The plugin ID.
   * @param array $plugin_definition
   *   The plugin definition.
   */
  public function __construct(RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager, array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $configuration,
      $plugin_id,
      $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['types' => []] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Generate a list of content entity type options.
    $options = [];
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type => $definition) {
      if ($definition->getGroup() == 'content') {
        $options[$entity_type] = (string) $definition->getLabel();
      }
    }
    asort($options);

    $form['types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Entity types'),
      '#default_value' => $this->configuration['types'],
      '#options' => $options,
      '#description' => $this->t('Specify which entity types you want this block to appear on. It will only show on the canonical path.'),
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['types'] = array_values(array_filter($form_state->getValue('types')));
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $types = implode(', ', $this->configuration['types']);
    return $this->t('Return true on the following entity canonical routes: @types', ['@types' => $types]);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    // Get the route name.
    $route_name = $this->routeMatch->getRouteName();

    // Iterate the active entity types.
    foreach ($this->configuration['types'] as $type) {
      // Check for a canonical match.
      if ($route_name == "entity.{$type}.canonical") {
        return $this->isNegated() ? FALSE : TRUE;
      }
    }

    return $this->isNegated() ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

}
