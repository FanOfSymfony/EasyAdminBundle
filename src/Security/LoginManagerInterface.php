<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\Security;

use FanOfSymfony\Bundle\EasyAdminBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\Response;

interface LoginManagerInterface
{
    /**
     * @param string        $firewallName
     * @param UserInterface $user
     * @param Response|null $response
     */
    public function logInUser($firewallName, UserInterface $user, Response $response = null);
}