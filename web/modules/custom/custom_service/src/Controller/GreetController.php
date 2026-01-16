<?php

    namespace Drupal\custom_service\Controller;
    use Drupal\Core\Controller\ControllerBase;
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Drupal\custom_service\Service\ModuleService;

    /**
     * Controller for the custom_service module.
     *
     * Displays a greeting message using a custom service.
     */
    class GreetController extends ControllerBase {
        /**
         * The custom module service.
         *
         * @var ModuleService
         */
        protected $my_service;

        /**
         * Constructs a new GreetController object.
         *
         * @param ModuleService $my_service
         *   The custom module service.
         */
        public function __construct(ModuleService $my_service) {
            $this->my_service = $my_service;
        }

        /**
         * Creates an instance of the controller.
         *
         * @param ContainerInterface $container
         *   The service container.
         *
         * @return static
         *   Returns an instance of the controller.
         */
        public static function create(ContainerInterface $container) {
            return new static (
                $container->get('custom_service.info_service')
            );
        }

        /**
         * Displays a greeting message.
         *
         * @return array
         *   A render array containing the greeting markup.
         */
        public function greet() {
            $title = $this->my_service->getTitle();
            $content = $this->my_service->desc('Guess who created this service? ME.');
            return [
                '#title' => $title,
                '#markup' => $content
            ];
        }
    }
    
?>
