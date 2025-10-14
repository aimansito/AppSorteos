<?php

namespace App\Repository;

use App\Entity\Sorteo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sorteo>
 */
class SorteoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sorteo::class);
    }

    public function findActivos(): array
    {
        $ahora = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Madrid')); 
        return $this->createQueryBuilder('s')
            ->where('s.fecha > :ahora')
            ->andWhere('s.activo = :activo')
            ->setParameter('ahora', $ahora)
            ->setParameter('activo', true)
            ->orderBy('s.fecha', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findInactivos(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.activo = :activo')
            ->setParameter('activo', false)
            ->orderBy('s.fecha', 'DESC')
            ->getQuery()
            ->getResult();
    }


    //    /**
    //     * @return Sorteo[] Returns an array of Sorteo objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sorteo
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
