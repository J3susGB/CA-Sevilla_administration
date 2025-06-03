<?php

namespace App\Repository;

use App\Entity\Entrenamientos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Entrenamientos>
 */
class EntrenamientosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entrenamientos::class);
    }

    public function save(Entrenamientos $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Entrenamientos $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Busca un entrenamiento por árbitro y categoría (si quieres evitar duplicados)
     */
    public function findOneByArbitroAndCategoria(int $arbitroId, int $categoriaId): ?Entrenamientos
    {
        return $this->createQueryBuilder('e')
            ->where('e.arbitro = :arbitro')
            ->andWhere('e.categoria = :categoria')
            ->setParameter('arbitro', $arbitroId)
            ->setParameter('categoria', $categoriaId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllOrderedByArbitro(): array
    {
        return $this->createQueryBuilder('e')
            ->join('e.arbitro', 'a')
            ->addSelect('a')
            ->orderBy('a.first_surname', 'ASC')
            ->addOrderBy('a.second_surname', 'ASC')
            ->addOrderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
