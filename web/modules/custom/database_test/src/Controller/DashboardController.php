<?php

namespace Drupal\database_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a dashboard displaying event statistics.
 */
class DashboardController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var Drupal\Core\Database\Database
   */
  protected $database;

  /**
   * The entity type manager service.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new DashboardController object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct($database, EntityTypeManagerInterface $entityTypeManager) {
    $this->database = Database::getConnection();
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns the dashboard render array.
   *
   * @return array
   *   A render array containing yearly and type-based event statistics.
   */
  public function dashboard() {
    $yearly_counts = $this->getEventCountsByYear();
    $quarterly_counts = $this->getEventCountsByQuarter();
    $type_counts = $this->getEventCountsByType();

    return [
      '#theme' => 'dashboard',
      '#yearly_counts' => $yearly_counts,
      '#type_counts' => $type_counts,
      '#quarterly_counts' => $quarterly_counts,
    ];
  }

  /**
   * Returns the count of events per year.
   *
   * @return object
   *   Returns the result object.
   */
  protected function getEventCountsByYear() {
    $query = $this->database->select('node_field_data', 'n');
    $query->join('node__field_date', 'fd', 'n.nid = fd.entity_id');

    $query->addExpression("EXTRACT(YEAR FROM fd.field_date_value)", 'year');
    $query->addExpression("COUNT(n.nid)", 'event_count');

    $query->condition('n.type', 'event');
    $query->condition('n.status', 1);

    $query->groupBy('year');
    $query->orderBy('year', 'DESC');

    $result = $query->execute()->fetchAll();
    return $result;
  }

  /**
   * Returns the count of events per quarter.
   *
   * @return object
   *   Returns the result object.
   */
  protected function getEventCountsByQuarter() {
    $query = $this->database->select('node_field_data', 'n');
    $query->join('node__field_date', 'fd', 'n.nid = fd.entity_id');

    $query->addExpression("EXTRACT(YEAR FROM fd.field_date_value)", 'year');
    $query->addExpression("EXTRACT(QUARTER FROM fd.field_date_value)", 'quarter');
    $query->addExpression("COUNT(n.nid)", 'event_count');

    // Add quarter_month based on quarter number.
    $query->addExpression("
        CASE 
            WHEN EXTRACT(QUARTER FROM fd.field_date_value) = 1 THEN 'January - March'
            WHEN EXTRACT(QUARTER FROM fd.field_date_value) = 2 THEN 'April - June'
            WHEN EXTRACT(QUARTER FROM fd.field_date_value) = 3 THEN 'July - September'
            WHEN EXTRACT(QUARTER FROM fd.field_date_value) = 4 THEN 'October - December'
            ELSE 'Unknown'
        END
      ", 'quarter_month');

    $query->groupBy('year');
    $query->groupBy('quarter');
    $query->groupBy('quarter_month');
    $query->orderBy('year', 'DESC');

    $result = $query->execute()->fetchAll();
    return $result;
  }

  /**
   * Returns the count of events per type.
   *
   * @return object
   *   Returns the result object.
   */
  protected function getEventCountsByType() {
    $query = $this->database->select('taxonomy_term_field_data', 't');
    $query->leftjoin('node__field_type', 'ft', 't.tid = ft.field_type_target_id AND t.vid = :type', [
      ':type' => 'event_type',
    ]);
    $query->leftjoin('node_field_data', 'n', 'n.nid = ft.entity_id AND n.type = :content_type AND n.status = 1', [
      ':content_type' => 'event',
    ]);

    $query->addField('t', 'tid', 'term_id');
    $query->addField('t', 'name', 'term_name');
    $query->addExpression('COUNT(n.nid)', 'event_count');
    $query->groupBy('t.tid');
    $query->groupBy('t.name');

    // $query->leftjoin('node__field_type', 'ft', 't.tid = ft.field_type_target_id AND t.vid = :type', [
    // ':type' => 'event_type',
    // ]);
    // $query->addField('t', 'tid', 'term_id');
    // $query->addField('t', 'name', 'term_name');
    // $query->addExpression('COUNT(ft.entity_id)', 'event_count');
    // $query->groupBy('t.tid');
    // $query->groupBy('t.name');

    $result = $query->execute()->fetchAll();
    return $result;
  }

}
