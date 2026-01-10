<?php

namespace App\Http\Controllers;

use App\Actions\Projects\RecalculateProjectProgressAction;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Models\Stage;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class TaskController extends Controller
{
    public function update(Request $request, Task $task, RecalculateProjectProgressAction $recalculateProgress): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'priority' => ['sometimes', 'string'],
            'has_subtasks' => ['sometimes', 'boolean'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', 'string'],
            'is_completed' => ['sometimes', 'boolean'],
        ]);

        // Handle is_completed toggle (from checkbox)
        if (isset($validated['is_completed'])) {
            $task->status = $validated['is_completed'] ? TaskStatus::Completed : TaskStatus::Pending;
            unset($validated['is_completed']);
        }

        // Handle status if passed as string
        if (isset($validated['status'])) {
            $task->status = TaskStatus::tryFrom($validated['status']) ?? $task->status;
            unset($validated['status']);
        }

        // Update other fields
        $task->fill($validated);
        $task->save();

        // Recalculate Project Progress
        $recalculateProgress->execute($task->stage->project);

        return redirect()->back();
    }

    public function store(StoreTaskRequest $request, Stage $stage, RecalculateProjectProgressAction $recalculateProgress): RedirectResponse
    {
        $validated = $request->validated();
        $validated['status'] = TaskStatus::Pending;
        $validated['weight'] = 1.0; // Fixed weight, not user-configurable

        $stage->tasks()->create($validated);

        $recalculateProgress->execute($stage->project);

        return redirect()->back()->with('success', 'Tarea creada.');
    }

    public function destroy(Task $task, RecalculateProjectProgressAction $recalculateProgress): RedirectResponse
    {
        $project = $task->stage->project;
        $task->delete();

        $recalculateProgress->execute($project);

        return redirect()->back()->with('success', 'Tarea eliminada.');
    }
}
