<?php
    
    namespace Drupal\custom_form\Form;
    use Drupal\Core\Form\ConfigFormBase;
    use Drupal\Core\Form\FormStateInterface;

    /**
     * Custom config form
     */
    class ConfigForm extends ConfigFormBase {

        /**
         * Function which returns form id
         *
         * @return void
         */
        public function getFormId() {
            return 'site_config_form_settings';
        }

        /**
         * Function which returns the configuration name
         *
         * @return void
         */
        public function getEditableConfigNames() {
            return ['site_config_form.settings'];
        }

        /**
         * Function which creates a form & returns it
         *
         * @param array $form
         * @param FormStateInterface $form_state
         * @return void
         */
        public function buildForm(array $form, FormStateInterface $form_state) {
            $config = $this->config('site_config_form.settings');
            $form['fullname'] = [
                '#type' => 'textfield',
                '#title' => $this->t('Enter your Name'),
                '#required'=> TRUE,
                '#default_value' => $config->get('fullname')
            ];

            $form['phone_number'] = [
                '#type' => 'tel',
                '#title' => $this->t('Enter your Phone Number'),
                '#required'=> TRUE,
                '#default_value' => $config->get('phone_number')
            ];

            $form['email_id'] = [
                '#type' => 'email',
                '#title' => $this->t('Enter your Email Id'),
                '#required'=> TRUE,
                '#default_value' => $config->get('email_id')
            ];

            $form['gender'] = [
                '#type' => 'radios',
                '#title' => $this->t('Select your Gender'),
                '#options' => array(
                    'Male' => t('Male'),
                    'Female' => t('Female')
                ),
                '#required'=> TRUE,
                '#default_value' => $config->get('gender')
            ];

            return parent::buildForm($form, $form_state);
        }

        /**
         * Class which validates the different fields
         *
         * @param array $form
         * @param FormStateInterface $form_state
         * @return void
         */
        public function validateForm(array &$form, FormStateInterface $form_state){
            //mobile number validation
            $phone = $form_state->getValue('phone_number');
            if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
                $form_state->setErrorByName('phone_number', $this->t('Indian phone number must be exactly of 10 digits where the first digit is between 6-9.'));
            }
            
            //email validation 1 --> Check if it's a valid email
            $email = $form_state->getValue('email_id');
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $form_state->setErrorByName('email_id', $this->t('Invalid email format.'));
            }
            
            //email validation 2 --> Check for pulblic domains
            $public_domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];
            $email_domain = strtolower(substr(strrchr($email, "@"), 1));
            if(!in_array($email_domain, $public_domains)) {
                $form_state->setErrorByName('email_id', $this->t('Please use a public email domain like Gmail, Yahoo, or Outlook.'));
            }

            //email validation 3 --> Check if the email ends in .com
            if (!str_ends_with($email, '.com')) {
                $form_state->setErrorByName('email_id', $this->t('Only ".com" email addresses are allowed.'));
            }
        }

        /**
         * Function which handles the form submission
         *
         * @param array $form
         * @param FormStateInterface $form_state
         * @return void
         */
        public function submitForm(array &$form, FormStateInterface $form_state) {
            $config = $this->config('site_config_form.settings');
            $config->set('fullname', $form_state->getValue('fullname'));
            $config->set('phone_number', $form_state->getValue('phone_number'));
            $config->set('email_id', $form_state->getValue('email_id'));
            $config->set('gender', $form_state->getValue('gender'));
            $config->save();

            parent::submitForm($form, $form_state);
        }

    }