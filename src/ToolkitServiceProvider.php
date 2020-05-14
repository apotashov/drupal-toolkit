<?php

namespace Drupal\toolkit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Replace services with our own.
 */
class ToolkitServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Override entity SQL query.
    $definition = $container->getDefinition('entity.query.sql');
    $definition->setClass('\Drupal\toolkit\EntityQueryFactorySql');
  }

}
