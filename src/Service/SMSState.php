<?php

namespace App\Service;

use Twilio\Rest\Client;

class SMSState
{
    private $client;

    public function __construct()
    {
        $this->client = new Client('AC8730b5d0c520f4a516c35f1b5e51b914', '91bd384b72f7a672053b7be56b9f7de5');
    }

    public function sendMessage(string $to, string $message)
    {
        $this->client->messages->create(
            $to,
            [
                'from' => $_ENV['TWILIO_PHONE_NUMBER'],
                'body' => $message,
            ]
        );
    }
}