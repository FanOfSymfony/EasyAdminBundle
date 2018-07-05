<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\EventListener;

//use IDaas\CoreBundle\Event\LoginCidadaoCoreEvents;
//use IDaas\CoreBundle\Service\RegisterRequestedScope;
//use IDaas\TaskStackBundle\Event\EntryPointStartEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;

class SecurityEntryPoint implements AuthenticationEntryPointInterface
{
    /** @var HttpUtils */
    private $httpUtils;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var RegisterRequestedScope */
    private $registerRequestedScopeService;

    /**
     * LoginEntryPoint constructor.
     * @param HttpUtils $httpUtils
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        HttpUtils $httpUtils,
        EventDispatcherInterface $dispatcher
    ) {
        $this->httpUtils = $httpUtils;
        $this->dispatcher = $dispatcher;
    }

    public function start(Request $request, AuthenticationException $authenticationException = null)
    {
//        $event = new EntryPointStartEvent($request, $authenticationException);
//        $this->dispatcher->dispatch(LoginCidadaoCoreEvents::AUTHENTICATION_ENTRY_POINT_START, $event);
//
//        $this->registerRequestedScopeService->registerRequestedScope($request);

        return $this->httpUtils->createRedirectResponse($request, 'easy_admin_security_login');
    }
}