<?php

namespace Drupal\toolkit;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\Query\Sql\Query;

/**
 * The SQL storage entity query class override.
 *
 * This is injected via \Drupal\toolkit\EntityQueryFactorySql.
 *
 * This override is needed to attempt to bypass several core issues with entity
 * queries. 1) It seems that even if the sorting field is a base, core is still
 * joining the base table (again) and sorting based on the join, which is
 * resulting in terrible performance. 2) Complex queries are using an expression
 * and join to sort, which also results in terrible performance. These
 * expressions are needed if the entity is revisionable, but should not be used
 * if that is not the case. 3) Filters on base tables are also using joins.
 *
 * @see https://www.drupal.org/project/drupal/issues/3006315
 */
class EntityQuerySql extends Query {

  /**
   * {@inheritdoc}
   */
  protected function isSimpleQuery() {
    return !$this->entityType->isRevisionable() || parent::isSimpleQuery();
  }

  /**
   * {@inheritdoc}
   */
  public function getTables(SelectInterface $sql_query) {
    return new EntityQueryTables($sql_query);
  }

}
