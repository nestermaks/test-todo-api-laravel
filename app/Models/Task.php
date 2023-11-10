<?php

namespace App\Models;

use App\Enums\Status;
use App\Enums\TaskFilters;
use App\Enums\TaskSorters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parent_id',
        'status',
        'priority',
        'title',
        'description',
        'completed_at',
    ];

    protected $casts = [
        'status' => Status::class,
        'created_at' => 'datetime:d-m-Y H:i:s',
        'completed_at' => 'datetime:d-m-Y H:i:s',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id')->sort();
    }

    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    public function scopeKeepTree(Builder $q, bool $keepTree = true): Builder
    {
        return $keepTree ? $q->whereParentId(null)->with('descendants') : $q;
    }

    public function scopeStatusFilter(Builder $q, ?Status $status): Builder
    {
        if (is_null($status)) {
            return $q;
        }

        return $q->whereStatus($status);
    }

    public function scopePriorityFilter(Builder $q, ?string $priorities): Builder
    {
        if (is_null($priorities)) {
            return $q;
        }

        $prioritiesArray = explode(',', $priorities);

        return $q->whereIn('priority', $prioritiesArray);
    }

    public function scopeSearch(Builder $q, ?string $searchString): Builder
    {
        if (gettype($searchString) !== 'string') {
            return $q;
        }

        return $q->whereFullText(['title', 'description'], $searchString);
    }

    public function scopeFilter(Builder $query): Builder
    {
        $filters = request('filters');

        if (gettype($filters) !== 'array') {
            return $query;
        }

        foreach ($filters as $filterName => $payload) {
            $query = match ($filterName) {
                TaskFilters::Status->value => $query->statusFilter(Status::fromName($payload)),
                TaskFilters::Priority->value => $query->priorityFilter($payload),
                TaskFilters::Search->value => $query->search($payload),
                default => $query
            };
        }

        return $query;
    }

    public function scopeSortCompletedAt(Builder $q, string $direction = 'desc'): Builder
    {
        return $q->orderBy('completed_at', $direction);
    }

    public function scopeSortCreatedAt(Builder $q, string $direction = 'desc'): Builder
    {
        return $q->orderBy('created_at', $direction);
    }

    public function scopeSortPriority(Builder $q, string $direction = 'desc'): Builder
    {
        return $q->orderBy('priority', $direction);
    }

    public function scopeSort(Builder $query): Builder
    {
        $sorters = request('sorters');

        if (gettype($sorters) !== 'array') {
            return $query;
        }

        foreach ($sorters as $sorterName => $direction) {
            $query = match ($sorterName) {
                TaskSorters::CreatedAt->value => $query->sortCreatedAt($direction),
                TaskSorters::CompletedAt->value => $query->sortCompletedAt($direction),
                TaskSorters::Priority->value => $query->sortPriority($direction),
                default => $query
            };
        }

        return $query;
    }

    public function hasSubtasksWithStatus(Status $status): bool
    {
        return $this->descendants()->where('status', $status)->get()->isNotEmpty();
    }

    public function hasUndoneSubtasks(): bool
    {
        return $this->hasSubtasksWithStatus(Status::Todo);
    }

    public function hasDoneSubtasks(): bool
    {
        return $this->hasSubtasksWithStatus(Status::Done);
    }
}
