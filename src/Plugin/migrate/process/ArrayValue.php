<?php

namespace Drupal\toolkit\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Handles an array as a single value for a field.
 *
 * This treats an array as a single value, passing that directly in to the
 * field rather than having to use a sub-process.
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "array_value",
 *   handle_multiples = TRUE
 * )
 */
class ArrayValue extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return $value;
  }

}
