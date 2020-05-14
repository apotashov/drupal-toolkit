<?php

namespace Drupal\toolkit\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;

/**
 * Skips processing the current row when a source value is equal to value.
 *
 * The skip_row_if_value_is process plugin checks whether a value is the same as
 * a given value. If the value is the same, a MigrateSkipRowException
 * is thrown, otherwise the value is returned.
 *
 * Available configuration keys:
 * - index: The source property to check for.
 * - message: (optional) A message to be logged in the {migrate_message_*} table
 *   for this row. If not set, nothing is logged in the message table.
 *
 * Example:
 *
 * @code
 *  process:
 *    settings:
 *      # Skips this row if type is equal to Event.
 *      plugin: skip_row_if_value_is
 *      value: Event
 *      source: type
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "skip_row_if_value_is"
 * )
 */
class SkipRowIfValueIs extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ($value == $this->configuration['value']) {
      throw new MigrateSkipRowException();
    }
    return $value;
  }

}
