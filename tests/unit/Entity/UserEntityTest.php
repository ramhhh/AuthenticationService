<?php

namespace App\Tests\unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserEntityTest extends KernelTestCase
{
    public function testCreateUserWithUsername() {
        $user = new User();
        $user->setUsername('Newuser');

        $this->assertNull($user->getId());
        $this->assertEquals('Newuser',$user->getUsername());
        $this->assertIsArray($user->getRoles());
        $this->assertContains('ROLE_USER',$user->getRoles());
    }

    public function testRoleChanging() {
        $user = new User();

        $this->assertIsArray($user->getRoles());
        $this->assertEquals(['ROLE_USER'],$user->getRoles());

        $user->setRoles(['ROLE_ADMIN']);

        $this->assertIsArray($user->getRoles());
        $this->assertContains('ROLE_ADMIN',$user->getRoles());
        $this->assertContains('ROLE_USER',$user->getRoles());
    }

    public function testEncodingPassword() {
        $user = new User();
        self::bootKernel();
        $passwordEncoder = self::$container->get('security.password_encoder');

        $user->setPassword($passwordEncoder->encodePassword(
            $user,
            'testpassword'
        ));

        $this->assertNotEquals('testpassword',$user->getPassword());

        $this->assertTrue($passwordEncoder->isPasswordValid($user,'testpassword'));

        $this->assertNull($user->getSalt());
        $this->assertNull($user->eraseCredentials());
    }

}