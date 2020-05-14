<?php

namespace Drupal\toolkit\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Constraint validator for unique multi-value fields.
 *
 * This prevents a multi-value field from containing the same values within
 * itself and other entities.
 */
class UniqueMultiValueFieldConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a new UniqueMultiValueFieldConstraintValidator instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    // Stop if there are no values.
    if ($items->isEmpty()) {
      return;
    }

    // Extract the values.
    $values = [];
    foreach ($items->getValue() as $value) {
      $values[] = strtolower($value['value']);
    }

    // Get the name of the field we are validating.
    $field_name = $items->getFieldDefinition()->getName();

    // Determine if duplicates exist within this entity.
    if (count($values) != count(array_unique($values))) {
      // Add a violation.
      $this->context->addViolation($constraint->duplicateInputMessage, [
        '@field_name' => $items->getFieldDefinition()->getLabel(),
      ]);
    }
    else {
      // Extract the entity being validated.
      $entity = $items->getEntity();
      $entity_type_id = $entity->getEntityTypeId();
      $id_key = $entity->getEntityType()->getKey('id');

      // Build a query to check if there are entities with these values.
      $query = $this->entityTypeManager
        ->getStorage($entity_type_id)
        ->getQuery();

      $entity_id = $entity->id();
      // Using isset() instead of !empty() as 0 and '0' are valid ID values for
      // entity types using string IDs.
      if (isset($entity_id)) {
        $query->condition($id_key, $entity_id, '<>');
      }

      // Query to find an existing entity with these values.
      $value_taken = (bool) $query
        ->condition($field_name, $values, 'IN')
        ->range(0, 1)
        ->count()
        ->execute();

      if ($value_taken) {
        $this->context->addViolation($constraint->existingEntityMessage, [
          '@entity_type' => $entity->getEntityType()->getLowercaseLabel(),
          '@field_name' => $items->getFieldDefinition()->getLabel(),
        ]);
      }
    }
  }

}
