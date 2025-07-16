<?php

    namespace Drupal\custom_service\Service;
    use Drupal\Core\Session\AccountProxyInterface;
    use Symfony\Component\DependencyInjection\ContainerInterface;

    /**
     * Service class for the custom_service module.
     *
     * Provides utility functions related to the current user.
     */
    class ModuleService {

        /**
         * The current logged-in user.
         *
         * @var AccountProxyInterface
         */
        protected $currentUser;

        /**
         * Constructs a new ModuleService object.
         *
         * @param AccountProxyInterface $current_user
         *   The current user service.
         */
        public function __construct(AccountProxyInterface $current_user) {
            $this->currentUser = $current_user;
        }

        /**
         * Creates an instance of the service.
         *
         * @param ContainerInterface $container
         *   The service container.
         *
         * @return static
         *   Returns an instance of this service class.
         */
        public static function create(ContainerInterface $container) {
            return new static(
            $container->get('current_user')
            );
        }

        public function getTitle() {
            return 'Custom Service';
        }

        /**
         * Returns a personalized message for the current user.
         *
         * @param string $message
         *   The message to include.
         *
         * @return string
         *   A string with a greeting and the provided message.
         */
        public function desc($message) {
            $username = $this->currentUser->getDisplayName();
            return 'Hi '.$username.'! '.$message.'';
        }
    }
    
?>
