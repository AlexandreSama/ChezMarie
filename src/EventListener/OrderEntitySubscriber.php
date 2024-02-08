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
    private $SMSState;
    private $logger;

    public function __construct(SMSState $SMSState, LoggerInterface $logger)
    {
        $this->SMSState = $SMSState;
        $this->logger = $logger;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
        ];
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Order) {
            return;
        }

        if ($entity instanceof Order) {
            $this->logger->info('L\'événement preUpdate a été déclenché.', [
                'entity' => get_class($entity),
                'id' => $entity->getId(),
            ]);
        }

        if ($args->hasChangedField('is_pending') && $args->getNewValue('is_pending') === true) {
            $this->SMSState->sendMessage(
                $entity->getPhone(),
                "Vous pouvez désormais récupérer votre commande Chez Marie en magasin !"
            );
        }
    }
    
}