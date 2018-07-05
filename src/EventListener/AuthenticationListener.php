<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\EventListener;

use FanOfSymfony\Bundle\EasyAdminBundle\Event\FilterUserResponseEvent;
use FanOfSymfony\Bundle\EasyAdminBundle\Event\UserEvent;
use FanOfSymfony\Bundle\EasyAdminBundle\UserEvents;
use FanOfSymfony\Bundle\EasyAdminBundle\Security\LoginManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AuthenticationListener implements EventSubscriberInterface
{
    /**
     * @var LoginManagerInterface
     */
    private $loginManager;

    /**
     * @var string
     */
    private $firewallName;

    /**
     * AuthenticationListener constructor.
     *
     * @param LoginManagerInterface $loginManager
     * @param string                $firewallName
     */
    public function __construct(LoginManagerInterface $loginManager, $firewallName)
    {
        $this->loginManager = $loginManager;
        $this->firewallName = $firewallName;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            UserEvents::REGISTRATION_COMPLETED => 'authenticate',
            UserEvents::REGISTRATION_CONFIRMED => 'authenticate',
            UserEvents::RESETTING_RESET_COMPLETED => 'authenticate',
        );
    }

    /**
     * @param FilterUserResponseEvent  $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function authenticate(FilterUserResponseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        try {
            $this->loginManager->logInUser($this->firewallName, $event->getUser(), $event->getResponse());

            $eventDispatcher->dispatch(UserEvents::SECURITY_IMPLICIT_LOGIN, new UserEvent($event->getUser(), $event->getRequest()));
        } catch (AccountStatusException $ex) {
            // We simply do not authenticate users which do not pass the user
            // checker (not enabled, expired, etc.).
        }
    }
}