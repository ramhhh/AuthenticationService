<?php

namespace App\Security\Authenticators;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\UserNotFoundException;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class JWTAuthenticator implements AuthenticatorInterface
{
    private JWTEncoderInterface $JWTEncoder;
    private EntityManagerInterface $entityManager;

    public function __construct(JWTEncoderInterface $JWTEncoder, EntityManagerInterface $entityManager)
    {
        $this->JWTEncoder = $JWTEncoder;
        $this->entityManager = $entityManager;
    }

    public function supports(Request $request)
    {
        $extractor = new AuthorizationHeaderTokenExtractor(
            'Bearer',
            'Authorization'
        );

        return $extractor->extract($request) !== false;
    }

    public function getCredentials(Request $request)
    {
        $extractor = new AuthorizationHeaderTokenExtractor(
            'Bearer',
            'Authorization'
        );

        return $extractor->extract($request);
    }

    public function getUser($credentials)
    {
        $data = $this->JWTEncoder->decode($credentials);

        $username = $data['username'];

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username'=>$username]);

        if(!$user) {
            throw new UsernameNotFoundException('User not found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, User $user)
    {
        return true;
    }
}
