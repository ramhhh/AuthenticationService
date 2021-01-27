<?php


namespace App\Tests\integration\Security;


use App\Entity\User;
use App\Tests\integration\IntegrationTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class AuthenticatorHandlerTest extends IntegrationTestCase
{
    public function testGoodUserAuthentication() {
        $user = new User();
        $user->setUsername('testuser');
        $user->setPassword($this->getUserPasswordEncoder()->encodePassword($user,'testpassword'));

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $token = $this->getJWTCreator()->getNewToken($user->getUsername(),'testpassword');
        $this->assertNotEquals('',$token);

        $request = new Request();
        $request->headers->set('Content-Type','application/json');
        $request->headers->set('Authorization','Bearer '.$token);

        $userAuthenticated = $this->getAuthenticatorHandler()->authenticateRequest($request);

        $this->assertEquals($user,$userAuthenticated);
    }

    public function testNotSupportedRequest() {
        $request = new Request();

        $this->assertNull($this->getAuthenticatorHandler()->authenticateRequest($request));
    }

    public function testBadToken() {
        $user = new User();
        $user->setUsername('testuser');
        $user->setPassword($this->getUserPasswordEncoder()->encodePassword($user,'testpassword'));

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $token = $this->getJWTCreator()->getNewToken($user->getUsername(),'testpassword');
        $this->assertNotEquals('',$token);

        $request = new Request();
        $request->headers->set('Content-Type','application/json');
        $request->headers->set('Authorization','Bearer '.$token.'aaaabbbb');

        $this->expectException(JWTDecodeFailureException::class);
        $this->getAuthenticatorHandler()->authenticateRequest($request);
    }

    public function testUserNotFound() {
        $user = new User();
        $user->setUsername('testuser');
        $user->setPassword($this->getUserPasswordEncoder()->encodePassword($user,'testpassword'));

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $token = $this->getJWTCreator()->getNewToken($user->getUsername(),'testpassword');
        $this->assertNotEquals('',$token);

        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();

        $request = new Request();
        $request->headers->set('Content-Type','application/json');
        $request->headers->set('Authorization','Bearer '.$token);

        $this->expectException(UsernameNotFoundException::class);
        $this->getAuthenticatorHandler()->authenticateRequest($request);
    }
}