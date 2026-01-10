<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

class StageFactory extends Factory
{
    protected $model = Stage::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'project_id' => Project::factory(),
        ];
    }
}
