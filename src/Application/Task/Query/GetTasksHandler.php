<?php

declare(strict_types=1);

namespace App\Application\Task\Query;

use App\Domain\Task\Task;
use App\Domain\Task\TaskRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetTasksHandler
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
    ) {
    }

    /**
     * @return array{items: list<Task>, total: int}
     */
    public function __invoke(GetTasksQuery $query): array
    {
        $offset = ($query->page - 1) * $query->limit;
        $items = $this->taskRepository->findAllOrderedByPriorityPaginated(
            $offset,
            $query->limit,
            $query->status,
            $query->searchKeyword,
        );
        $total = $this->taskRepository->countAll($query->status, $query->searchKeyword);

        return ['items' => $items, 'total' => $total];
    }
}
