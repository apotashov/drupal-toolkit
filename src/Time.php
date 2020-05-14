<?php

namespace Drupal\toolkit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Provides a service that contains extended time-related functionality.
 */
class Time implements TimeInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $datetimeTime;

  /**
   * Constructs a new OlyTime object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, TimeInterface $datetime_time) {
    $this->configFactory = $config_factory;
    $this->datetimeTime = $datetime_time;
  }

  /**
   * {@inheritdoc}
   */
  public function getTime() {
    return $this->datetimeTime;
  }

  /**
   * {@inheritdoc}
   */
  public function getRequestTime() {
    return $this->datetimeTime->getRequestTime();
  }

  /**
   * {@inheritdoc}
   */
  public function getRequestMicroTime() {
    return $this->datetimeTime->getRequestMicroTime();
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentTime() {
    return $this->datetimeTime->getCurrentTime();
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentMicroTime() {
    return $this->datetimeTime->getCurrentMicroTime();
  }

  /**
   * {@inheritdoc}
   */
  public function getRequestDatetimeUtc() {
    // Get the request time.
    $request_time = $this->getRequestTime();

    // Convert the request timestamp to a datestamp.
    $request_datetime = date(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, $request_time);

    // Convert the datestamp to a converted date.
    $request_date = $this->convertDatetimeTimezone($request_datetime, NULL, 'UTC');

    // Convert back to datetime format.
    return $request_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
  }

  /**
   * {@inheritdoc}
   */
  public function convertUtcDatetimeToLocalTimestamp(string $datetime) {
    // Perform the conversion.
    if ($date = $this->convertDatetimeTimezone($datetime, 'UTC')) {
      return $date->format('U');
    }

    return strtotime($datetime);
  }

  /**
   * {@inheritdoc}
   */
  public function convertDatetimeTimezone(string $datetime, string $tz_from = NULL, string $tz_to = NULL) {
    // Load the system timezone.
    $timezone = $this->configFactory->get('system.date')
      ->get('timezone.default');

    // Set the defaults.
    $tz_from = $tz_from ? $tz_from : $timezone;
    $tz_to = $tz_to ? $tz_to : $timezone;

    // Stop if both timezones aren't present.
    if (!$tz_from || !$tz_to) {
      return NULL;
    }

    // Perform the timezone conversion.
    $date = new \DateTime($datetime, new \DateTimeZone($tz_from));
    $date->setTimezone(new \DateTimeZone($tz_to));

    // Return the date.
    return $date;
  }

}
