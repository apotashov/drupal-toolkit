<?php

namespace Drupal\oly;

/**
 * Defines an interface for the time service.
 */
interface TimeInterface {

  /**
   * Returns the time service.
   *
   * @return \Drupal\Component\Datetime\TimeInterface
   *   The time service.
   */
  public function getTime();

  /**
   * Returns the timestamp for the current request.
   *
   * @return int
   *   A Unix timestamp.
   *
   * @see \Drupal\Component\Datetime\TimeInterface::getRequestTime()
   */
  public function getRequestTime();

  /**
   * Returns the timestamp for the current request with microsecond precision.
   *
   * @return float
   *   A Unix timestamp with a fractional portion.
   *
   * @see \Drupal\Component\Datetime\TimeInterface::getRequestMicroTime()
   */
  public function getRequestMicroTime();

  /**
   * Returns the current system time as an integer.
   *
   * @return int
   *   A Unix timestamp.
   *
   * @see \Drupal\Component\Datetime\TimeInterface::getCurrentTime()
   */
  public function getCurrentTime();

  /**
   * Returns the current system time with microsecond precision.
   *
   * @return float
   *   A Unix timestamp with a fractional portion.
   *
   * @see \Drupal\Component\Datetime\TimeInterface::getCurrentMicroTime()
   */
  public function getCurrentMicroTime();

  /**
   * Get the current request datetime converted to UTC.
   *
   * @return string
   *   The current request datetime converted to UTC.
   */
  public function getRequestDatetimeUtc();

  /**
   * Convert a UTC datetime string to a timestamp in the local timezone.
   *
   * @param string $datetime
   *   A datetime string.
   *
   * @return int
   *   A timestamp.
   */
  public function convertUtcDatetimeToLocalTimestamp(string $datetime);

  /**
   * Convert a datetime timezone.
   *
   * @param string $datetime
   *   A datetime string.
   * @param string $tz_from
   *   The timezone to convert from. Defaults to the site's current timezone.
   * @param string $tz_to
   *   The timezone to convert to. Defaults to the site's current timezone.
   *
   * @return \DateTime|null
   *   A DateTime object, or NULL if a timezone could not be found.
   */
  public function convertDatetimeTimezone(string $datetime, string $tz_from = NULL, string $tz_to = NULL);

}
