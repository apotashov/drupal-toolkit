<?php

namespace Drupal\toolkit\Plugin\Field\FieldFormatter;

use Drupal\toolkit\JsAppElement;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;


/**
 * Generic field formatter for rendering JSON:API markup of referenced entities.
 *
 * @FieldFormatter(
 *   id = "entity_reference_json_api",
 *   label = @Translation("JSON:API"),
 *   description = @Translation("Render the JSON:API markup of the referenced entities in a JS element."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class EntityReferenceJsonApiFormatter extends EntityReferenceFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The JSON API markup generator service.
   *
   * @var \Drupal\toolkit\JsonApiGeneratorInterface
   */
  protected $jsonApiGenerator;

  /**
   * Constructs a new EntityReferenceJsonApiFormatterBase.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\toolkit\JsonApiGeneratorInterface $json_api_generator
   *   The JSON API markup generator service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, JsonApiGeneratorInterface $json_api_generator) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->jsonApiGenerator = $json_api_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('toolkit.jsonapi_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'js_element_class' => NULL,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['js_element_class'] = [
      '#title' => t('The class to attach to the HTML element that contains JSON data'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('js_element_class'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $element_class = $this->getSetting('js_element_class');
    $summary = [];
    $summary[] = $element_class ? t('%class class is used', ['%class' => $element_class]) : t('Default class is used');
    return $summary;
  }

  /**
   * Get the includes to include in the JSON API markup.
   *
   * @return array
   *   An array of includes.
   */
  public static function getIncludes() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Attempt to get the class to attach to html element with post JSON data.
    // If class is not supplied in formatter settings, use field name to provide
    // a default class.
    $json_element_class = $this->getSetting('js_element_class') ?: $this->fieldDefinition->getName();

    // Build default renderable array.
    $vue_element = new JsAppElement(NULL, [$json_element_class]);
    $element[0] = $vue_element->build();

    $entities = [];
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      // Collect referenced entities.
      $entities[] = $entity;
    }

    // If there ara no referenced entities.
    if (empty($entities)) {
      // Stop here.
      return $element;
    }

    $this->jsonApiGenerator->reset();
    // Set the entities for generating JSON API markup.
    $this->jsonApiGenerator->setEntities($entities);

    // Set the includes to include in the JSON API markup.
    $this->jsonApiGenerator->setIncludes(static::getIncludes());

    // Generate JSON API markup for referenced entities and set it as json to
    // for the data-json attribute.
    $vue_element->setJsonData($this->jsonApiGenerator->generate());
    $element[0] = $vue_element->build();

    // Apply the cacheability of service to a render array.
    (new CacheableMetadata())
      ->addCacheableDependency($this->jsonApiGenerator)
      ->applyTo($element[0]);

    return $element;
  }

}
