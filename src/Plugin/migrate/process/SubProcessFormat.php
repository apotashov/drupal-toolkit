<?php

namespace Drupal\toolkit\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Formats a flat array to be used with a sub-process.
 *
 * Arrays passed in to a sub-process require keys. This takes an array like:
 * [
 *   1,
 *   2,
 *   3
 * ]
 *
 * And converts it to:
 *
 * [
 *   ['id' => 1],
 *   ['id' => 2],
 *   ['id' => 3],
 * ]
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "sub_process_format",
 *   handle_multiples = TRUE
 * )
 */
class SubProcessFormat extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $return = [];
    foreach ($value as $item) {
      $return[] = ['id' => $item];
    }
    return $return;
  }

}
