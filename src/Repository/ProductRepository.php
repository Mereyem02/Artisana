<?php

namespace App\Repository;

use App\Entity\Artisan;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    
    public function findDuplicateBySlug(string $slug, ?int $excludeId = null): ?Product
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug);

        if ($excludeId) {
            $qb->andWhere('p.id != :id')
               ->setParameter('id', $excludeId);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return Product[]
     */
    public function findByArtisan(Artisan $artisan): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.artisans', 'a')
            ->andWhere('a = :artisan')
            ->setParameter('artisan', $artisan)
            ->orderBy('p.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Optionally enforce unique (titre, cooperative) pair
     */
    public function findDuplicateByTitreAndCooperative(string $titre, ?int $cooperativeId, ?int $excludeId = null): ?Product
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.titre = :titre')
            ->setParameter('titre', $titre);

        if ($cooperativeId !== null) {
            $qb->andWhere('p.cooperative = :coop')
               ->setParameter('coop', $cooperativeId);
        }

        if ($excludeId) {
            $qb->andWhere('p.id != :id')
               ->setParameter('id', $excludeId);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findDuplicateByTitreForArtisan(string $titre, Artisan $artisan, ?int $excludeId = null): ?Product
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.artisans', 'a')
            ->where('p.titre = :titre')
            ->andWhere('a = :artisan')
            ->setParameter('titre', $titre)
            ->setParameter('artisan', $artisan);

        if ($excludeId) {
            $qb->andWhere('p.id != :id')
               ->setParameter('id', $excludeId);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
