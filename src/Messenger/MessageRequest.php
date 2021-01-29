<?php


namespace App\Messenger;


class MessageRequest extends Message
{
    public function __construct(string $input, string $clientIp, string $type, string $id)
    {
        parent::__construct($input, $clientIp, $type);

        $this->id = $id;
    }

    public function createResponse(): MessageResponse {
        return new MessageResponse($this->input,$this->clientIp,$this->type,$this->id,json_encode([]));
    }
}