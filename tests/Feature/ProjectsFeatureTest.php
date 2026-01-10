<?php

namespace Tests\Feature;

use App\Actions\Projects\RecalculateProjectProgressAction;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Stage;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_progress_recalculation(): void
    {
        $project = Project::factory()->create(['status' => ProjectStatus::Active]);
        $stage = Stage::factory()->create(['project_id' => $project->id]);
        
        // Task 1: Weight 1.0 (Default)
        $task1 = Task::factory()->create(['stage_id' => $stage->id, 'weight' => 1.0, 'status' => TaskStatus::Pending]);
        
        // Task 2: Weight 3.0
        $task2 = Task::factory()->create(['stage_id' => $stage->id, 'weight' => 3.0, 'status' => TaskStatus::Pending]);

        $action = new RecalculateProjectProgressAction();

        // 0% Progress
        $action->execute($project);
        $this->assertEquals(0, $project->fresh()->progress);

        // Complete Task 1 (1 / 4 = 25%)
        $task1->update(['status' => TaskStatus::Completed]);
        $action->execute($project);
        $this->assertEquals(25, $project->fresh()->progress);

        // Complete Task 2 (Total 4 / 4 = 100%)
        $task2->update(['status' => TaskStatus::Completed]);
        $action->execute($project);
        $this->assertEquals(100, $project->fresh()->progress);
    }
}
