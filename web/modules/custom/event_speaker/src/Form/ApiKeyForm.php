<?php

namespace Drupal\event_speaker\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form which stores the API key of the site.
 */
class ApiKeyForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'site_api_key';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['site_api_key.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('site_api_key.settings');
    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter the API key'),
      '#required' => TRUE,
      '#default_value' => $config->get('api_key'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('site_api_key.settings');
    $config->set('api_key', $form_state->getValue('api_key'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
