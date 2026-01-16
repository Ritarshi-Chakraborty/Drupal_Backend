<?php

    namespace Drupal\custom_service;
    use Drupal\Core\DependencyInjection\ServiceProviderBase;
    use Drupal\Core\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    /**
     * Modifies the custom_service.info_service service definition.
     *
     * This service provider overrides the original class of the
     * 'custom_service.info_service' service and injects the 'current_user'
     * dependency into the new implementation.
     */
    class CustomServiceServiceProvider extends ServiceProviderBase {

        /**
         * {@inheritDoc}
         */
        public function alter(ContainerBuilder $container) {
            $container
            ->getDefinition('custom_service.info_service')
            ->setClass('Drupal\custom_service\Service\NewService')
            ->setArguments([new Reference('current_user')]);
        }
    }
      
?>
