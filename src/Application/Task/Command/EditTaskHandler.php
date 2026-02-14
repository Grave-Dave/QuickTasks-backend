<?php

declare(strict_types=1);

namespace App\Application\Task\Command;

use App\Domain\Task\Task;
use App\Domain\Task\TaskRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EditTaskHandler
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
    ) {
    }

    public function __invoke(EditTaskCommand $command): Task
    {
        $task = $this->taskRepository->getById($command->taskId);
        if ($task === null) {
            throw new \InvalidArgumentException(sprintf('Task %d not found.', $command->taskId));
        }

        $task->setTitle($command->title);
        $task->setDescription($command->description);
        $task->setPriority($command->priority);

        return $task;
    }
}
