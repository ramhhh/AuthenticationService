<?php


namespace App\MessageHandler\RequestHandler;


use App\Entity\User;
use App\Messenger\MessageRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthenticationUserRegisterHandler
{
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $messageBus;
    private UserPasswordEncoderInterface $passwordEncoder;
    private ValidatorInterface $validator;
    private Serializer $serializer;

    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $messageBus, UserPasswordEncoderInterface $passwordEncoder, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->passwordEncoder = $passwordEncoder;
        $this->validator = $validator;

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function __invoke(MessageRequest $messageRequest)
    {
        $input = json_decode($messageRequest->getInput(),true);
        foreach(['username','passowrd'] as $key) {
            if(!isset($input[$key])) {
                $response = $messageRequest->createResponse();
                $response->setOutput($this->serializer->serialize(['error' => sprintf('Missing the %s key',$key)],'json'));
                $this->messageBus->dispatch($response);
                return;
            }
        }
        $user = new User();
        $user->setUsername($input['username']);
        $user->setPassword($this->passwordEncoder->encodePassword($user,$input['password']));

        $violations = $this->validator->validate($user);

        if($violations->count() === 0) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $response = $messageRequest->createResponse();
        $response->setOutput($this->serializer->serialize([
            'user' => $user,
            'violation_list' => $violations
        ],'json'));

        $this->messageBus->dispatch($response);
    }
}