<?php

namespace Drupal\toolkit\Logger;

use Psr\Log\LoggerInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Logger\RfcLoggerTrait;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Email serious log entries.
 */
class MailLog implements LoggerInterface {

  use RfcLoggerTrait;

  /**
   * Maximum amount of emails to send in a single request.
   *
   * @var int
   */
  const MAX_EMAILS_PER_REQUEST = 5;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Constructs a new MailLog object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    $sent = &drupal_static(__METHOD__, 0);

    // Stop if we've hit the sent limit per request.
    if ($sent >= self::MAX_EMAILS_PER_REQUEST) {
      return;
    }

    // Get all log levels.
    $levels = RfcLogLevel::getLevels();

    // Skip mail errors to avoid an endless loop.
    // TODO: Find a better way to do this.
    if (!strstr($context['channel'], 'mail')) {
      return;
    }

    // Get the log email address.
    if ($email_address = $this->config->get('toolkit.settings')->get('mail_log_email')) {
      // Check if the level is beyond a warning, or mail was requested.
      if (($level < RfcLogLevel::WARNING) || !empty($context['mail_log'])) {
        // Build message placeholders.
        $placeholders = [];
        foreach ($context as $key => $value) {
          if (!in_array(substr($key, 0, 1), ['@', ':', '%'])) {
            $key = ':' . $key;
          }
          $placeholders[$key] = $value;
        }

        // Build the email param.
        $params = [
          'subject' => strtr('@level was logged in @channel', ['@level' => $levels[$level], '@channel' => $context['channel']]),
          'body' => strtr($message, $placeholders),
        ];

        // Send the email.
        // TODO: Injecting this service seems to cause circular dependency
        // issues with Symfony for some reason.
        \Drupal::service('plugin.manager.mail')
          ->mail('toolkit', 'mail_log', $email_address, 'en', $params);

        // Add to the sent count.
        $sent++;
      }
    }
  }

}
