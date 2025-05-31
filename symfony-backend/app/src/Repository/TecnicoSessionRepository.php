<?php
// src/Repository/TecnicoSessionRepository.php

namespace App\Repository;

use App\Entity\TecnicoSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TecnicoSession>
 */
class TecnicoSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TecnicoSession::class);
    }

    public function save(TecnicoSession $entity, bool $flush = false): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(TecnicoSession $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // Aquí puedes añadir tus métodos personalizados, por ejemplo:
    // /**
    //  * @return TecnicoSession[] Returns an array of sessions for una categoría y examen dado
    //  */
    // public function findByCategoryAndExam($categoria, int $examNumber): array
    // {
    //     return $this->createQueryBuilder('s')
    //         ->andWhere('s.categoria = :cat')
    //         ->andWhere('s.examNumber = :num')
    //         ->setParameters(['cat' => $categoria, 'num' => $examNumber])
    //         ->orderBy('s.fecha', 'DESC')
    //         ->getQuery()
    //         ->getResult()
    //     ;
    // }
}
