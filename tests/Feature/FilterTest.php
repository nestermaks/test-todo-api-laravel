<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Task;
use App\Models\User;
use Auth;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class FilterTest extends TestCase
{
    use DatabaseMigrations;

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

    protected function createTask(User $user = null): Task
    {
        $user = $user ?? Auth::user();

        return $user->tasks()->create($this->taskPayload);
    }

    protected function createSubtask(int $parentId, User $user = null): Task
    {
        $user = $user ?? Auth::user();

        return $user->tasks()->create(['parent_id' => $parentId, ...$this->subtaskPayload]);
    }

    public function test_filters_tasks_by_status(): void
    {
        $tasksAmount = Auth::user()->tasks()->whereStatus(Status::Done)->count();
        $params = [
            'keep-tree' => false,
            'filters' => [
                'status' => Status::Done->label(),
            ],
        ];

        $response = $this->get(route('tasks.index', $params));

        $response->assertOk();
        $response->assertJson(function (AssertableJson $json) use ($tasksAmount) {
            return $json->has('data', $tasksAmount);
        });
    }

    public function test_filters_tasks_by_priority(): void
    {
        $tasksAmount = Auth::user()->tasks()->wherePriority(3)->count();
        $params = [
            'keep-tree' => false,
            'filters' => [
                'priority' => 3,
            ],
        ];

        $response = $this->get(route('tasks.index', $params));

        $response->assertOk();
        $response->assertJson(function (AssertableJson $json) use ($tasksAmount) {
            return $json->has('data', $tasksAmount);
        });

        $tasksAmount = Auth::user()->tasks()->whereIn('priority', [3, 1])->count();
        $params = [
            'keep-tree' => false,
            'filters' => [
                'priority' => '3,1',
            ],
        ];

        $response = $this->get(route('tasks.index', $params));

        $response->assertOk();
        $response->assertJson(function (AssertableJson $json) use ($tasksAmount) {
            return $json->has('data', $tasksAmount);
        });
    }

    public function test_filters_tasks_by_title_and_description(): void
    {
        $this->taskPayload['title'] = '!!!clean everything';
        $this->taskPayload['description'] = 'Just do it!';
        $this->createTask();
        $this->taskPayload['title'] = 'clean up the code';
        $this->taskPayload['description'] = 'And test it properly';
        $this->createTask();
        $this->taskPayload['title'] = 'kill putin';
        $this->taskPayload['description'] = 'And clean everything after';
        $this->createTask();

        $params = [
            'keep-tree' => false,
            'filters' => [
                'search' => "clean",
            ],
        ];

        $response = $this->get(route('tasks.index', $params));

        $response->assertOk();
        $response->assertJson(function (AssertableJson $json) {
            return $json->has('data', 3);
        });
    }

    public function test_filters_work_together(): void
    {
        $this->taskPayload['title'] = '!!!clean everything';
        $this->taskPayload['description'] = 'Just do it!';
        $this->taskPayload['priority'] = 2;
        $task = $this->createTask();
        $this->subtaskPayload['title'] = 'clean up the code';
        $this->subtaskPayload['description'] = 'And test it properly';
        $this->subtaskPayload['status'] = Status::Done;
        $this->createSubtask($task->id);
        $this->subtaskPayload['title'] = 'kill putin';
        $this->subtaskPayload['description'] = 'And clean everything after';
        $this->subtaskPayload['priority'] = 5;
        $this->subtaskPayload['status'] = Status::Todo;
        $this->createSubtask($task->id);

        $params = [
            'keep-tree' => false,
            'filters' => [
                'search' => "clean",
            ],
        ];

        $response = $this->get(route('tasks.index', $params));

        $response->assertJson(function (AssertableJson $json) {
            return $json->has('data', 3);
        });

        $params['filters']['priority'] = 5;
        $response = $this->get(route('tasks.index', $params));

        $response->assertJson(function (AssertableJson $json) {
            return $json->has('data', 1);
        });

        $params['filters']['status'] = 'done';
        $response = $this->get(route('tasks.index', $params));

        $response->assertJson(function (AssertableJson $json) {
            return $json->has('data', 0);
        });

        $params['filters']['status'] = 'done';
        $params['filters']['priority'] = null;
        $response = $this->get(route('tasks.index', $params));

        $response->assertJson(function (AssertableJson $json) {
            return $json->has('data', 1);
        });
    }

    public function test_filters_work_in_tree_mode(): void
    {
        $this->subtaskPayload['title'] = 'Clean something';
        $this->taskPayload['title'] = '!!!clean everything';
        $this->taskPayload['description'] = 'Just do it!';
        $this->taskPayload['priority'] = 2;
        $task1 = $this->createTask();
        $this->createSubtask($task1->id);
        $this->createSubtask($task1->id);
        $this->taskPayload['title'] = 'clean up the code';
        $this->taskPayload['description'] = 'And test it properly';
        $this->taskPayload['status'] = Status::Done;
        $task2 = $this->createTask();
        $this->createSubtask($task2->id);
        $this->createSubtask($task2->id);
        $this->taskPayload['title'] = 'kill putin';
        $this->taskPayload['description'] = 'And clean everything after';
        $this->taskPayload['priority'] = 5;
        $this->taskPayload['status'] = Status::Todo;
        $task3 = $this->createTask();
        $this->createSubtask($task3->id);
        $this->createSubtask($task3->id);

        $params = [
            'keep-tree' => true,
            'filters' => [
                'search' => "clean",
            ],
        ];

        $response = $this->get(route('tasks.index', $params));

        $response->assertJson(function (AssertableJson $json) {
            return $json->has('data', 3);
        });

        $params['keep-tree'] = false;
        $response = $this->get(route('tasks.index', $params));
        $response->assertJson(function (AssertableJson $json) {
            return $json->has('data', 9);
        });
    }
}
