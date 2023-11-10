<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SortingTest extends TestCase
{
    use RefreshDatabase;

    protected User $johnDoe;

    protected User $janeDoe;

    protected array $taskPayload;

    protected array $subtaskPayload;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->johnDoe = User::whereEmail('johnDoe@example.com')->first();
        $this->janeDoe = User::whereEmail('janeDoe@example.com')->first();

        $this->taskPayload = [
            'priority' => 3,
            'title' => 'Clean up',
            'description' => 'Clean every corner in the house',
        ];

        $this->subtaskPayload = [
            'priority' => 4,
            'title' => 'Wash the windows',
            'description' => 'They have to shine bright like a diamonds',
        ];

        $this->actingAs($this->johnDoe);
    }

    public function test_sorts_by_created_at_field(): void
    {
        $params = [
            'keep-tree' => false,
            'sorters' => [
                'createdAt' => 'asc',
            ],
        ];

        $response = $this->get(route('tasks.index', $params));

        $date = Carbon::createFromFormat('Y-m-d', '1900-1-1');
        foreach ($response->json()['data'] as $task) {
            $nextDate = Carbon::parse($task['createdAt']);
            $this->assertTrue($nextDate->gte($date));
            $date = $nextDate;
        }
    }

    public function test_sorts_by_completed_at_field(): void
    {
        $params = [
            'keep-tree' => false,
            'sorters' => [
                'completedAt' => 'desc',
            ],
        ];

        $response = $this->get(route('tasks.index', $params));

        $date = Carbon::createFromFormat('Y-m-d', '2900-1-1');
        foreach ($response->json()['data'] as $task) {
            $nextDate = Carbon::parse($task['completedAt']);
            $this->assertTrue($nextDate->lt($date) || ($task['completedAt'] === null));
            $date = $nextDate;
        }
    }

    public function test_sorts_by_priority_field(): void
    {
        $params = [
            'keep-tree' => false,
            'sorters' => [
                'priority' => 'asc',
            ],
        ];

        $response = $this->get(route('tasks.index', $params));

        $priority = 0;
        foreach ($response->json()['data'] as $task) {
            $nextPriority = $task['priority'];
            $this->assertTrue($nextPriority >= $priority);
            $priority = $nextPriority;
        }
    }

    public function test_sorters_work_together(): void
    {
        $params = [
            'keep-tree' => false,
            'sorters' => [
                'priority' => 'asc',
                'createdAt' => 'desc',
            ],
        ];

        $response = $this->get(route('tasks.index', $params));

        $priority = 0;
        $date = Carbon::createFromFormat('Y-m-d', '2900-1-1');
        foreach ($response->json()['data'] as $task) {
            $nextPriority = $task['priority'];
            $this->assertTrue($nextPriority >= $priority);

            if ($nextPriority === $priority) {
                $nextDate = Carbon::createFromFormat('d-m-Y H:i:s', $task['createdAt']);
                $this->assertTrue($nextDate->lt($date));
                $date = $nextDate;
            } else {
                $date = Carbon::parse($task['completedAt']);
            }

            $priority = $nextPriority;
        }
    }
}
