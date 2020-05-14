<?php

namespace Drupal\toolkit\Plugin\jsonapi\FieldEnhancer;

use Drupal\jsonapi_extras\Plugin\ResourceFieldEnhancerBase;
use Shaper\Util\Context;
use Drupal\Core\Url;

/**
 * Include a URL along with the URI.
 *
 * @ResourceFieldEnhancer(
 *   id = "link_url",
 *   label = @Translation("Link with URL"),
 *   description = @Translation("Include a URL along with the URI.")
 * )
 */
class LinkUrlEnhancer extends ResourceFieldEnhancerBase {

  /**
   * {@inheritdoc}
   */
  protected function doUndoTransform($value, Context $context) {
    if (isset($value['uri'])) {
      $value['url'] = Url::fromUri($value['uri'])->setAbsolute()->toString();
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
      'type' => 'object',
    ];
  }

}
