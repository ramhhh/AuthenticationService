<?php


namespace App\Tests\unit\Entity;


use PHPUnit\Framework\TestCase;

class UserEntityTest extends TestCase
{
    public function testCreateUserWithUsername() {
        $user = new User();
        $user->setUsername('Newuser');

        $this->assertEquals('newuser',$user->getUsername());
        $this->assertIsArray($user->getRoles());
        $this->assertContains('ROLE_USER',$user->getRoles());

        $this->assertNull($user->getPassword());
        $this->assertNull($user->getSalt());

    }
}