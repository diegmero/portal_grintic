<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStageRequest;
use App\Models\Project;
use App\Models\Stage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function store(StoreStageRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();
        
        // Auto-calculate order if not provided
        if (!isset($validated['order'])) {
            $validated['order'] = $project->stages()->count() + 1;
        }

        $project->stages()->create($validated);

        return redirect()->back()->with('success', 'Etapa creada exitosamente.');
    }

    public function update(Request $request, Stage $stage): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'order' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', 'string'],
            'due_date' => ['nullable', 'date'],
        ]);

        $stage->update($validated);

        return redirect()->back()->with('success', 'Etapa actualizada.');
    }

    public function destroy(Stage $stage): RedirectResponse
    {
        // Soft delete tasks first (they have SoftDeletes)
        $stage->tasks()->delete();
        $stage->delete();

        return redirect()->back()->with('success', 'Etapa eliminada.');
    }
}
