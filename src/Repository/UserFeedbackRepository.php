<?php

namespace App\Repository;

use App\Entity\UserFeedback;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Criteria;

/**
 * @method UserFeedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserFeedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserFeedback[]    findAll()
 * @method UserFeedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserFeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFeedback::class);
    }

    public function getAverageRating(User $user)
    {
        return $this->createQueryBuilder('uf')
        ->select('avg(uf.rating) as averageRating')
        ->where('uf.userId = :userId')
        ->setParameter('userId', $user->getId())
        ->getQuery()
        ->getSingleScalarResult();
    }

    public function findTopRatedSellerFeedbacks()
    {
        $queryBuilder = $this->createQueryBuilder('uf')
            ->select('uf')
            ->orderBy('uf.rating', 'DESC')
        ;
        $criteria = Criteria::create()
            ->setFirstResult(0)
            ->setMaxResults(5);
        $queryBuilder->addCriteria($criteria);

        return $queryBuilder->getQuery()->getResult();
    }
}
