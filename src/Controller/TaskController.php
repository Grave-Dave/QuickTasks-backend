<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Task\Command\CreateTaskCommand;
use App\Application\Task\Command\EditTaskCommand;
use App\Application\Task\Command\UpdateTaskCommand;
use App\Application\Task\Query\GetTasksQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tasks', name: 'api_tasks_')]
final class TaskController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 6)));
        $statusParam = $request->query->get('status');
        $status = $statusParam === null || $statusParam === ''
            ? null
            : (filter_var($statusParam, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) (int) $statusParam);
        $searchKeyword = $request->query->get('search_keyword');
        $searchKeyword = is_string($searchKeyword) && trim($searchKeyword) !== '' ? trim($searchKeyword) : null;

        $envelope = $this->messageBus->dispatch(new GetTasksQuery(
            page: $page,
            limit: $limit,
            status: $status,
            searchKeyword: $searchKeyword,
        ));
        $result = $envelope->last(HandledStamp::class)?->getResult() ?? ['items' => [], 'total' => 0];
        $tasks = $result['items'];
        $total = $result['total'];
        $items = array_map(fn ($task) => $this->taskToArray($task), $tasks);
        $totalPages = $total > 0 ? (int) ceil($total / $limit) : 1;

        $payload = [
            'total_count' => $total,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'limit' => $limit,
            'items' => $items,
        ];
        if ($searchKeyword !== null) {
            $payload['search_keyword'] = $searchKeyword;
        }
        return $this->json($payload);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true) ?? [];
        $title = isset($data['title']) ? trim((string) $data['title']) : '';

        if ($title === '') {
            return $this->json(
                ['errors' => ['title' => 'Tytuł nie może być pusty']],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $command = new CreateTaskCommand(
            title: $title,
            description: isset($data['description']) ? trim((string) $data['description']) : null,
            priority: (int) ($data['priority'] ?? 1),
        );

        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $v) {
                $errors[$v->getPropertyPath()] = $v->getMessage();
            }
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $envelope = $this->messageBus->dispatch($command);
        $task = $envelope->last(HandledStamp::class)?->getResult();

        return $this->json($this->taskToArray($task), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true) ?? [];
        $title = isset($data['title']) ? trim((string) $data['title']) : '';

        if ($title === '') {
            return $this->json(
                ['errors' => ['title' => 'Tytuł nie może być pusty']],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $command = new EditTaskCommand(
            taskId: $id,
            title: $title,
            description: isset($data['description']) ? trim((string) $data['description']) : null,
            priority: (int) ($data['priority'] ?? 1),
        );

        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $v) {
                $errors[$v->getPropertyPath()] = $v->getMessage();
            }
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $envelope = $this->messageBus->dispatch($command);
            $task = $envelope->last(HandledStamp::class)?->getResult();
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->taskToArray($task));
    }

    #[Route('/{id}', name: 'patch_status', methods: ['PATCH'])]
    public function patchStatus(int $id, Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true) ?? [];
        if (!array_key_exists('status', $data)) {
            return $this->json(
                ['errors' => ['status' => 'Pole status jest wymagane']],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $status = (bool) $data['status'];

        try {
            $this->messageBus->dispatch(new UpdateTaskCommand(taskId: $id, status: $status));
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['id' => $id, 'status' => $status]);
    }

    /**
     * @param \App\Domain\Task\Task $task
     * @return array<string, mixed>
     */
    private function taskToArray($task): array
    {
        return [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->isStatus(),
            'createdAt' => $task->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'priority' => $task->getPriority(),
        ];
    }
}
