<?php

namespace App\Dtos;

use App\Http\Requests\Api\TaskCreateRequest;
use App\Http\Requests\Api\TaskUpdateRequest;
use App\Http\Requests\Api\TaskStatusRequest;

readonly class TaskDto
{
    public function __construct(
        public ?int $parent_id = null,
        public ?string $status = null,
        public ?int $priority = null,
        public ?string $title = null,
        public ?string $description = null,
    ) {
    }

    public static function make(TaskUpdateRequest|TaskCreateRequest $request): self
    {
        return new self(
            parent_id: $request->validated('parent_id'),
            priority: $request->validated('priority'),
            title: $request->validated('title'),
            description: $request->validated('description')
        );
    }

    public static function fromStatus(TaskStatusRequest $request): self
    {
        return new self(
            status: $request->validated('status')
        );
    }
}
