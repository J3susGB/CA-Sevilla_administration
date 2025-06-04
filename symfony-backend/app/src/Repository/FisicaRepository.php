<?php
// src/Repository/FisicaRepository.php

namespace App\Repository;

use App\Entity\Fisica;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fisica>
 */
class FisicaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fisica::class);
    }

}
