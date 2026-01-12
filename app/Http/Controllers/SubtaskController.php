<?php

namespace App\Http\Controllers;

use App\Actions\Projects\RecalculateProjectProgressAction;
use App\Http\Requests\StoreSubtaskRequest;
use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    public function store(StoreSubtaskRequest $request, Task $task, RecalculateProjectProgressAction $recalculateProgress): RedirectResponse
    {
        // Permission Check for Clients
        if ($request->user()->company_id && !$request->user()->can('create_subtasks')) {
            abort(403, 'No tienes permiso para crear subtareas.');
        }

        $task->subtasks()->create($request->validated());

        // Recalculate progress since subtask count changed
        $recalculateProgress->execute($task->stage->project);

        return redirect()->back()->with('success', 'Subtarea creada.');
    }

    public function update(Request $request, Subtask $subtask, RecalculateProjectProgressAction $recalculateProgress): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'is_completed' => ['sometimes', 'boolean'],
            'due_date' => ['nullable', 'date'],
        ]);

        $subtask->update($validated);

        // Recalculate progress
        $recalculateProgress->execute($subtask->task->stage->project);

        return redirect()->back();
    }

    public function destroy(Subtask $subtask, RecalculateProjectProgressAction $recalculateProgress): RedirectResponse
    {
        if (request()->user()->company_id) {
            abort(403, 'Acción no permitida.');
        }

        $project = $subtask->task->stage->project;
        $subtask->delete();

        $recalculateProgress->execute($project);

        return redirect()->back()->with('success', 'Subtarea eliminada.');
    }
}
