<?php

namespace App\Repository;

use App\Entity\ClaseSesion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ClaseSesionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClaseSesion::class);
    }

    public function truncate(): void
    {
        $this->getEntityManager()
            ->createQuery('DELETE FROM App\Entity\ClaseSesion')
            ->execute();
    }
}
