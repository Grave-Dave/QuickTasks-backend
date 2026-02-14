<?php

declare(strict_types=1);

namespace App\Application\Task\Command;

final readonly class UpdateTaskCommand
{
    public function __construct(
        public int $taskId,
        public bool $status,
    ) {
    }
}
