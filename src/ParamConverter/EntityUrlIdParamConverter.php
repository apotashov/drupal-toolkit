<?php

namespace Drupal\toolkit\ParamConverter;

use Symfony\Component\Routing\Route;

/**
 * Parameter converter for converting a URL ID an entity.
 *
 * Extension of EntityFieldValueParamConverter but forces the field to be
 * the URL ID.
 *
 * @see EntityUrlIdTrait
 */
class EntityUrlIdParamConverter extends EntityFieldValueParamConverter {

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return ($definition['type'] == 'entity_url_id');
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    $definition['field_name'] = 'url_id';
    return parent::convert($value, $definition, $name, $defaults);
  }

}
