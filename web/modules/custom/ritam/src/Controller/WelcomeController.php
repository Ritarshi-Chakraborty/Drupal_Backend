<?php
    namespace Drupal\ritam\Controller;
    use Drupal\Core\Controller\ControllerBase;

    class WelcomeController extends ControllerBase {
        public function index() {
            $title = \Drupal::service('ritam.custom_service');

            return [
                '#title' => $title->hello(),
                '#markup' => ''
            ];
        }
    }
?>