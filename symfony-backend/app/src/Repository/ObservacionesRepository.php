<?php

namespace App\Repository;

use App\Entity\Observaciones;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Observaciones>
 *
 * @method Observaciones|null find($id, $lockMode = null, $lockVersion = null)
 * @method Observaciones|null findOneBy(array $criteria, array $orderBy = null)
 * @method Observaciones[]    findAll()
 * @method Observaciones[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ObservacionesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Observaciones::class);
    }
}
