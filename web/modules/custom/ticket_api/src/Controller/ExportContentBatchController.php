<?php

namespace Drupal\ticket_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Batch\BatchBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Controller for exporting content using Drupal batch API.
 */
class ExportContentBatchController extends ControllerBase {

  /**
   * The entity manager interface service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Initiates the batch export process.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response after the batch is processed.
   */
  public function export() {
    $query = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(TRUE);

    $nids = $query->execute();

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
    // Redirect after completion.
    return batch_process('/');
  }

  /**
   * Processes an individual node for export.
   *
   * @param int $nid
   *   The node ID to export.
   * @param array $context
   *   The batch context array.
   */
  public static function processNode($nid, &$context) {
    $node = Node::load($nid);

    // Simulate export by writing to a temporary static file (for demo)
    $data = [
      'nid' => $node->id(),
      'title' => $node->getTitle(),
      'created' => date('Y-m-d H:i:s', $node->getCreatedTime()),
    ];

    $file = 'public://exported_content_' . time() . '.csv';
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
   * Callback function when batch export is finished.
   *
   * @param bool $success
   *   Indicates whether the batch was successful.
   * @param array $results
   *   The results from the batch process.
   * @param array $operations
   *   The remaining operations if the batch was not completed.
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
