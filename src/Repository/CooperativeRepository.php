<?php

namespace App\Repository;

use App\Entity\Cooperative;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cooperative>
 */
class CooperativeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cooperative::class);
    }

  
    public function findDuplicateByEmail(string $email, ?int $excludeId = null): ?Cooperative
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.email = :email')
            ->setParameter('email', $email);
        
        if ($excludeId) {
            $qb->andWhere('c.id != :id')
               ->setParameter('id', $excludeId);
        }
        
        return $qb->getQuery()->getOneOrNullResult();
    }


    public function findDuplicateByTelephone(string $telephone, ?int $excludeId = null): ?Cooperative
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.telephone = :telephone')
            ->setParameter('telephone', $telephone);
        
        if ($excludeId) {
            $qb->andWhere('c.id != :id')
               ->setParameter('id', $excludeId);
        }
        
        return $qb->getQuery()->getOneOrNullResult();
    }

   
    public function findDuplicateBySiteWeb(string $siteWeb, ?int $excludeId = null): ?Cooperative
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.siteWeb = :siteWeb')
            ->setParameter('siteWeb', $siteWeb);
        
        if ($excludeId) {
            $qb->andWhere('c.id != :id')
               ->setParameter('id', $excludeId);
        }
        
        return $qb->getQuery()->getOneOrNullResult();
    }

    //    /**
    //     * @return Cooperative[] Returns an array of Cooperative objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Cooperative
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
