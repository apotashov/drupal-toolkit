<?php

/**
 * @file
 * Snippets useful for settings.php.
 */

/**
 * Acquia specific settings.
 */
if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  // Load the current directory.
  $dirname = dirname(__FILE__);

  // Shared settings for all environments.
  $settings['file_temp_path'] = "/mnt/gfs/{$_ENV['AH_SITE_GROUP']}.{$_ENV['AH_SITE_ENVIRONMENT']}/tmp";

  // List Acquia files to include.
  $acquia_includes = [
    // Acquia database settings.
    '/var/www/site-php/' . $_ENV['AH_SITE_GROUP'] . '/' . $_ENV['AH_SITE_GROUP'] . '-settings.inc',
    // Memcache integration.
    $dirname . '/cloud-memcache-d8.php',
    // General acquia settings.
    $dirname . '/acquia.settings.php',
    // Per environment settings.
    $dirname . '/acquia.' . $_ENV['AH_SITE_ENVIRONMENT'] . '.settings.php',
    // Per site and per environment settings.
    $dirname . '/acquia.' . $_ENV['AH_SITE_GROUP'] . '.' . $_ENV['AH_SITE_ENVIRONMENT'] . '.settings.php',
  ];

  // Load the includes.
  foreach ($acquia_includes as $acquia_include) {
    if (file_exists($acquia_include)) {
      require $acquia_include;
    }
  }
}
