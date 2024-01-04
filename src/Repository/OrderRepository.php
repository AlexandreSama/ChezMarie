<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function getOngoingOrders()
    {
        // Commencez par obtenir le QueryBuilder
        $qb = $this->createQueryBuilder('o');  // 'o' est un alias que vous utiliserez pour vous référer à Order dans la requête

        // Construire la requête avec des conditions 'OU'
        $qb->where('o.is_preparing = :preparing')
            ->orWhere('o.is_pending = :pending')
            ->setParameters([
                'preparing' => true,
                'pending' => true
            ]);

        // Exécutez la requête et obtenez les résultats
        return $qb->getQuery()->getResult();
    }

    public function getClosedOrders()
    {
        // Commencez par obtenir le QueryBuilder
        $qb = $this->createQueryBuilder('o');  // 'o' est un alias que vous utiliserez pour vous référer à Order dans la requête

        // Construire la requête avec des conditions 'OU'
        $qb->where('o.is_served = :served')
            ->orWhere('o.is_notServer = :notServed')
            ->setParameters([
                'served' => true,
                'notServed' => true
            ]);

        // Exécutez la requête et obtenez les résultats
        return $qb->getQuery()->getResult();
    }
}
