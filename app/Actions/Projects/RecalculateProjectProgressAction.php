<?php

namespace App\Actions\Projects;

use App\Enums\TaskStatus;
use App\Models\Project;

class RecalculateProjectProgressAction
{
    public function execute(Project $project): void
    {
        $project->refresh();
        $project->load('stages.tasks.subtasks');

        // 1. Cascade completion: Subtasks -> Tasks
        $this->cascadeTaskCompletion($project);
        
        // 2. Cascade: Tasks -> Stages
        $this->cascadeStageCompletion($project);
        
        // 3. Calculate project progress (NO weights - all tasks equal)
        $this->calculateProjectProgress($project);
    }

    private function cascadeTaskCompletion(Project $project): void
    {
        foreach ($project->stages as $stage) {
            foreach ($stage->tasks as $task) {
                if ($task->subtasks->isEmpty()) {
                    continue;
                }

                $totalSubtasks = $task->subtasks->count();
                $completedSubtasks = $task->subtasks->where('is_completed', true)->count();

                if ($totalSubtasks > 0 && $totalSubtasks === $completedSubtasks) {
                    if ($task->status !== TaskStatus::Completed) {
                        $task->status = TaskStatus::Completed;
                        $task->saveQuietly();
                    }
                } elseif ($task->status === TaskStatus::Completed && $completedSubtasks < $totalSubtasks) {
                    $task->status = TaskStatus::InProgress;
                    $task->saveQuietly();
                }
            }
        }
    }

    private function cascadeStageCompletion(Project $project): void
    {
        foreach ($project->stages as $stage) {
            $stage->load('tasks');
            
            $totalTasks = $stage->tasks->count();
            $completedTasks = $stage->tasks->where('status', TaskStatus::Completed)->count();

            $newStatus = $stage->status;
            if ($totalTasks > 0 && $totalTasks === $completedTasks) {
                $newStatus = 'completed';
            } elseif ($completedTasks > 0) {
                $newStatus = 'in_progress';
            } elseif ($stage->status === 'completed') {
                $newStatus = 'pending';
            }

            if ($stage->status !== $newStatus) {
                $stage->status = $newStatus;
                $stage->saveQuietly();
            }
        }
    }

    private function calculateProjectProgress(Project $project): void
    {
        $project->refresh();
        $project->load('stages.tasks.subtasks');

        $totalProgress = 0;
        $totalTasks = 0;

        foreach ($project->stages as $stage) {
            foreach ($stage->tasks as $task) {
                $totalTasks++;
                $totalProgress += $this->calculateTaskProgress($task);
            }
        }

        // Simple average (no weights)
        $progress = $totalTasks > 0 ? (int) round($totalProgress / $totalTasks) : 0;

        $project->progress = $progress;
        $project->saveQuietly();
    }

    /**
     * Task progress based on subtasks or status
     */
    private function calculateTaskProgress($task): float
    {
        if ($task->subtasks->isNotEmpty()) {
            $totalSubtasks = $task->subtasks->count();
            $completedSubtasks = $task->subtasks->where('is_completed', true)->count();
            return $totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : 0;
        }

        return $task->status === TaskStatus::Completed ? 100 : 0;
    }
}
