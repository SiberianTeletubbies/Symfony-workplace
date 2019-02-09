<?php

namespace App\Repository;

use App\Entity\TaskAdditionalData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TaskAdditionalData|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskAdditionalData|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskAdditionalData[]    findAll()
 * @method TaskAdditionalData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskAdditionalDataRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TaskAdditionalData::class);
    }

    // /**
    //  * @return TaskAdditionalData[] Returns an array of TaskAdditionalData objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TaskAdditionalData
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
