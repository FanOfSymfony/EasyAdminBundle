<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SecurityController extends Controller
{
    /**
     * Login action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            '@EasyAdmin/security/login.html.twig',
            [
                'error' => $error,
                'lastUsername' => $lastUsername,
                'easyadminConfig' => $this->getParameter('easyadmin.config'),
            ]
        );
    }
}