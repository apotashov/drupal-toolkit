<?php

namespace Drupal\toolkit\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if an entity reference field has a unique value.
 *
 * @Constraint(
 *   id = "UniqueEntityReferenceField",
 *   label = @Translation("Unique entity reference field constraint", context = "Validation"),
 * )
 */
class UniqueEntityReferenceFieldConstraint extends Constraint {

  public $message = 'A @entity_type with @field_name %value already exists.';

}
