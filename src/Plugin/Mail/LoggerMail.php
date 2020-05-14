<?php

namespace Drupal\toolkit\Plugin\Mail;

use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Store outbound emails as log entries.
 *
 * @Mail(
 *   id = "logger_mail",
 *   label = @Translation("Logger mailer"),
 *   description = @Translation("Stores the message using the Drupal logger.")
 * )
 */
class LoggerMail implements MailInterface, ContainerFactoryPluginInterface {

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Construct a LoggerMail object.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param mixed $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger_factory) {
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function format(array $message) {
    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function mail(array $message) {
    // Remove the params.
    unset($message['params']);

    // Log the mail.
    $this->loggerFactory
      ->get('mail')
      ->debug("<pre>" . print_r($message, 1) . "</pre>");

    return TRUE;
  }

}
