<?php

namespace App\Http\Controllers\Api\V1;

use App\Dtos\TaskDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TaskUpdateRequest;
use App\Http\Requests\Api\TaskCreateRequest;
use App\Http\Requests\Api\TaskStatusRequest;
use App\Http\Resources\Api\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function __construct(protected TaskService $service)
    {
        $this->authorizeResource(Task::class, 'task');
    }

    public function index()
    {
        $tasks = $this->service->index();

        return TaskResource::collection($tasks);
    }

    public function store(TaskCreateRequest $request): TaskResource
    {
        $task = $this->service->store(TaskDto::make($request));

        return TaskResource::make($task);
    }

    public function update(TaskUpdateRequest $request, Task $task)
    {
        $task = $this->service->update(TaskDto::make($request), $task);

        return TaskResource::make($task);
    }

    public function setStatus(TaskStatusRequest $request, Task $task): TaskResource
    {
        $this->authorize('setStatus', [$task, $request]);

        $task = $this->service->setStatus(TaskDto::fromStatus($request), $task);

        return TaskResource::make($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json([
            'message' => "Task with id '$task->id' successfully deleted"
        ]);
    }
}
