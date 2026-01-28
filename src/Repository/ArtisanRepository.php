<?php

namespace App\Repository;

use App\Entity\Artisan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Artisan>
 */
class ArtisanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artisan::class);
    }

       public function findDuplicateByEmail(string $email, ?int $excludeId = null): ?Artisan
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.email = :email')
            ->setParameter('email', $email);
        
        if ($excludeId) {
            $qb->andWhere('a.id != :id')
               ->setParameter('id', $excludeId);
        }
        
        return $qb->getQuery()->getOneOrNullResult();
    }

   
    public function findDuplicateByTelephone(string $telephone, ?int $excludeId = null): ?Artisan
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.telephone = :telephone')
            ->setParameter('telephone', $telephone);
        
        if ($excludeId) {
            $qb->andWhere('a.id != :id')
               ->setParameter('id', $excludeId);
        }
        
        return $qb->getQuery()->getOneOrNullResult();
    }

    //    /**
    //     * @return Artisan[] Returns an array of Artisan objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Artisan
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
