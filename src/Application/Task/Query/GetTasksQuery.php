<?php

declare(strict_types=1);

namespace App\Application\Task\Query;

final readonly class GetTasksQuery
{
    public function __construct(
        public int $page = 1,
        public int $limit = 6,
        /** null = wszystkie, true = tylko zakończone (status=1), false = tylko niezakończone (status=0) */
        public ?bool $status = null,
        /** wyszukiwanie po tytule i opisie (LIKE), null/'' = bez filtra */
        public ?string $searchKeyword = null,
    ) {
    }
}
