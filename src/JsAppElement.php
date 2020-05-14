<?php

namespace Drupal\toolkit;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\toolkit\Entity\SponsorInterface;
use Drupal\Component\Serialization\Json;

/**
 * Build render array for a JS app element.
 */
class JsAppElement {

  /**
   * The ID attribute of render element.
   *
   * @var string|null
   */
  protected $id = NULL;

  /**
   * The array with class attributes of render element.
   *
   * @var array
   */
  protected $classes = [];

  /**
   * Array with the settings for render element.
   *
   * @var array
   */
  protected $settings = [];

  /**
   * The weight of render element.
   *
   * @var int|null
   */
  protected $weight = NULL;

  /**
   * The json to store in the data-json attribute.
   *
   * @var string
   */
  protected $json;

  /**
   * Constructs a new JsAppElement object.
   *
   * @param string|null $id
   *   (optional) The ID attribute of render element.
   * @param array $classes
   *   (optional) The array of class attributes of render element.
   */
  public function __construct(string $id = NULL, array $classes = []) {
    $this->setId($id);
    $this->setClasses($classes);
  }

  /**
   * Sets an ID attribute of render element.
   *
   * @param string|null $id
   *   (optional) The ID attribute of render element.
   */
  public function setId(string $id = NULL) {
    $this->id = $id;
  }

  /**
   * Sets array of class attributes of render element.
   *
   * @param array $classes
   *   The class attributes of render element.
   */
  public function setClasses(array $classes) {
    $this->classes = $classes;
  }

  /**
   * Sets class attribute of render element.
   *
   * @param string $class
   *   The class attribute of render element.
   */
  public function setClass(string $class) {
    $this->classes[] = $class;
  }

  /**
   * Sets a particular value in the settings.
   *
   * @param string $key
   *   The key of JsAppElement::$settings to set.
   * @param mixed $value
   *   The value to set for the provided key
   */
  public function setSetting(string $key, $value) {
    $this->settings[$key] = $value;
  }

  /**
   * Sets a particular value in the settings.
   *
   * @param array $data
   *   The settings array.
   */
  public function setSettings(array $data) {
    foreach ($data as $key => $value) {
      $this->setSetting($key, $value);
    }
  }

  /**
   * Sets a title in the settings.
   *
   * @param string $title
   */
  public function setTitle(string $title) {
    $this->setSetting('title', $title);
  }

  /**
   * Set the weight of render element.
   *
   * @param int $weight
   *   The weight of render element.
   */
  public function setWeight(int $weight) {
    $this->weight = $weight;
  }

  /**
   * Set the json to store in the data-json attribute.
   *
   * @param array $json
   *   The json array.
   */
  public function setJsonData(array $json) {
    $this->json = Json::encode($json);
  }

  /**
   * Build render element array.
   *
   * @return array
   *   Render array for vue app element.
   */
  public function build() {
    $build = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => ''
    ];

    // Add id.
    if (!empty($this->id)) {
      $build['#attributes']['id'] = $this->id;
    }

    // Add class.
    if (!empty($this->classes)) {
      $build['#attributes']['class'] = array_unique($this->classes);
    }

    // Add data-settings attribute.
    if (!empty($this->settings)) {
      $build['#attributes']['data-settings'] = Json::encode($this->settings);
    }

    // Add data-json attribute.
    if (!empty($this->json)) {
      // Set data-json attribute.
      $build['#attributes']['data-json'] = $this->json;
    }

    // If weight should be set.
    if (isset($this->weight)) {
      // Do so.
      $build['#weight'] = $this->weight;
    }

    return $build;
  }

}
