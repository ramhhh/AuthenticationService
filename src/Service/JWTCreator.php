<?php


namespace App\Service;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class JWTCreator
{
    private EntityManagerInterface $entityManager;
    private UserPasswordEncoderInterface $passwordEncoder;
    private JWTEncoderInterface $JWTEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, JWTEncoderInterface $JWTEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->JWTEncoder = $JWTEncoder;
    }

    public function getNewToken($username, $password) {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username'=>$username]);

        if(!$user) {
            throw new UsernameNotFoundException();
        }

        if(!$this->passwordEncoder->isPasswordValid($user,$password)) {
            throw new BadCredentialsException();
        }

        $token = $this->JWTEncoder->encode([
            'username' => $username
        ]);

        return $token;
    }
}