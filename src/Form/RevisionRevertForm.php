<?php

namespace Drupal\toolkit\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting revision.
 *
 * @ingroup toolkit
 */
class RevisionRevertForm extends ConfirmFormBase {

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
   * Provides system time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a new RevisionRevertForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   Provides system time.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, DateFormatterInterface $date_formatter, TimeInterface $time) {
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $date_formatter;
    $this->time = $time;
    $this->setDefaultRevisionableEntityInfo();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('date.formatter'),
      $container->get('datetime.time')
    );
  }

  /**
   * Set contextual revisionable entity info: id, type, revision id, storage.
   */
  public function setDefaultRevisionableEntityInfo() {
    // Load the current request.
    $request = $this->getRequest();

    // Store the entity type id of the contextual revisionable entity.
    $this->entityType = $request->attributes->get('entity_type_id');

    // Store the revision id of the contextual revisionable entity.
    $this->entityRevisionId = $request->attributes->get("{$this->entityType}_revision");

    // Store the storage of the contextual revisionable entity.
    $this->storage = $this->entityTypeManager->getStorage($this->entityType);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'toolkit_revision_revert_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to revert to the revision from %revision-date?', [
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
    return $this->t('Revert');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return '';
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
    // The revision timestamp will be updated when the revision is saved. Keep
    // the original one for the confirmation message.
    $original_revision_timestamp = $this->revision->getRevisionCreationTime();

    $this->revision = $this->prepareRevertedRevision($this->revision);
    $this->revision->revision_log_message = $this->t('Copy of the revision from @date.', [
      '@date' => $this->dateFormatter->format($original_revision_timestamp),
    ]);
    $this->revision->save();

    // Get entity type label.
    $entity_type_label = $this
      ->revision
      ->getEntityType()
      ->getLabel()
      ->__toString();

    $this->logger('content')->notice(
      '%entity_type_label: reverted %title revision %revision.',
      [
        '%entity_type_label' => $entity_type_label,
        '%title' => $this->revision->label(),
        '%revision' => $this->revision->getRevisionId(),
      ]
    );
    $this->messenger()->addMessage(
      t('%entity_type_label %title has been reverted to the revision from %revision-date.', [
        '%entity_type_label' => $entity_type_label,
        '%title' => $this->revision->label(),
        '%revision-date' => $this->dateFormatter->format($original_revision_timestamp),
      ])
    );
    $form_state->setRedirect(
      "entity.{$this->entityType}.version_history",
      [$this->entityType => $this->revision->id()]
    );
  }

  /**
   * Prepares a revision to be reverted.
   *
   * @param \Drupal\Core\Entity\EntityInterface $revision
   *   The revision to be reverted.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The prepared revision ready to be stored.
   */
  protected function prepareRevertedRevision(EntityInterface $revision) {
    $revision->setNewRevision();
    $revision->isDefaultRevision(TRUE);
    $revision->setRevisionCreationTime($this->time->getRequestTime());

    return $revision;
  }

}
