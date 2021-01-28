<?php


namespace App\Security\Authenticators;


use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

interface AuthenticatorInterface
{
    public function supports(Request $request);
    public function getCredentials(Request $request);
    public function getUser($credentials);
    public function checkCredentials($credentials, User $user);
}
