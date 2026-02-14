<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Task\Task;
use App\Domain\Task\TaskRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class DoctrineTaskRepository extends ServiceEntityRepository implements TaskRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function getById(int $id): ?Task
    {
        $task = $this->find($id);
        return $task instanceof Task ? $task : null;
    }

    public function save(Task $task): void
    {
        $this->getEntityManager()->persist($task);
    }

    public function remove(Task $task): void
    {
        $this->getEntityManager()->remove($task);
    }

    /**
     * @return list<Task>
     */
    public function findAllOrderedByPriority(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.priority', 'DESC')
            ->addOrderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<Task>
     */
    public function findAllOrderedByPriorityPaginated(int $offset, int $limit, ?bool $status = null, ?string $searchKeyword = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.priority', 'DESC')
            ->addOrderBy('t.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($status !== null) {
            $qb->andWhere('t.status = :status')->setParameter('status', $status);
        }
        if ($searchKeyword !== null && $searchKeyword !== '') {
            $qb->andWhere('t.title LIKE :keyword OR t.description LIKE :keyword')
                ->setParameter('keyword', '%' . addcslashes($searchKeyword, '%_\\') . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function countAll(?bool $status = null, ?string $searchKeyword = null): int
    {
        $qb = $this->createQueryBuilder('t')->select('COUNT(t.id)');

        if ($status !== null) {
            $qb->andWhere('t.status = :status')->setParameter('status', $status);
        }
        if ($searchKeyword !== null && $searchKeyword !== '') {
            $qb->andWhere('t.title LIKE :keyword OR t.description LIKE :keyword')
                ->setParameter('keyword', '%' . addcslashes($searchKeyword, '%_\\') . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
