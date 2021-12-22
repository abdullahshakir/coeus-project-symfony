<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
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

    public function findCartsNotModifiedSince(\DateTime $limitDate, int $limit = 10): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.status = :status')
            ->andWhere('o.updatedAt < :date')
            ->setParameter('status', Order::STATUS_CART)
            ->setParameter('date', $limitDate)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNonNotifiedDeliveredOrders(): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.status = :status')
            ->andWhere('o.isNotified = :isNotified')
            ->setParameter('status', Order::STATUS_DELIVERED)
            ->setParameter('isNotified', false)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findConfirmedOrders(): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.status = :status')
            ->andWhere('o.isConfirmed = :isConfirmed')
            ->setParameter('status', Order::STATUS_INPROGRESS)
            ->setParameter('isConfirmed', false)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByIn($field, $value)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where($qb->expr()->In('p.'.$field, '?1'));
        $qb->setParameter(1, $value);

        return $qb->getQuery()
            ->getResult();
    }

    public function findByNot($field, $value): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where($qb->expr()->not($qb->expr()->eq('p.'.$field, '?1')));
        $qb->setParameter(1, $value);

        return $qb->getQuery()
            ->getResult();
    }
}
