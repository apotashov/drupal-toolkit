<?php

namespace Drupal\toolkit;

use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides a trait for storing the user who last updated the entity.
 */
trait EntityLastUpdatedByTrait {

  /**
   * Provides base field definitions for storing the user to last update.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   An array of base field definitions for the entity type, keyed by field
   *   name.
   */
  public static function lastUpdatedByBaseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['last_updated_by'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Last updated by'))
      ->setDescription(t('The user who last updated the entity.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default');

    return $fields;
  }

  /**
   * Get the user who last updated this entity.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity who last updated the entity.
   */
  public function getLastUpdatedBy() {
    return $this->get('last_updated_by')->entity;
  }

  /**
   * Get the user's ID who last updated this entity.
   *
   * @return int
   *   The user entity ID who last updated the entity.
   */
  public function getLastUpdatedById() {
    return $this->get('last_updated_by')->target_id;
  }

  /**
   * Set the last updated user ID.
   *
   * @param mixed $uid
   *   The user entity ID, or NULL, to use the current user's ID.
   *
   * @return mixed
   *   The called entity.
   */
  public function setLastUpdatedById($uid = NULL) {
    $this->set('last_updated_by', $uid ? $uid : \Drupal::currentUser()->id());
    return $this;
  }

  /**
   * Set the last updated user.
   *
   * @param \Drupal\user\UserInterface|null $account
   *   The user entity, or NULL, to use the current user.
   *
   * @return mixed
   *   The called entity.
   */
  public function setLastUpdatedBy(UserInterface $account = NULL) {
    $this->setLastUpdatedById($account ? $account->id() : NULL);
    return $this;
  }

}
