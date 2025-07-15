<?php

    namespace Drupal\ritam\EventSubscriber;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\KernelEvents;
    use Symfony\Component\HttpKernel\Event\ResponseEvent;
    use Symfony\Component\HttpFoundation\RedirectResponse;

    class LoginRedirectSubscriber implements EventSubscriberInterface {

        public static function getSubscribedEvents() {
            return [
                KernelEvents::RESPONSE => 'onRespond',
            ];
        }

        public function onRespond(ResponseEvent $event) {
            $request = $event->getRequest();
            $session = $request->getSession();

            if ($session->get('custom_login_redirect')) {
                $session->remove('custom_login_redirect');
                $event->setResponse(new RedirectResponse('/welcome'));
            }
        }
    }

?>