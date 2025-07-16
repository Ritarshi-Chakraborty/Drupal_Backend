<?php
    
    namespace Drupal\ritam\EventSubscriber;
    use Drupal\Core\Routing\RouteSubscriberBase;
    use Symfony\Component\Routing\RouteCollection;

    
    class RouteSubscriber extends RouteSubscriberBase {

        /**
         * {@inheritdoc}
         */
        protected function alterRoutes(RouteCollection $collection) {
            // Change the path of the login form
            if($route = $collection->get('user.login')) {
                $route->setPath('/login');
            }

            // Restrict access to the custom node Id pages to administrators.
            if ($route = $collection->get('ritam.node_id')) {
                $route->setRequirement('_role', 'administrator');
            }
        }
    }
?>
