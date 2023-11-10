<?php

namespace App\Services;

use App\Dtos\TaskDto;
use App\Enums\Status;
use App\Models\Task;
use Auth;
use DateTime;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    public function index(): Collection
    {
        $tasks = Auth::user()->tasks()
            ->keepTree(request('keep-tree') ?? true)
            ->sort()
            ->filter()
            ->get();

        return $tasks;
    }

    public function store(TaskDto $dto): Task
    {
        $task = Task::create([
            'user_id' => Auth::id(),
            'status' => Status::Todo,
            'parent_id' => $dto->parent_id,
            'priority' => $dto->priority,
            'title' => $dto->title,
            'description' => $dto->description,
        ]);

        return $task;
    }

    public function update(TaskDto $dto, Task $task): Task
    {
        return tap($task)->update([
            'priority' => $dto->priority,
            'parent_id' => $dto->parent_id,
            'title' => $dto->title,
            'description' => $dto->description,
        ]);
    }

    public function setStatus(TaskDto $dto, Task $task): Task
    {
        $newStatus = Status::fromName($dto->status);
        $isStatusChanged = $task->status !== $newStatus;

        if (! $isStatusChanged) {
            return $task;
        }

        return tap($task)->update([
            'status' => $newStatus,
            'completed_at' => $newStatus === Status::Done ? now() : null,
        ]);
    }

    protected function getCompletedAt(Task $old, TaskDto $new): ?DateTime
    {
        $newStatus = Status::from($new->status);

        return match (true) {
            $old->status == $newStatus => $old->completedAt,
            $newStatus === Status::Done => now(),
            $newStatus === Status::Todo => null
        };
    }
}
