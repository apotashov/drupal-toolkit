<?php

namespace Drupal\toolkit\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Checks if a value exists in an array.
 *
 * Available configuration keys:
 * - needle: The searched value.
 *
 * Examples:
 *
 * @code
 * process:
 *   new_text_field:
 *     plugin: in_array
 *     source:
 *       - foo
 *       - bar
 *     needle: foo
 * @endcode
 *
 * This will set new_text_field to the TRUE if needle is found in the source
 * array, FALSE otherwise.
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "in_array",
 *   handle_multiples = TRUE
 * )
 */
class InArray extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (is_array($value)) {
      $needle = $this->configuration['needle'];
      return in_array($needle, $value);
    }
    else {
      throw new MigrateException(sprintf('%s is not an array', var_export($value, TRUE)));
    }
  }

}
