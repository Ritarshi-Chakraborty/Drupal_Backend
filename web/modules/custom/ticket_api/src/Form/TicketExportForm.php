<?php

namespace Drupal\ticket_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Batch\BatchBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a form to export tickets based on filters.
 */
class TicketExportForm extends FormBase implements ContainerInjectionInterface {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The entity manager interface service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritDoc}
   */
  public function __construct(MessengerInterface $messenger, EntityTypeManagerInterface $entityTypeManager) {
    $this->messenger = $messenger;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'ticket_export_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['status'] = [
      '#type' => 'select',
      '#title' => $this->t('Status'),
      '#options' => [
        'any' => $this->t('Any'),
        'open' => $this->t('Open'),
        'closed' => $this->t('Closed'),
        'resolved' => $this->t('Resolved'),
        'in_progress' => $this->t('In progress'),
      ],
      '#default_value' => 'any',
    ];

    $form['priority'] = [
      '#type' => 'select',
      '#title' => $this->t('Priority'),
      '#options' => [
        'any' => $this->t('Any'),
        'low' => $this->t('Low'),
        'medium' => $this->t('Medium'),
        'high' => $this->t('High'),
        'urgent' => $this->t('Urgent'),
      ],
      '#default_value' => 'any',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export Tickets'),
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $status = $form_state->getValue('status');
    $priority = $form_state->getValue('priority');

    // Load node IDs based on filters.
    $query = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'ticket');

    if ($status !== 'any') {
      $query->condition('field_status', $status);
    }

    if ($priority !== 'any') {
      $query->condition('field_priority', $priority);
    }

    $nids = $query->execute();

    if (empty($nids)) {
      $this->messenger->addMessage($this->t('No tickets found for selected filters.'));
      return;
    }

    $batch_builder = new BatchBuilder();
    $batch_builder
      ->setTitle($this->t('Exporting content'))
      ->setInitMessage($this->t('Export starting...'))
      ->setProgressMessage($this->t('Processed @current out of @total.'))
      ->setErrorMessage($this->t('Export has encountered an error.'))
      ->setFinishCallback([self::class, 'exportFinished']);

    // Add operations per node.
    foreach ($nids as $nid) {
      $batch_builder->addOperation([self::class, 'processNode'], [$nid]);
    }

    batch_set($batch_builder->toArray());
  }

  /**
   * Batch operation callback for processing a single node.
   *
   * @param int $nid
   *   The node ID to export.
   * @param array $context
   *   The batch context array.
   */
  public static function processNode($nid, &$context) {
    $node = Node::load($nid);
    $assigned_user = $node->get('field_assigned_to')->entity;

    // Simulate export by writing to a temporary static file (for demo)
    $data = [
      'nid' => $node->id(),
      'title' => $node->getTitle(),
      'node_url' => $node->toUrl()->toString(),
      'node_path' => $node->toUrl()->setAbsolute()->toString(),
      'created_time' => date('Y-m-d H:i:s', $node->getCreatedTime()),
      'update_time' => date('Y-m-d H:i:s', $node->getChangedTime()),
      'status' => $node->get('field_status')->value,
      'priority' => $node->get('field_priority')->value,
      'author_id' => $node->getOwnerId(),
      'author_name' => $node->getOwner()->getDisplayName(),
      'assigned_to_user_id' => $assigned_user->id(),
      'assigned_to_user_name' => $assigned_user->getDisplayName(),
    ];

    $file = 'public://ticket_nodes_' . time() . '.csv';
    $write_header = !file_exists($file);

    $handle = fopen($file, 'a');
    if ($write_header) {
      fputcsv($handle, array_keys($data));
    }
    fputcsv($handle, $data);
    fclose($handle);

    $context['message'] = t('Exported node @nid', ['@nid' => $nid]);
  }

  /**
   * Batch finish callback.
   *
   * @param bool $success
   *   TRUE if the batch completed successfully, FALSE otherwise.
   * @param array $results
   *   The results of the batch operations.
   * @param array $operations
   *   The operations that were not completed, if any.
   */
  public static function exportFinished($success, $results, $operations) {
    if ($success) {
      \Drupal::messenger()->addStatus(t('Export completed successfully.'));
    }
    else {
      \Drupal::messenger()->addError(t('Export encountered issues.'));
    }
  }

}
