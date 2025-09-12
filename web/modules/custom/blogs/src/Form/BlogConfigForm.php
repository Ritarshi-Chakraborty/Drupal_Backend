<?php

namespace Drupal\blogs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\Role;
use Drupal\Core\Config\ConfigFactoryInterface;

class BlogConfigForm extends ConfigFormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'blog_config_form';
  }

  /**
   * {@inheritDoc}
   */
  public function getEditableConfigNames() {
    return ['blog_config_form.settings'];
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Load saved config.
    $config = $this->config('blog_config_form.settings');
    // Load all roles.
    $roles = Role::loadMultiple();

    // Prepare options array.
    $role_options = [];
    foreach ($roles as $role_id => $role) {
      $role_options[$role_id] = $role->label();
    }

    $form['role'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a role'),
      '#options' => $role_options,
      '#empty_option' => $this->t('- Select -'),
      '#default_value' => $config->get('role'),
      '#description' => $this->t('This will fetch all the blogs created by the selected role.'),
    ];

    $form['author'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter author name'),
      '#default_value' => $config->get('author'),
      '#description' => $this->t('This will fetch all the blogs whose author name starts with the above value.'),
    ];

    $form['tag'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter a Tag name'),
      '#default_value' => $config->get('tag'),
      '#description' => $this->t('This will fetch all the blogs associated with the above tag name.'),
    ];

    $form['start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Select the Start Date'),
      '#default_value' => $config->get('start_date'),
      '#description' => $this->t('This will fetch all the blogs created on and after the date.'),
    ];

    $form['end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Select the End Date'),
      '#default_value' => $config->get('end_date'),
      '#description' => $this->t('This will fetch all the blogs created on and before the date.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('blog_config_form.settings');

    $config->set('role', $form_state->getValue('role'))
      ->set('author', $form_state->getValue('author'))
      ->set('tag', $form_state->getValue('tag'))
      ->set('start_date', $form_state->getValue('start_date'))
      ->set('end_date', $form_state->getValue('end_date'));

    $config->save();
    parent::submitForm($form, $form_state);
  }
}
