<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Domain\Task\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TaskFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tasks = [
            [
                'title' => 'Zaimplementować API zadań',
                'description' => 'Endpointy CRUD dla Task: list, create, update, delete.',
                'status' => false,
                'priority' => 3,
                'createdAt' => new \DateTimeImmutable('-2 days'),
            ],
            [
                'title' => 'Dodać walidację requestów',
                'description' => 'Walidacja pól title, description, priority w kontrolerze.',
                'status' => false,
                'priority' => 2,
                'createdAt' => new \DateTimeImmutable('-1 day'),
            ],
            [
                'title' => 'Naprawić błędy w formularzu',
                'description' => null,
                'status' => true,
                'priority' => 1,
                'createdAt' => new \DateTimeImmutable('-3 days'),
            ],
            [
                'title' => 'Code review PR #42',
                'description' => 'Przejrzeć zmiany w module użytkowników.',
                'status' => false,
                'priority' => 2,
                'createdAt' => new \DateTimeImmutable('today'),
            ],
            [
                'title' => 'Aktualizacja zależności',
                'description' => 'composer update i testy po aktualizacji.',
                'status' => false,
                'priority' => 1,
                'createdAt' => new \DateTimeImmutable('-5 days'),
            ],
        ];

        foreach ($tasks as $data) {
            $task = new Task();
            $task->setTitle($data['title']);
            $task->setDescription($data['description']);
            $task->setStatus($data['status']);
            $task->setPriority($data['priority']);
            $task->setCreatedAt($data['createdAt']);
            $manager->persist($task);
        }

        $manager->flush();
    }
}
