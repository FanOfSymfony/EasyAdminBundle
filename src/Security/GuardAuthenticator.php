<?php
namespace FanOfSymfony\Bundle\EasyAdminBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class GuardAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * NOTE: I chose to throw an HTTP Exception here to let the response be rendered elsewhere -
     *       separation of concerns and all... You could always return a JsonResponse here.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = 'Invalid Credentials';
        if ($exception instanceof CustomAuthenticationException) {
            $message = $exception->getMessageKey();
        }
        throw new HttpException(401, $message);
    }
    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        // noop
    }
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // noop
    }
    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }
}