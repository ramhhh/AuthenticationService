<?php


namespace App\Security;


use App\Security\Authenticators\AuthenticatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class AuthenticatorHandler
{
    /** @var AuthenticatorInterface[] $authenticators */
    private $authenticators;

    public function __construct(iterable $authenticators)
    {
        $this->authenticators = $authenticators;
    }

    public function authenticateRequest(Request $request) {
        foreach($this->authenticators as $authenticator) {
            if($authenticator->supports($request)) {
                $credentials = $authenticator->getCredentials($request);
                $user = $authenticator->getUser($credentials);
                if($authenticator->checkCredentials($credentials,$user)) return $user;
                else throw new BadCredentialsException();
            }
        }
        return null;
    }
}