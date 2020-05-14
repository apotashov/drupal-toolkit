<?php

namespace Drupal\toolkit;

/**
 * Provides a trait for using the UUID in the entity routes.
 *
 * In order to use properly, the UUID needs to be passed in to all url and
 * link functions, rather than the ID.
 */
trait EntityUuidRouteTrait {

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    // Switch the entity ID out for the UUID.
    if (isset($uri_route_parameters[$this->getEntityTypeId()])) {
      $uri_route_parameters[$this->getEntityTypeId()] = $this->uuid();
    }

    return $uri_route_parameters;
  }

}
