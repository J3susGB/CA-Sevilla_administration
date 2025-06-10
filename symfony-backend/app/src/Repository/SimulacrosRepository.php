<?php

namespace App\Repository;

use App\Entity\Simulacros;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Simulacros>
 *
 * @method Simulacros|null find($id, $lockMode = null, $lockVersion = null)
 * @method Simulacros|null findOneBy(array $criteria, array $orderBy = null)
 * @method Simulacros[]    findAll()
 * @method Simulacros[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SimulacrosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Simulacros::class);
    }

    public function findAllOrderedByFecha(): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.arbitro', 'a')
            ->addSelect('a')
            ->join('s.categoria', 'c')
            ->addSelect('c')
            ->orderBy('s.fecha', 'DESC')
            ->addOrderBy('a.first_surname', 'ASC')    
            ->addOrderBy('a.second_surname', 'ASC')   
            ->addOrderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function truncate(): void
    {
        $this->getEntityManager()
            ->createQuery('DELETE FROM App\Entity\Simulacros')
            ->execute();
    }

}
