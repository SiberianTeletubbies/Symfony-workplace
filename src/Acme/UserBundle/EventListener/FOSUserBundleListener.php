<?php

namespace App\Acme\UserBundle\EventListener;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FOSUserBundleListener implements EventSubscriberInterface
{
    private $router;
    private $securityChecker;

    public function __construct(UrlGeneratorInterface $router, AuthorizationCheckerInterface $securityChecker)
    {
        $this->router = $router;
        $this->securityChecker = $securityChecker;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
            FOSUserEvents::REGISTRATION_INITIALIZE => 'onRegistrationInitialize',
        );
    }

    public function onRegistrationSuccess(FormEvent $event)
    {
        $url = $this->router->generate('task.index');
        $event->setResponse(new RedirectResponse($url));
    }

    public function onRegistrationInitialize(GetResponseUserEvent $event)
    {
        if ( $this->securityChecker->isGranted( 'ROLE_USER' ) ) {
            $url = $this->router->generate('task.index');
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
