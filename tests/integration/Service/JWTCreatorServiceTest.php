<?php


namespace App\Tests\integration\Service;


use App\Entity\User;
use App\Tests\integration\IntegrationTestCase;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class JWTCreatorServiceTest extends IntegrationTestCase
{
    public function testGoodCredentials() {
        $user = new User();
        $user->setUsername('testuser');
        $user->setPassword($this->getUserPasswordEncoder()->encodePassword($user,'testpassword'));

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $token = $this->getJWTCreator()->getNewToken($user->getUsername(),'testpassword');
        $this->assertNotEquals('',$token);
    }

    public function testBadUsername() {
        $user = new User();
        $user->setUsername('testuser');
        $user->setPassword($this->getUserPasswordEncoder()->encodePassword($user,'testpassword'));

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $this->expectException(UsernameNotFoundException::class);
        $this->getJWTCreator()->getNewToken($user->getUsername().'aaa','testpassword');
    }

    public function testBadPassword() {
        $user = new User();
        $user->setUsername('testuser');
        $user->setPassword($this->getUserPasswordEncoder()->encodePassword($user,'testpassword'));

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $this->expectException(BadCredentialsException::class);
        $this->getJWTCreator()->getNewToken($user->getUsername(),'bbb');
    }
}