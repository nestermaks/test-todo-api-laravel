<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::all()->map(function (User $user) {
            Task::factory()
                ->count(3)
                ->state(fn () => ['status' => Status::Todo])
                ->for($user)
                ->has(Task::factory()
                    ->count(2)
                    ->state(fn () => ['status' => Status::Todo])
                    ->for($user)
                    ->has(
                        Task::factory()->count(2)->for($user),
                        'children'
                    ), 'children')
                ->create();
        });
    }
}
