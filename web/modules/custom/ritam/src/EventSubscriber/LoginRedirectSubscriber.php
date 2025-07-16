<?php

    namespace Drupal\ritam\EventSubscriber;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\KernelEvents;
    use Symfony\Component\HttpKernel\Event\ResponseEvent;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Drupal\Core\Url;

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
                // Generate URL from Route name
                $url = Url::fromRoute('ritam.welcome')->toString();
                // Set the redirect response
                $event->setResponse(new RedirectResponse($url));
            }
        }
    }
?>
