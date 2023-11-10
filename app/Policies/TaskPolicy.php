<?php

namespace App\Policies;

use App\Enums\Status;
use App\Http\Requests\Api\TaskUpdateRequest;
use App\Http\Requests\Api\TaskStatusRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return true;
    }

    private function getTaskUserId(int $taskId): int
    {
        return Task::firstWhere('id', $taskId)->user->id;
    }

    private function isUserTask(int $taskId, int $userId): bool
    {
        return $this->getTaskUserId($taskId) === $userId;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        if (! is_null(request('parent_id')) && ! $this->isUserTask(request('parent_id'), $user->id)) {
            return Response::deny('You cannot add subtask to the task of another user');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): Response
    {
        if ($user->id !== $task->user_id) {
            return Response::deny('You don\'t own this task');
        }

        if (! is_null(request('parent_id')) && ! $this->isUserTask(request('parent_id'), $user->id)) {
            return Response::deny('You cannot add subtask to the task of another user');
        }

        if (! is_null(request('parent_id')) && $task->descendants->contains('id', request('parent_id'))) {
            return Response::denyWithStatus(422, 'You cannot attach task to its own subtask');
        }

        return Response::allow();
    }

    public function setStatus(User $user, Task $task, TaskStatusRequest $request): Response
    {
        if ($user->id !== $task->user_id) {
            return Response::deny('You don\'t own this task');
        }

        if ($task->hasUndoneSubtasks() && Status::fromName($request->get('status')) === Status::Done) {
            return Response::denyWithStatus(422, 'You must complete all subtasks before moving on');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): Response
    {
        if ($user->id !== $task->user_id) {
            return Response::deny('You don\'t own this task');
        }

        if ($task->status === Status::Done) {
            return Response::denyWithStatus(422, 'You cannot delete a completed task');
        }

        if ($task->hasDoneSubtasks()) {
            return Response::denyWithStatus(422, 'You cannot delete a task with completed subtasks');
        }

        return Response::allow();
    }
}
