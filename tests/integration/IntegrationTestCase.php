<?php


namespace App\Tests\integration;


use App\Entity\User;
use App\Security\AuthenticatorHandler;
use App\Service\JWTCreator;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class IntegrationTestCase extends KernelTestCase
{
    private EntityManagerInterface|null $entityManager;

    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $this->entityManager = self::$container->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }

    public function getUserPasswordEncoder() {
        return self::$container->get('security.password_encoder');
    }

    public function getEntityManager() {
        return $this->entityManager;
    }

    public function getAuthenticatorHandler() {
        return self::$container->get(AuthenticatorHandler::class);
    }

    public function getJWTCreator() {
        return self::$container->get(JWTCreator::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}