<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $creationDate = fake()->dateTimeBetween('-2 week', 'now');
        $isCompleted = $this->coinFlip();

        return [
            'status' => $isCompleted,
            'priority' => fake()->numberBetween(1, 5),
            'title' => fake()->sentence(3),
            'description' => fake()->sentences(2, true),
            'created_at' => $creationDate,
            'completed_at' => $isCompleted ? fake()->dateTimeBetween($creationDate, 'now') : null
        ];
    }

    private function coinFlip(): bool
    {
        return (bool) fake()->numberBetween(0, 1);
    }
}
