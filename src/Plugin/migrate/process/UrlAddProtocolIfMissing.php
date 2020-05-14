<?php

namespace Drupal\toolkit\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Add protocol to the url if it's not set.
 *
 * @MigrateProcessPlugin(
 *   id = "url_add_protocol_if_missing"
 * )
 *
 * Example:
 *
 * @code
 *  process:
 *    settings:
 *      plugin: url_add_protocol_if_missing
 *      source: url
 * @endcode
 *
 */
class UrlAddProtocolIfMissing extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // If tags are not set return null.
    if (!$value) {
      return NULL;
    }

    // Add protocol if it's not set.
    $scheme = parse_url($value, PHP_URL_SCHEME);
    if (empty($scheme)) {
      // TODO: Make the scheme configurable.
      $value = 'http://' . ltrim($value, '/');
    }

    return $value;
  }

}
