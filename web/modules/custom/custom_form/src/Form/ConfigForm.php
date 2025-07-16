<?php
    
    namespace Drupal\custom_form\Form;
    use Drupal\Core\Form\ConfigFormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Drupal\Core\Site\Settings;

    /**
     * Defines a custom configuration form for site settings.
     */
    class ConfigForm extends ConfigFormBase {

        /**
         * {@inheritdoc}
         */
        public function getFormId() {
            return 'site_config_form_settings';
        }

        /**
         * {@inheritdoc}
         */
        public function getEditableConfigNames() {
            return ['site_config_form.settings'];
        }

        /**
         * {@inheritdoc}
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
         * {@inheritdoc}
         */
        public function validateForm(array &$form, FormStateInterface $form_state){
            // Mobile number validation
            $phone = $form_state->getValue('phone_number');
            if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
                $form_state->setErrorByName('phone_number', $this->t('Indian phone number must be exactly of 10 digits where the first digit is between 6-9.'));
            }
            
            // Email validation
            $email = $form_state->getValue('email_id');
            $api_key = Settings::get('api_key');

            $url = "http://apilayer.net/api/check?access_key=$api_key&email=$email";
            $response = file_get_contents($url);
            $data = json_decode($response, true);

            // Email validation 1 --> Check if it's a valid email
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $form_state->setErrorByName('email_id', $this->t('Invalid email format.'));
            }

            // Email validation 2 --> Check if the email exists or not
            if(!$data['smtp_check']) {
                $form_state->setErrorByName('email_id', $this->t('This email id does not exist.'));
            }
            
            // Email validation 3 --> Check for pulblic domains
            $public_domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];
            $email_domain = strtolower(substr(strrchr($email, "@"), 1));
            if(!in_array($email_domain, $public_domains)) {
                $form_state->setErrorByName('email_id', $this->t('Please use a public email domain like Gmail, Yahoo, or Outlook.'));
            }

            // Email validation 4 --> Check if the email ends in .com
            if (!str_ends_with($email, '.com')) {
                $form_state->setErrorByName('email_id', $this->t('Only ".com" email addresses are allowed.'));
            }
        }

        /**
         * {@inheritdoc}
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
