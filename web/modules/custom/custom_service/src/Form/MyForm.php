<?php

    namespace Drupal\custom_service\Form;
    use Drupal\Core\Form\FormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Drupal\Core\Messenger\MessengerInterface;

    /**
     * Provides a custom form for user input with a name field.
     */
    class MyForm extends FormBase {
        /**
         * The messenger service.
         *
         * @var MessengerInterface
         */
        protected $messenger;

        /**
         * Constructs a new MyForm instance.
         *
         * @param MessengerInterface $messenger
         *   The messenger service.
         */
        public function __construct(MessengerInterface $messenger) {
            $this->messenger = $messenger;
        }

        /**
         * Creates an instance of the form class.
         *
         * @param ContainerInterface $container
         *   The service container.
         *
         * @return static
         *   Returns an instance of this class.
         */
        public static function create(ContainerInterface $container) {
            return new static (
                $container->get('messenger')
            );
        }

        /**
         * {@inheritdoc}
         */
        public function getFormId() {
            return 'service_form';
        }

        /**
         * {@inheritdoc}
         */
        public function buildForm(array $form, FormStateInterface $form_state) {
            $form['name'] = [
                '#type' => 'textfield',
                '#title' => $this->t('Your Name')
            ];

            $form['submit'] = [
                '#type'=> 'submit',
                '#value'=> $this->t('Submit')
            ];

            return $form;
        }

        /**
         * {@inheritdoc}
         */
        public function submitForm(array &$form, FormStateInterface $form_state) {
            $name = $form_state->getValue('name');
            $form_state->setValue('name', $name);
            $this->messenger->addMessage($this->t('Hello @name, your form was submitted.', ['@name' => $name]));
        }
    }
?>
