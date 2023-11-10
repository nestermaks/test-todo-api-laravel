<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'parentId' => $this->parent_id,
            'status' => $this->status->label(),
            'priority' => $this->priority,
            'title' => $this->title,
            'description' => $this->description,
            'completedAt' => $this->completed_at?->format('d-m-Y H:i:s'),
            'createdAt' => $this->created_at?->format('d-m-Y H:i:s'),
            'subtasks' => TaskResource::collection($this->whenLoaded('descendants'))
        ];
    }
}
