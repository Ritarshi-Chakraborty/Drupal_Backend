<?php

    namespace Drupal\custom_form\Form;
    use Drupal\Core\Form\FormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Drupal\Core\Ajax\AjaxResponse;
    use Drupal\Core\Ajax\ReplaceCommand;

    
    /**
     * Provides a simple form with AJAX-based addition functionality.
     */
    class SimpleForm extends FormBase {
        
        /**
         * {@inheritDoc}
         */
        public function getFormId() {
            return 'generic_form';
        }

        /**
         * {@inheritDoc}
         */
        public function buildForm(array $form, FormStateInterface $form_state) {
            $form['name'] = [
                '#type'=> 'textfield',
                '#title'=> $this->t('Your name'),
                '#required'=> TRUE
            ];

            $form['number_1'] = [
                '#type'=> 'number',
                '#title'=> $this->t('First number')
            ];

            $form['number_2'] = [
                '#type'=> 'number',
                '#title'=> $this->t('Second number')
            ];

            $form['add'] = [
                '#type'=> 'button',
                '#value'=> 'Add',
                '#ajax'=> [
                    'callback'=> '::calculate',
                    'wrapper' => 'result-field',
                ]
            ];

            // Wrapper for AJAX update
            $form['result'] = [
                '#type' => 'textfield',
                // '#title' => $this->t('Your result'),
                '#value' => $form_state->getValue('result') ?? '',
                '#attributes' => [
                    'readonly' => 'readonly',
                    'id' => 'result-field'
                ],
            ];

            $form['submit'] = [
                '#type'=> 'submit',
                '#value'=> $this->t('Send message')
            ];
            return $form;
        }

        /**
         * {@inheritDoc}
         */
        public function validateForm(array &$form, FormStateInterface $form_state) {
            if(strlen($form_state->getValue('name')) < 3) {
                $form_state->setErrorByName('name', $this->t('Your name must be atleast of 3 characters :('));
            }
        }

        /**
         * AJAX callback to calculate the sum of two numbers.
         *
         * @param array $form
         *   The form structure.
         * @param \Drupal\Core\Form\FormStateInterface $form_state
         *   The current state of the form.
         *
         * @return \Drupal\Core\Ajax\AjaxResponse
         *   The AJAX response with the updated result field.
         */
        public function calculate(array &$form, FormStateInterface $form_state) {
            $num1 = $form_state->getValue('number_1');
            $num2 = $form_state->getValue('number_2');
            $sum = $num1 + $num2;

            // Set the calculated value in the form state
            $form_state->setValue('result', $sum);

            // Also update the value directly for AJAX rendering
            $form['result']['#value'] = $sum;

            $response = new AjaxResponse();
            $response->addCommand(new ReplaceCommand('#result-field', $form['result']));
            return $response;
        }

        /**
         * {@inheritDoc}
         */
        public function submitForm(array &$form, FormStateInterface $form_state) {
            $form_state->setValue('name', $form_state->getValue('name'));
            \Drupal::messenger()->addMessage('Hello, '. $form_state->getValue('name') .' your message is submitted :)');
        }
    }
