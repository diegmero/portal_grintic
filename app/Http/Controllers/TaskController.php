<?php

namespace App\Http\Controllers;

use App\Actions\Projects\RecalculateProjectProgressAction;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class TaskController extends Controller
{
    public function update(Request $request, Task $task, RecalculateProjectProgressAction $recalculateProgress): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'is_completed' => ['sometimes', 'boolean'],
        ]);

        if (isset($validated['is_completed'])) {
            $task->status = $validated['is_completed'] ? TaskStatus::Completed : TaskStatus::Pending;
        } else {
            $task->status = TaskStatus::tryFrom($validated['status']);
        }

        $task->save();

        // Recalculate Project Progress
        $recalculateProgress->execute($task->stage->project);

        return redirect()->back();
    }
}
