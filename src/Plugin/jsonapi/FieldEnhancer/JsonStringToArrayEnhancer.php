<?php

namespace Drupal\toolkit\Plugin\jsonapi\FieldEnhancer;

use Drupal\Component\Serialization\Json;
use Drupal\jsonapi_extras\Plugin\ResourceFieldEnhancerBase;
use Shaper\Util\Context;

/**
 * Convert JSON string to array.
 *
 * @ResourceFieldEnhancer(
 *   id = "json_string_to_array",
 *   label = @Translation("JSON string to array"),
 *   description = @Translation("Convert JSON string to array.")
 * )
 */
class JsonStringToArrayEnhancer extends ResourceFieldEnhancerBase {

  /**
   * {@inheritdoc}
   */
  protected function doUndoTransform($value, Context $context) {
    if (isset($value)) {
      $value = Json::decode($value);
    }
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  protected function doTransform($value, Context $context) {
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOutputJsonSchema() {
    return [
      'oneOf' => [
        ['type' => 'array'],
        ['type' => 'null'],
      ],
    ];
  }

}
