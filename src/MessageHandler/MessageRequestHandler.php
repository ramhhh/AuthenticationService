<?php


namespace App\MessageHandler;


use App\MessageHandler\RequestHandler\AuthenticationUserRegisterHandler;
use App\Messenger\MessageRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MessageRequestHandler implements MessageHandlerInterface
{
    private static $handlers = [
        'authentication_user_register' => AuthenticationUserRegisterHandler::class
    ];
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {

        $this->container = $container;
    }

    public function __invoke(MessageRequest $request)
    {
        if(isset(MessageRequestHandler::$handlers[$request->getType()])) {
            /** @var AuthenticationUserRegisterHandler $handler */
            $handler = $this->container->get(MessageRequestHandler::$handlers[$request->getType()]);
            $handler($request);
        }
        else {
            throw new \Exception('Not implemented type '.$request->getType());
        }
    }
}