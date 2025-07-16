<?php
    namespace Drupal\ritam\Controller;
    use Drupal\Core\Controller\ControllerBase;

    /**
     * Class HomeController
     *
     * Provides route responses for the 'ritam' module.
     */
    class HomeController extends ControllerBase {
        /**
         * Returns a greeting message using a custom hook.
         *
         * @return array
         *   A renderable array containing the greeting markup.
     */
        public function index() {
            // Calling the custom hook
            $greet = \Drupal::moduleHandler()->invoke('ritam', 'greet');

            return [
                '#title' => 'Ritam module',
                '#markup' => $greet
            ];
        }

        /**
         * Returns information about a person to be rendered using a custom theme.
         *
         * @return array
         *   A renderable array containing user info and a reference to the theme hook.
         */
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

        /**
         * Returns a title with the given node ID.
         *
         * @param int $nodeId
         *   The node ID passed from the route.
         *
         * @return array
         *   A renderable array containing the translated title with node ID.
         */
        public function node($nodeId) {
            return [
                '#title' => $this->t('The Id of this node is @id', ['@id' => $nodeId])
            ];
        }
    }
?>
