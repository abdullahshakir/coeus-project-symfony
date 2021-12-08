<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\OrderProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderProduct[]    findAll()
 * @method OrderProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderProduct::class);
    }

    // /**
    //  * @return OrderProduct[] Returns an array of OrderProduct objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OrderProduct
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getTotalSales(Product $product)
    {
        return $this->createQueryBuilder('op')
        ->select('sum(op.quantity) as totalSales')
        ->where('op.product_id = :productId')
        ->setParameter('productId', $product->getId())
        ->getQuery()
        ->getSingleScalarResult();
    }

    public function getOrderIdsHavingProducts($products)
    {
        return $this->createQueryBuilder('op')
        ->select('DISTINCT op.order_id')
        ->where('op.product_id IN (:products)')
        ->setParameter('products', $products)
        ->getQuery()
        ->getResult();
    }

    public function getOrderProductsHavingProducts($orderId, $products)
    {
        return $this->createQueryBuilder('op')
        ->andWhere('op.order_id = :orderId')
        ->andWhere('op.product_id IN (:products)')
        ->setParameter('orderId', $orderId)
        ->setParameter('products', $products)
        ->getQuery()
        ->getResult();
    }
}
