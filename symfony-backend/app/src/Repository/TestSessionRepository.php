<?php

namespace App\Repository;

use App\Entity\TestSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TestSession>
 */
class TestSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestSession::class);
    }

    public function truncate(): void
    {
        $this->getEntityManager()
            ->createQuery('DELETE FROM App\Entity\TestSession')
            ->execute();
    }
}
