<?php

namespace App\Actions\Projects;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;

class RecalculateProjectProgressAction
{
    public function execute(Project $project): void
    {
        // 1. Get all tasks for this project via stages
        $tasksQuery = Task::whereIn('stage_id', $project->stages()->select('id'));
        
        $totalWeight = $tasksQuery->sum('weight');
        
        if ($totalWeight <= 0) {
            $project->update(['progress' => 0]);
            return;
        }

        // 2. Sum weight of completed tasks
        $completedWeight = $tasksQuery->where('status', TaskStatus::Completed)->sum('weight');

        // 3. Calculate percentage
        $progress = ($completedWeight / $totalWeight) * 100;

        // 4. Update Project (no decimals needed for integer progress column, but could round)
        $project->update(['progress' => (int) round($progress)]);
    }
}
