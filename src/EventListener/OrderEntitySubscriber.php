<?php

namespace App\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use App\Entity\Order;
use App\Service\SMSState;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Log\LoggerInterface;

class OrderEntitySubscriber implements EventSubscriber
{

    //propriété privé de la classe
    private $SMSState;
    private $logger;

    //Constructeur indiquant que j'appelle la classe SMSState pour le Client Twilio 
    //Et loggerInterface pour les logs 
    public function __construct(SMSState $SMSState, LoggerInterface $logger)
    {
        $this->SMSState = $SMSState;
        $this->logger = $logger;
    }

    //Ici on récupère l'événement preUpdate de Doctrine
    //C'est un événement qui s'enclenche avant l'envoi
    //D'une modification Doctrine
    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
        ];
    }

    //Cet fonction récupère l'objet qui va être mis a jour
    //Et vérifie que c'est bien un objet de type Order
    //Si oui, je log en disant que l'événement est bien passé
    //Puis je vérifie que c'est bien le champ "is_pending" qui
    //Change et passe a true avant d'envoyer un message
    //Si non, je return dans le vide
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Order) {
            return;
        }else{
            $this->logger->info('L\'événement preUpdate a été déclenché.', [
                'entity' => get_class($entity),
                'id' => $entity->getId(),
            ]);

            if ($args->hasChangedField('is_pending') && $args->getNewValue('is_pending') === true) {
                $this->SMSState->sendMessage(
                    $entity->getPhone(),
                    "Vous pouvez désormais récupérer votre commande Chez Marie en magasin !"
                );
            }
        }
    }
    
}