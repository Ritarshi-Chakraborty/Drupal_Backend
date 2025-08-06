<?php

namespace Drupal\database_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides a form for taxonomy term lookup and related node retrieval.
 */
class TaxonomyForm extends FormBase {

  /**
   * The messenger service.
   *
   * @var Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The database connection.
   *
   * @var Drupal\Core\Database\Database
   */
  protected $database;

  /**
   * Constructs a new TaxonomyForm object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
    $this->database = Database::getConnection();
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'taxonomy_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Enter a taxonomy term'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('name');
    $exists = $this->termExists($name);

    if ($exists) {
      $response = "<div><strong>Taxonomy Term</strong> ---> $name</div>";

      $id = $this->getTermId($name);
      $response .= "<div><strong>Term Id</strong> ---> $id</div>";

      $uuid = $this->getUuid($id);
      $response .= "<div><strong>UUID</strong> ---> $uuid</div>
                    <div><strong>Associated Nodes :-</strong>
                    <ul>";

      $node_list = $this->getNodes($id);
      foreach ($node_list as $node) {
        $response .= "<li>
                        <a href='{$node->node_url}' target=_blank>{$node->node_title}</a>
                      </li>";
        // $link = Link::fromTextAndUrl($node->node_title, $node->node_url)->toString();
        // $response .= "<li>$link</li>";
      }
      $response .= "</ul></div>";
      $this->messenger->addMessage($response);
    }
    else {
      $this->messenger->addError("The taxonomy term $name does not exist.");
    }
  }

  /**
   * Checks if a taxonomy term exists by its name.
   *
   * @param string $value
   *   The name of the taxonomy term to check.
   *
   * @return bool
   *   TRUE if the term exists, FALSE otherwise.
   */
  protected function termExists(string $value) {
    $query = $this->database->select('taxonomy_term_field_data', 't');
    $query->fields('t');
    $query->condition('t.name', $value, '= BINARY');
    $result = $query->execute()->fetchAssoc();

    if (empty($result)) {
      return FALSE;
    }
    else {
      return TRUE;
    }
  }

  /**
   * Retrieves the taxonomy term ID by its name.
   *
   * @param string $value
   *   The name of the taxonomy term.
   *
   * @return int
   *   The term ID if found.
   */
  protected function getTermId(string $value) {
    $query = $this->database->select('taxonomy_term_field_data', 't');
    $query->fields('t');
    $query->condition('t.name', $value, '=');

    $result = $query->execute()->fetchAssoc();
    return $result['tid'];
  }

  /**
   * Retrieves the UUID of a taxonomy term by its term ID.
   *
   * @param int $termId
   *   The taxonomy term ID.
   *
   * @return string
   *   The UUID of the taxonomy term.
   */
  protected function getUuid(int $termId) {
    $query = $this->database->select('taxonomy_term_data', 'td');
    $query->fields('td');
    $query->condition('td.tid', $termId, '=');

    $result = $query->execute()->fetchAssoc();
    return $result['uuid'];
  }

  /**
   * Retrieves nodes associated with a given taxonomy term ID.
   *
   * @param int $termId
   *   The taxonomy term ID.
   *
   * @return object
   *   Connected node objects with node_id, node_title, and node_url properties.
   */
  protected function getNodes(int $termId) {
    $query = $this->database->select('node_field_data', 'n');
    $query->join('node__field_type', 'ft', 'n.nid = ft.entity_id AND ft.field_type_target_id = :id', [
      ':id' => $termId,
    ]);

    $query->addField('n', 'nid', 'node_id');
    $query->addField('n', 'title', 'node_title');
    // $query->addExpression("CONCAT('/node/', n.nid)", 'node_url');
    $result = $query->execute()->fetchAll();

    foreach ($result as &$row) {
      // If we are using `Link::fromTextAndUrl` to create link in `submitForm` function, then do not convert the url to string here.
      $url = Url::fromRoute('entity.node.canonical', ['node' => $row->node_id])->toString();
      $row->node_url = $url;
    }
    return $result;
  }

}
