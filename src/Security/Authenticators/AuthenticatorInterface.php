<?php


namespace App\Security\Authenticators;


use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

interface AuthenticatorInterface
{
    function supports(Request $request);
    function getCredentials(Request $request);
    function getUser($credentials);
    function checkCredentials($credentials, User $user);
}