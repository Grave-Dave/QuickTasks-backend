<?php

declare(strict_types=1);

namespace App\Application\Task\Command;

use App\Domain\Task\Task;
use App\Domain\Task\TaskRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateTaskHandler
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
    ) {
    }

    public function __invoke(CreateTaskCommand $command): Task
    {
        $task = new Task();
        $task->setTitle($command->title);
        $task->setDescription($command->description);
        $task->setPriority($command->priority);

        $this->taskRepository->save($task);

        return $task;
    }
}
