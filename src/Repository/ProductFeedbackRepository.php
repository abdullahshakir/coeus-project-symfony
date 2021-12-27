<?php

namespace App\Repository;

use App\Entity\ProductFeedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Criteria;

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

    public function findTopRatedProductFeedbacks()
    {
        $queryBuilder = $this->createQueryBuilder('pf')
            ->select('pf')
            ->orderBy('pf.rating', 'DESC')
        ;
        $criteria = Criteria::create()
            ->setFirstResult(0)
            ->setMaxResults(5);
        $queryBuilder->addCriteria($criteria);

        return $queryBuilder->getQuery()->getResult();
    }
}
