<?php

namespace Drupal\toolkit\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Show revisions for revisionable content entity.
 */
class ContentEntityRevisionController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The entity type id of the contextual revisionable entity.
   *
   * @var string
   */
  public $entityType;

  /**
   * The entity id of the contextual revisionable entity.
   *
   * @var int
   */
  public $entityId;

  /**
   * The revision id of the contextual revisionable entity.
   *
   * @var int|null
   */
  public $entityRevisionId;

  /**
   * Constructs a new ContentEntityRevisionController.
   *
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack object.
   */
  public function __construct(DateFormatter $date_formatter, Renderer $renderer, RequestStack $request_stack) {
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
    $this->requestStack = $request_stack;
    $this->setDefaultRevisionableEntityInfo();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer'),
      $container->get('request_stack')
    );
  }

  /**
   * Set contextual revisionable entity info: id, type, revision id.
   */
  public function setDefaultRevisionableEntityInfo() {
    // Load the current request.
    $request = $this->requestStack
      ->getCurrentRequest();

    // Store the entity type id of the contextual revisionable entity.
    $this->entityType = $request->attributes
      ->get('entity_type_id');

    // Store the entity id of the contextual revisionable entity.
    $this->entityId = $request->attributes
      ->get($this->entityType);

    // Store the revision id of the contextual revisionable entity.
    $this->entityRevisionId = $request->attributes
      ->get("{$this->entityType}_revision");
  }

  /**
   * Displays entity revision.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow() {
    // Get the revisionable entity.
    $entity = $this
      ->entityTypeManager()
      ->getStorage($this->entityType)
      ->loadRevision($this->entityRevisionId);

    // Get the revisionable entity view builder.
    $view_builder = $this
      ->entityTypeManager()
      ->getViewBuilder($this->entityType);

    return $view_builder->view($entity);
  }

  /**
   * Page title callback for entity revision.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle() {
    // Get the revisionable entity.
    $entity = $this
      ->entityTypeManager()
      ->getStorage($this->entityType)
      ->loadRevision($this->entityRevisionId);

    return $this->t('Revision of %title from %date', [
      '%title' => $entity->label(),
      '%date' => $this->dateFormatter->format($entity->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of the revisionable entity.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview() {
    // Get entity type id.
    $entity_type_id = $this->entityType;

    // Get storage.
    $storage = $this
      ->entityTypeManager()
      ->getStorage($this->entityType);

    // Load the entity.
    $entity = $storage->load($this->entityId);

    // Get the current user.
    $account = $this->currentUser();

    // Build the renderable array.
    $build['#title'] = $this->t('Revisions for %title', ['%title' => $entity->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    // Get the entity type admin permission.
    $admin_permission = $entity
      ->getEntityType()
      ->getAdminPermission();

    // Check if currect user has the admin permission.
    $user_is_admin = $account->hasPermission($admin_permission);

    $rows = [];

    // Get the revision ids.
    $vids = $storage->revisionIds($entity);

    $latest_revision = TRUE;

    // Iterate the revisions.
    foreach (array_reverse($vids) as $vid) {
      $revision = $storage->loadRevision($vid);
      $username = [
        '#theme' => 'username',
        '#account' => $revision->getRevisionUser(),
      ];

      // Use revision link to link to revisions that are not active.
      $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
      if ($vid != $entity->getRevisionId()) {
        $link = Link::fromTextAndUrl($date, new Url("entity.{$entity_type_id}.revision", [
          $entity_type_id => $entity->id(),
          "{$entity_type_id}_revision" => $vid,
        ]))->toString();
      }
      else {
        $link = $entity->toLink($date)->toString();
      }

      $row = [];
      $column = [
        'data' => [
          '#type' => 'inline_template',
          '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
          '#context' => [
            'date' => $link,
            'username' => $this->renderer->renderPlain($username),
            'message' => [
              '#markup' => $revision->getRevisionLogMessage(),
              '#allowed_tags' => Xss::getHtmlTagList(),
            ],
          ],
        ],
      ];
      $row[] = $column;

      if ($latest_revision) {
        $row[] = [
          'data' => [
            '#prefix' => '<em>',
            '#markup' => $this->t('Current revision'),
            '#suffix' => '</em>',
          ],
        ];
        foreach ($row as &$current) {
          $current['class'] = ['revision-current'];
        }
        $latest_revision = FALSE;
      }
      else {
        $links = [];
        if ($user_is_admin) {
          $links['revert'] = [
            'title' => $this->t('Revert'),
            'url' => Url::fromRoute("entity.{$entity_type_id}.revision_revert", [
              $entity_type_id => $entity->id(),
              "{$entity_type_id}_revision" => $vid,
            ]),
          ];

          $links['delete'] = [
            'title' => $this->t('Delete'),
            'url' => Url::fromRoute("entity.{$entity_type_id}.revision_delete", [
              $entity_type_id => $entity->id(),
              "{$entity_type_id}_revision" => $vid,
            ]),
          ];
        }

        $row[] = [
          'data' => [
            '#type' => 'operations',
            '#links' => $links,
          ],
        ];
      }

      $rows[] = $row;
    }

    $build['revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
