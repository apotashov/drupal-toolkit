<?php

namespace Drupal\toolkit;

use Drupal\Core\Entity\Query\Sql\QueryFactory;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Factory class creating entity query objects for the SQL backend.
 */
class EntityQueryFactorySql extends QueryFactory {

  /**
   * {@inheritdoc}
   */
  public function get(EntityTypeInterface $entity_type, $conjunction) {
    // Use our class override.
    return new EntityQuerySql($entity_type, $conjunction, $this->connection, $this->namespaces);
  }

}
