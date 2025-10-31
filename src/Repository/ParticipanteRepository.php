<?php

namespace App\Repository;

use App\Entity\Participante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repositorio de Participante: utilidades de búsqueda y validación por sorteo.
 * @extends ServiceEntityRepository<Participante>
 */
class ParticipanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participante::class);
    }

    public function codigoExisteEnSorteo(string $codigo, int $sorteoId): bool
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.codigoEntrada = :codigo')
            ->andWhere('IDENTITY(p.sorteo) = :sorteoId')
            ->setParameter('codigo', $codigo)
            ->setParameter('sorteoId', $sorteoId)
            ->getQuery()
            ->getOneOrNullResult() !== null;
    }

//    /**
//     * @return Participante[] Returns an array of Participante objects
//     */
//    public function findByExampleField(\$value): array
//    {
//        return \$this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', \$value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField(\$value): ?Participante
//    {
//        return \$this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', \$value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
