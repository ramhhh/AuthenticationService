<?php


namespace App\Tests\integration\Entity;


use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\integration\IntegrationTestCase;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserEntityTest extends IntegrationTestCase
{
    public function testPersistingUser() {
        $user = new User();
        $user->setUsername('testUsername');
        $user->setPassword($this->getUserPasswordEncoder()->encodePassword($user,'testPassword'));

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $this->assertEquals(1,$this->getEntityManager()->getRepository(User::class)->count([]));
    }

    public function testDuplicatedValidatorUser() {
        $validator = self::$container->get('validator');

        $user = new User();
        $user->setUsername('testUsername');
        $user->setPassword($this->getUserPasswordEncoder()->encodePassword($user,'testPassword'));

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $user2 = new User();
        $user2->setUsername('testUsername');
        $user2->setPassword($this->getUserPasswordEncoder()->encodePassword($user2,'testPassword'));

        $violationList = $validator->validate($user2);
        $this->assertEquals(1,$violationList->count());
        $this->assertEquals('This value is already used.',$violationList->get(0)->getMessage());
        $this->assertEquals('username',$violationList->get(0)->getPropertyPath());
    }

    public function testUpgradePassword() {
        $user = new User();
        $user->setUsername('testUsername');
        $user->setPassword($this->getUserPasswordEncoder()->encodePassword($user,'testPassword'));

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $this->getEntityManager()->refresh($user);
        $this->assertTrue($this->getUserPasswordEncoder()->isPasswordValid($user,'testPassword'));

        $this->getEntityManager()->getRepository(User::class)->upgradePassword($user,$this->getUserPasswordEncoder()->encodePassword($user,'newpassword'));

        $this->getEntityManager()->refresh($user);
        $this->assertFalse($this->getUserPasswordEncoder()->isPasswordValid($user,'testPassword'));
        $this->assertTrue($this->getUserPasswordEncoder()->isPasswordValid($user,'newpassword'));

        $this->expectException(UnsupportedUserException::class);
        $nonAppUser = new \Symfony\Component\Security\Core\User\User('username','password');
        $this->getEntityManager()->getRepository(User::class)->upgradePassword($nonAppUser,'password');

    }
}