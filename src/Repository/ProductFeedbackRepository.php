<?php

namespace App\Repository;

use App\Entity\ProductFeedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductFeedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductFeedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductFeedback[]    findAll()
 * @method ProductFeedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductFeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductFeedback::class);
    }

}
