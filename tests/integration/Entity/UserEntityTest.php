<?php


namespace App\Tests\integration\Entity;


use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserEntityTest extends KernelTestCase
{
    private UserPasswordEncoder|null $passwordEncoder;
    private EntityManager|null $entityManager;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->passwordEncoder = self::$container->get('security.password_encoder');
        $this->entityManager = self::$container->get('doctrine.orm.entity_manager');
        $this->userRepository = $this->entityManager->getRepository(User::class);

        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }


    public function testPersistingUser() {
        $user = new User();
        $user->setUsername('testUsername');
        $user->setPassword($this->passwordEncoder->encodePassword($user,'testPassword'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->assertEquals(1,$this->userRepository->count([]));
    }

    public function testDuplicatedValidatorUser() {
        $validator = self::$container->get('validator');

        $user = new User();
        $user->setUsername('testUsername');
        $user->setPassword($this->passwordEncoder->encodePassword($user,'testPassword'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $user2 = new User();
        $user2->setUsername('testUsername');
        $user2->setPassword($this->passwordEncoder->encodePassword($user2,'testPassword'));

        $violationList = $validator->validate($user2);
        $this->assertEquals(1,$violationList->count());
        $this->assertEquals('This value is already used.',$violationList->get(0)->getMessage());
        $this->assertEquals('username',$violationList->get(0)->getPropertyPath());
    }

    public function testUpgradePassword() {
        $user = new User();
        $user->setUsername('testUsername');
        $user->setPassword($this->passwordEncoder->encodePassword($user,'testPassword'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->entityManager->refresh($user);
        $this->assertTrue($this->passwordEncoder->isPasswordValid($user,'testPassword'));

        $this->userRepository->upgradePassword($user,$this->passwordEncoder->encodePassword($user,'newpassword'));

        $this->entityManager->refresh($user);
        $this->assertFalse($this->passwordEncoder->isPasswordValid($user,'testPassword'));
        $this->assertTrue($this->passwordEncoder->isPasswordValid($user,'newpassword'));

        $this->expectException(UnsupportedUserException::class);
        $nonAppUser = new \Symfony\Component\Security\Core\User\User('username','password');
        $this->userRepository->upgradePassword($nonAppUser,'password');

    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}