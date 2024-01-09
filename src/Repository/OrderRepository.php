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

    /**
     * The function `getOngoingOrders` retrieves ongoing orders that are either in the preparing or
     * pending state.
     * 
     * @return a list of ongoing orders.
     */
    public function getOngoingOrders()
    {
        $qb = $this->createQueryBuilder('o'); 

        $qb->where('o.is_preparing = :preparing')
            ->orWhere('o.is_pending = :pending')
            ->setParameters([
                'preparing' => true,
                'pending' => true
            ]);

        return $qb->getQuery()->getResult();
    }

    public function getSpecificOngoingOrders($userId)
    {
        $qb = $this->createQueryBuilder('o');

        $qb->where('o.userid = :userId')
            ->andWhere($qb->expr()->orX(
                'o.is_preparing = :preparing',
                'o.is_pending = :pending'
            ))
            ->setParameters([
                'userId' => $userId,
                'preparing' => true,
                'pending' => true
            ]);

        return $qb->getQuery()->getResult();
    }

    /**
     * The function `getClosedOrders` retrieves closed orders from the database based on whether they
     * have been served or not.
     * 
     * @return the result of the query executed on the database.
     */
    public function getClosedOrders()
    {
        $qb = $this->createQueryBuilder('o');

        $qb->where('o.is_served = :served')
            ->orWhere('o.is_notServer = :notServed')
            ->setParameters([
                'served' => true,
                'notServed' => true
            ]);

        return $qb->getQuery()->getResult();
    }

    public function getSpecificClosedOrders($userId)
    {
        $qb = $this->createQueryBuilder('o');

        $qb->where('o.userid = :userId')
            ->andWhere($qb->expr()->orX(
                'o.is_served = :served',
                'o.is_notServer = :notServed'
            ))
            ->setParameters([
                'userId' => $userId,
                'served' => true,
                'notServed' => true
            ]);

        return $qb->getQuery()->getResult();
    }
}
