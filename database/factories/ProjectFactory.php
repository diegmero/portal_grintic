<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Project;
use App\Models\Stage;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'status' => 'active',
            'company_id' => Company::factory(), 
        ];
    }
}
