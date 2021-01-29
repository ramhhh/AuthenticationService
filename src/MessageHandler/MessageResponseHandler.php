<?php


namespace App\MessageHandler;


use App\Messenger\MessageResponse;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class MessageResponseHandler implements MessageHandlerInterface
{

    public function __invoke(MessageResponse $response)
    {

    }
}