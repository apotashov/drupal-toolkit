<?php

namespace Drupal\toolkit\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Downloads a remote image and converts to a file entity.
 *
 * The entity ID is returned.
 *
 * Available configuration keys:
 * - source:
 *    - The image URL.
 *    - The directory where the image should be saved.
 * - rename: (optional) TRUE if a new file should be created if one already
 *   exists at the destination. Defaults to FALSE.
 * - required: (optional) TRUE if the image is required to download
 *   successfully, otherwise FALSE. Defaults to TRUE.
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "image"
 * )
 */
class Image extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Split the URL.
    $parts = explode('/', $value[0]);

    // Determine the file name.
    $filename = end($parts);

    // Prepare the directory.
    $directory = $value[1];
    file_prepare_directory($directory, FILE_CREATE_DIRECTORY);

    // Generate a destination.
    $destination = $directory . '/' . $filename;

    // Determine the operation.
    $operation = empty($this->configuration['rename']) ? FILE_EXISTS_REPLACE : FILE_EXISTS_RENAME;

    // Attempt to download the file.
    if ($file_content = file_get_contents($value[0])) {
      if ($file = file_save_data($file_content, $destination, $operation)) {
        // Return the file ID.
        return $file->id();
      }
    }

    // Generate an error message.
    $error_message = "Could not download image '{$value[0]}'";

    // Check if the image is required.
    if (!isset($this->configuration['required']) || $this->configuration['required']) {
      // Fail this import row.
      throw new MigrateException($error_message);
    }
    else {
      // Log the error.
      $migrate_executable->saveMessage($error_message, MigrationInterface::MESSAGE_ERROR);
    }
  }

}
