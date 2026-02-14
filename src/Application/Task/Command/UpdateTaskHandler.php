<?php

declare(strict_types=1);

namespace App\Application\Task\Command;

use App\Domain\Task\TaskRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateTaskHandler
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
    ) {
    }

    public function __invoke(UpdateTaskCommand $command): void
    {
        $task = $this->taskRepository->getById($command->taskId);
        if ($task === null) {
            throw new \InvalidArgumentException(sprintf('Task %d not found.', $command->taskId));
        }

        $task->setStatus($command->status);
    }
}
