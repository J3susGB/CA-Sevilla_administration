<?php

namespace App\Repository;

use App\Entity\Informes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Informes>
 *
 * @method Informes|null   find($id, $lockMode = null, $lockVersion = null)
 * @method Informes|null   findOneBy(array $criteria, array $orderBy = null)
 * @method Informes[]      findAll()
 * @method Informes[]      findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InformesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Informes::class);
    }

    public function save(Informes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Informes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function truncate(): void
    {
        $this->getEntityManager()
            ->createQuery('DELETE FROM App\Entity\Informes')
            ->execute();
    }

}
