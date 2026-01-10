<?php

namespace Database\Factories;

use App\Models\Stage;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence,
            'stage_id' => Stage::factory(),
            'status' => 'pending',
            'weight' => 1.0,
        ];
    }
}
