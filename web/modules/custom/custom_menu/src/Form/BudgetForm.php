<?php

namespace Drupal\custom_menu\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a custom configuration form which stores the budget.
 */
class BudgetForm extends ConfigFormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'site_budget_form';
  }

  /**
   * {@inheritDoc}
   */
  public function getEditableConfigNames() {
    return ['site_budget_config.settings'];
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('site_budget_config.settings');
    $form['budget'] = [
      '#type' => 'number',
      '#title' => $this->t('Enter your Budget'),
      '#required' => TRUE,
      '#default_value' => $config->get('budget'),
      '#field_suffix' => $this->t('Cr INR'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('site_budget_config.settings');
    $config->set('budget', $form_state->getValue('budget'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }

}
