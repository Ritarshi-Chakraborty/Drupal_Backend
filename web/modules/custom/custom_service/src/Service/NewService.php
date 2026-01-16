<?php

    namespace Drupal\custom_service\Service;
    use Drupal\Core\Session\AccountInterface;
    use Drupal\custom_service\Service\ModuleService;

    class NewService extends ModuleService {
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
            return $message.' How are you? '.$username.'!';
        }
    }
    
?>
