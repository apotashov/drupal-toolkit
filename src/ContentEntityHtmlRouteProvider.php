<?php

namespace Drupal\toolkit;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for content entities.
 *
 * @ingroup toolkit
 *
 * @see \Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class ContentEntityHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * Determine if the canonical route should be restricted.
   *
   * If restricted, only users with edit access will be able to view the
   * canonical route. This is useful if you want to grant view access to this
   * entity type, but you don't want user's viewing the canonical route; which
   * can be the case with web services.
   *
   * @var bool
   */
  protected $restrictCanonicalRoute = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    // Build the routes.
    $collection = parent::getRoutes($entity_type);

    // Extract the entity type ID.
    $entity_type_id = $entity_type->id();

    // Check if a settings form route is needed.
    if ($settings_form_route = $this->getSettingsFormRoute($entity_type)) {
      // Add the settings form route.
      $collection->add("{$entity_type_id}.settings", $settings_form_route);
    }

    // Check if the canonical route should be restricted.
    if ($this->restrictCanonicalRoute) {
      // Load the canonical route.
      if ($route = $collection->get("entity.{$entity_type_id}.canonical")) {
        // Change the permission to edit.
        $route->setRequirement('_entity_access', "{$entity_type_id}.update");
      }
    }

    // If entity type have revision support.
    if ($entity_type->isRevisionable()) {
      // Supply revision routes.
      if ($history_route = $this->getHistoryRoute($entity_type)) {
        $collection->add("entity.{$entity_type_id}.version_history", $history_route);
      }

      if ($revision_route = $this->getRevisionRoute($entity_type)) {
        $collection->add("entity.{$entity_type_id}.revision", $revision_route);
      }

      if ($revert_route = $this->getRevisionRevertRoute($entity_type)) {
        $collection->add("entity.{$entity_type_id}.revision_revert", $revert_route);
      }

      if ($delete_route = $this->getRevisionDeleteRoute($entity_type)) {
        $collection->add("entity.{$entity_type_id}.revision_delete", $delete_route);
      }
    }

    return $collection;
  }

  /**
   * Gets the settings form route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getSettingsFormRoute(EntityTypeInterface $entity_type) {
    return NULL;
  }

  /**
   * Gets the version history route for entity type that have revision.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getHistoryRoute(EntityTypeInterface $entity_type) {
    // Gets the entity type admin permission.
    $admin_permission = $entity_type->getAdminPermission();

    if ($entity_type->hasLinkTemplate('version-history')) {
      $route = new Route($entity_type->getLinkTemplate('version-history'));
      $route
        ->setDefaults([
          '_title' => "{$entity_type->getLabel()} revisions",
          '_controller' => '\Drupal\toolkit\Controller\ContentEntityRevisionController::revisionOverview',
          'entity_type_id' => $entity_type->id(),
        ])
        ->setRequirement('_permission', $admin_permission)
        ->setOption('_admin_route', TRUE);

      return $route;
    }
  }

  /**
   * Gets the revision route for entity type that have revision.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getRevisionRoute(EntityTypeInterface $entity_type) {
    // Gets the entity type admin permission.
    $admin_permission = $entity_type->getAdminPermission();

    if ($entity_type->hasLinkTemplate('revision')) {
      $route = new Route($entity_type->getLinkTemplate('revision'));
      $route
        ->setDefaults([
          '_controller' => '\Drupal\toolkit\Controller\ContentEntityRevisionController::revisionShow',
          '_title_callback' => '\Drupal\toolkit\Controller\ContentEntityRevisionController::revisionPageTitle',
          'entity_type_id' => $entity_type->id(),
        ])
        ->setRequirement('_permission', $admin_permission)
        ->setOption('_admin_route', FALSE);

      return $route;
    }
  }

  /**
   * Gets the revision revert route for entity type that have revision.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getRevisionRevertRoute(EntityTypeInterface $entity_type) {
    // Gets the entity type admin permission.
    $admin_permission = $entity_type->getAdminPermission();
    if ($entity_type->hasLinkTemplate('revision_revert')) {
      $route = new Route($entity_type->getLinkTemplate('revision_revert'));
      $route
        ->setDefaults([
          '_form' => '\Drupal\toolkit\Form\RevisionRevertForm',
          '_title' => 'Revert to earlier revision',
          'entity_type_id' => $entity_type->id(),
        ])
        ->setRequirement('_permission', $admin_permission)
        ->setOption('_admin_route', TRUE);

      return $route;
    }
  }

  /**
   * Gets the revision delete route for entity type that have revision.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getRevisionDeleteRoute(EntityTypeInterface $entity_type) {
    // Gets the entity type admin permission.
    $admin_permission = $entity_type->getAdminPermission();
    if ($entity_type->hasLinkTemplate('revision_delete')) {
      $route = new Route($entity_type->getLinkTemplate('revision_delete'));
      $route
        ->setDefaults([
          '_form' => '\Drupal\toolkit\Form\RevisionDeleteForm',
          '_title' => 'Delete earlier revision',
          'entity_type_id' => $entity_type->id(),
        ])
        ->setRequirement('_permission', $admin_permission)
        ->setOption('_admin_route', TRUE);

      return $route;
    }
  }

}
