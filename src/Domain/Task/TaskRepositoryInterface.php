<?php

declare(strict_types=1);

namespace App\Domain\Task;

interface TaskRepositoryInterface
{
    public function getById(int $id): ?Task;

    public function save(Task $task): void;

    public function remove(Task $task): void;

    /**
     * @return list<Task>
     */
    public function findAllOrderedByPriority(): array;

    /**
     * @param int $offset
     * @param int $limit
     * @param bool|null $status null = wszystkie, true = tylko zakończone, false = tylko niezakończone
     * @param string|null $searchKeyword wyszukiwanie po tytule i opisie (LIKE)
     * @return list<Task>
     */
    public function findAllOrderedByPriorityPaginated(int $offset, int $limit, ?bool $status = null, ?string $searchKeyword = null): array;

    /**
     * @param bool|null $status null = wszystkie, true = tylko zakończone, false = tylko niezakończone
     * @param string|null $searchKeyword wyszukiwanie po tytule i opisie (LIKE)
     */
    public function countAll(?bool $status = null, ?string $searchKeyword = null): int;
}
