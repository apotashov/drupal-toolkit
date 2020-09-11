<?php

namespace Drupal\toolkit\Form;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting entity revision.
 *
 * @ingroup toolkit
 */
class RevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Entity revision.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $revision;

  /**
   * The Entity storage.
   *
   * @var \Drupal\toolkit\ContentEntityRevisionStorageInterface
   */
  protected $storage;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The entity type id of the contextual revisionable entity.
   *
   * @var string
   */
  protected $entityType;

  /**
   * The revision id of the contextual revisionable entity.
   *
   * @var int
   */
  protected $entityRevisionId;

  /**
   * Constructs a new RevisionDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, DateFormatterInterface $date_formatter) {
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $date_formatter;
    $this->setDefaultRevisionableEntityInfo();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('date.formatter')
    );
  }

  /**
   * Set contextual revisionable entity info: id, type, revision id, storage.
   */
  public function setDefaultRevisionableEntityInfo() {
    // Load the current request.
    $request = $this->getRequest();

    // Store the entity type id of the contextual revisionable entity.
    $this->entityType = $request->attributes
      ->get('entity_type_id');

    // Store the revision id of the contextual revisionable entity.
    $this->entityRevisionId = $request->attributes
      ->get("{$this->entityType}_revision");

    // Store the storage of the contextual revisionable entity.
    $this->storage = $this->entityTypeManager
      ->getStorage($this->entityType);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'toolkit_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url("entity.{$this->entityType}.version_history", [$this->entityType => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->revision = $this->storage->loadRevision($this->entityRevisionId);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->storage->deleteRevision($this->revision->getRevisionId());
    // Get entity type label.
    $entity_type_label = $this
      ->revision
      ->getEntityType()
      ->getLabel()
      ->__toString();

    $this->logger('content')->notice(
      '%entity_type_label: deleted %title revision %revision.',
      [
        '%entity_type_label' => $entity_type_label,
        '%title' => $this->revision->label(),
        '%revision' => $this->revision->getRevisionId(),
      ]
    );
    $this->messenger()->addMessage(
      t('Revision from %revision-date of %entity_type_label %title has been deleted.', [
        '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
        '%entity_type_label' => $entity_type_label,
        '%title' => $this->revision->label(),
      ])
    );
    $form_state->setRedirect(
      "entity.{$this->entityType}.canonical",
       [$this->entityType => $this->revision->id()]
    );
  }

}
