<?php

namespace Drupal\product_cart\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Datetime\Time;
use Drupal\Core\Database\Database;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Provides an Add to Cart form for a product.
 */
class AddToCartForm extends FormBase {

  /**
   * The database connection.
   *
   * @var Drupal\Core\Database\Database
   */
  protected $database;

  /**
   * The messenger service.
   *
   * @var Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The time service.
   *
   * @var Drupal\Component\Datetime\Time
   */
  protected $time;

  /**
   * The current user.
   *
   * @var Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritDoc}
   */
  public function __construct(MessengerInterface $messenger, Time $time, AccountProxyInterface $currentUser) {
    $this->messenger = $messenger;
    $this->time = $time;
    $this->currentUser = $currentUser;
    $this->database = Database::getConnection();
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('datetime.time'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_to_cart_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {
    $form['product_id'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Product ID'),
      '#default_value' => $nid,
      '#disabled' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add to Cart'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $product_id = $form_state->getValue('product_id');
    $user_id = $this->currentUser->id();

    $query = $this->database->select('cart_product', 'cp')
      ->fields('cp')
      ->condition('product', $product_id)
      ->condition('user', $user_id)
      ->execute()
      ->fetchAssoc();

    if ($query) {
      $this->messenger->addStatus($this->t('Product already in cart.'));
    }
    else {
      $this->database->insert('cart_product')
        ->fields([
          'product' => $product_id,
          'user' => $user_id,
          'created' => $this->time->getCurrentTime(),
        ])
        ->execute();

      $this->messenger->addMessage($this->t('Product added to cart.'));
    }
  }

}
