<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Task;
use App\Models\User;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class TaskTest extends TestCase
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

    public function test_works(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_gets_all_user_tasks(): void
    {
        $response = $this->get(route('tasks.index'));

        $response->assertOk();
        $response->assertJsonFragment(['userId' => Auth::id()]);
        $response->assertJsonMissing(['userId' => $this->janeDoe->id]);
        $response->assertJson(
            fn (AssertableJson $json) => $json->has('data', 3)
                ->has('data.0.subtasks', 2)
                ->has('data.0.subtasks.0.subtasks', 2)
        );
    }

    public function test_creates_a_task(): void
    {
        $response = $this->post(route('tasks.store'), $this->taskPayload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', ['user_id' => Auth::id(), ...$this->taskPayload]);
    }

    public function test_creates_subtask(): void
    {
        $task = $this->createTask();
        $this->subtaskPayload['parent_id'] = $task->id;

        $response = $this->post(route('tasks.store'), $this->subtaskPayload);
        $response->assertStatus(201);

        $this->assertDatabaseHas('tasks', ['user_id' => Auth::id(), ...$this->subtaskPayload]);
    }

    public function test_cannot_attach_to_someone_else_task(): void
    {
        $task = $this->createTask();
        $this->subtaskPayload['parent_id'] = $task->id;

        $this->actingAs($this->janeDoe);

        $response = $this->post(route('tasks.store'), $this->subtaskPayload);
        $response->assertStatus(403);

        $response = $this->put(route('tasks.update', Auth::user()->tasks->first()), [
            'parent_id' => $task->id,
        ]);
        $response->assertStatus(403);

        $response = $this->put(route('tasks.update', Auth::user()->tasks->first()), [
            'parent_id' => Auth::user()->tasks->last(),
        ]);
    }

    public function test_cannot_attach_to_descendant(): void
    {
        $task = $this->createTask();
        $subtask = $this->createSubtask($task->id);

        $this->taskPayload['parent_id'] = $subtask->id;

        $response = $this->put(route('tasks.update', $task), $this->taskPayload);
        $response->assertStatus(422);
    }

    public function test_edits_user_task(): void
    {
        $task = $this->createTask();
        $this->taskPayload['title'] = 'Take a rest';

        $response = $this->put(route('tasks.update', $task->id), $this->taskPayload);

        $response->assertOk();
        $this->assertDatabaseHas('tasks', ['title' => 'Take a rest']);
        $this->assertDatabaseMissing('tasks', ['title' => 'Clean up']);
    }

    public function test_marks_user_task_as_completed(): void
    {
        $task = $this->createTask();

        $this->assertDatabaseHas('tasks', ['title' => 'Clean up', 'status' => Status::Todo]);

        $response = $this->patch(route('tasks.status', $task), [
            'status' => 'done',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('tasks', ['title' => 'Clean up', 'status' => Status::Done]);
    }

    public function test_cannot_mark_as_done_if_task_has_undone_subtasks(): void
    {
        $task = $this->createTask();
        $this->createSubtask($task->id);

        $response = $this->patch(route('tasks.status', $task), ['status' => 'done']);

        $response->assertStatus(422);

        $response = $this->patch(
            route('tasks.status', $this->janeDoe->tasks->first()),
            ['status' => 'done'],
        );

        $response->assertStatus(403);
    }

    public function test_deletes_user_task(): void
    {
        $task = $this->createTask();
        $this->assertDatabaseHas('tasks', $this->taskPayload);

        $response = $this->delete(route('tasks.destroy', ['task' => $task]));
        $response->assertOk();
        $this->assertDatabaseMissing('tasks', $this->taskPayload);
    }

    public function test_cannot_delete_task_which_is_done(): void
    {
        $task = $this->createTask();
        $this->patch(route('tasks.status', $task), ['status' => 'done']);

        $response = $this->delete(route('tasks.destroy', ['task' => $task]));

        $response->assertStatus(422);
    }

    public function test_cannot_edit_or_delete_someone_else_task(): void
    {
        $task = $this->createTask();
        $this->assertDatabaseHas('tasks', $this->taskPayload);

        $this->actingAs($this->janeDoe);

        $response = $this->delete(route('tasks.destroy', ['task' => $task]));
        $response->assertStatus(403);
    }
}
