<?php

namespace App\Service;

use Twilio\Rest\Client;

class SMSState
{

    //Propriété privé de la class
    private $client;

    //Constructeur indiquant que la propriété client est
    //Le client de la dépendance Twilio
    public function __construct()
    {
        $this->client = new Client($_ENV['TWILIO_SIO'], $_ENV['TWILIO_AUTH']);
    }

    //Fonction envoyant un message et demandant un numéro de téléphone
    //Et un message. Le from est récupéré depuis le .env
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