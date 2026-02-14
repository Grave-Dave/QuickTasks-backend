<?php

declare(strict_types=1);

namespace App\Application\Task\Command;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateTaskCommand
{
    public function __construct(
        #[Assert\NotBlank(message: 'Tytuł nie może być pusty')]
        #[Assert\Length(max: 255)]
        public string $title,
        public ?string $description,
        #[Assert\Range(min: 1, max: 3, notInRangeMessage: 'Priorytet musi być od 1 do 3')]
        public int $priority = 1,
    ) {
    }
}
