<?php

namespace App\Tests\unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    public function testUsernameConstraints() {
        self::bootKernel();
        $validator = self::$container->get('validator');

        $user1 = new User();

        $user1->setUsername('');
        $violationList = $validator->validate($user1);
        $this->assertEquals(3,$violationList->count());
        $this->assertEquals('This value should not be blank.',$violationList->get(0)->getMessage());
        $this->assertEquals('username',$violationList->get(0)->getPropertyPath());
        $this->assertEquals('This value is too short. It should have 4 characters or more.',$violationList->get(1)->getMessage());
        $this->assertEquals('username',$violationList->get(1)->getPropertyPath());
        $this->assertEquals('This value should be of type alnum.',$violationList->get(2)->getMessage());
        $this->assertEquals('username',$violationList->get(2)->getPropertyPath());

        $user1->setUsername('abc');
        $violationList = $validator->validate($user1);
        $this->assertEquals(1,$violationList->count());
        $this->assertEquals('This value is too short. It should have 4 characters or more.',$violationList->get(0)->getMessage());
        $this->assertEquals('username',$violationList->get(0)->getPropertyPath());

        $user1->setUsername('0123456789012345678901234567890123456789');
        $violationList = $validator->validate($user1);
        $this->assertEquals(1,$violationList->count());
        $this->assertEquals('This value is too long. It should have 32 characters or less.',$violationList->get(0)->getMessage());
        $this->assertEquals('username',$violationList->get(0)->getPropertyPath());

        $user1->setUsername('composed username');
        $violationList = $validator->validate($user1);
        $this->assertEquals(1,$violationList->count());
        $this->assertEquals('This value should be of type alnum.',$violationList->get(0)->getMessage());
        $this->assertEquals('username',$violationList->get(0)->getPropertyPath());

        $user1->setUsername('Sp3c*4L');
        $violationList = $validator->validate($user1);
        $this->assertEquals(1,$violationList->count());
        $this->assertEquals('This value should be of type alnum.',$violationList->get(0)->getMessage());
        $this->assertEquals('username',$violationList->get(0)->getPropertyPath());

        $user1->setUsername('Sp3cI4L');
        $violationList = $validator->validate($user1);
        $this->assertEquals(0,$violationList->count());

        $user1->setUsername('normalUsername');
        $violationList = $validator->validate($user1);
        $this->assertEquals(0,$violationList->count());

        $user2 = new User();

        // Shouldn't get an error for duplicated username because value is not persisted in database
        $user2->setUsername('normalUsername');
        $violationList = $validator->validate($user2);
        $this->assertEquals(0,$violationList->count());

    }
}