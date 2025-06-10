<?php
// src/Repository/TecnicosRepository.php

namespace App\Repository;

use App\Entity\Tecnicos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tecnicos>
 */
class TecnicosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tecnicos::class);
    }

    public function save(Tecnicos $entity, bool $flush = false): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Tecnicos $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findOneByArbitroAndConvocatoria($arbitro, int $convocatoria): ?Tecnicos
    {
        return $this->createQueryBuilder('t')
            ->join('t.session', 's')
            ->where('t.arbitro = :arb')
            ->andWhere('s.examNumber = :conv')
            ->setParameter('arb', $arbitro)
            ->setParameter('conv', $convocatoria)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function truncate(): void
    {
        $this->getEntityManager()
            ->createQuery('DELETE FROM App\Entity\Tecnicos')
            ->execute();
    }

}
