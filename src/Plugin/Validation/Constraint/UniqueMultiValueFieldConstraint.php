<?php

namespace Drupal\toolkit\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint for multi-value unique fields.
 *
 * @Constraint(
 *   id = "UniqueMultiValueField",
 *   label = @Translation("Unique multi-value field", context = "Validation")
 * )
 */
class UniqueMultiValueFieldConstraint extends Constraint {

  public $existingEntityMessage = 'An existing @entity_type is currently using at least one of the @field_name values.';

  public $duplicateInputMessage = 'The @field_name field must not contain duplicate values.';

}
