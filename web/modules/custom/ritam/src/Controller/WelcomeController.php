<?php
    namespace Drupal\ritam\Controller;
    use Drupal\Core\Controller\ControllerBase;

    /**
     * Class WelcomeController
     *
     * Returns a page title from a custom service.
     */
    class WelcomeController extends ControllerBase {

        /**
         * Returns a page with a title from the custom service.
         *
         * Uses the 'ritam.custom_service' to generate a greeting for the page title.
         *
         * @return array
         *   A renderable array with the title set from the custom service and empty markup.
         */
        public function index() {
            $title = \Drupal::service('ritam.custom_service');

            return [
                '#title' => $title->hello(),
                '#markup' => ''
            ];
        }
    }
?>
