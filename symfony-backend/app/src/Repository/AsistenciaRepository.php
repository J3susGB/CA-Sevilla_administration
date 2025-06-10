<?php

// src/Repository/AsistenciaRepository.php
namespace App\Repository;

use App\Entity\Asistencia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AsistenciaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asistencia::class);
    }

    public function truncate(): void
    {
        $this->getEntityManager()
            ->createQuery('DELETE FROM App\Entity\Asistencia')
            ->execute();
    }

}
