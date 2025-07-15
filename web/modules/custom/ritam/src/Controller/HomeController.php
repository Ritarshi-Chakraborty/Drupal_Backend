<?php
    namespace Drupal\ritam\Controller;
    use Drupal\Core\Controller\ControllerBase;

    class HomeController extends ControllerBase {
        public function index() {

            // Calling the custom hook
            $greet = \Drupal::moduleHandler()->invoke('ritam', 'greet');

            return [
                '#title' => 'Ritam module',
                '#markup' => $greet
            ];
        }

        public function info() {

            $data = array(
                'name'=>'Ritarshi Chakraborty',
                'email'=>'ritarshi.chakraborty@innoraft.com',
                'city'=>'Kolkata'
            );

            return [
                '#theme'=>'info',
                '#data'=> $data
            ];
        }

        public function node($nodeId) {
            return [
                '#title' => $this->t('The Id of this node is @id', ['@id' => $nodeId])
            ];
        }
    }
?>