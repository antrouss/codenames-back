<?php

namespace App\Repository;

use App\Entity\Words;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Words|null find($id, $lockMode = null, $lockVersion = null)
 * @method Words|null findOneBy(array $criteria, array $orderBy = null)
 * @method Words[]    findAll()
 * @method Words[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WordsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Words::class);
    }

    // /**
    //  * @return Words[] Returns an array of Words objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Words
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
