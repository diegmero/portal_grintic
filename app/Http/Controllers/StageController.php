<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStageRequest;
use App\Models\Project;
use App\Models\Stage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StageController extends Controller
{
    public function store(StoreStageRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();
        
        // Permission Check for Clients
        if ($request->user()->company_id && !$request->user()->can('create_stages')) {
            abort(403, 'No tienes permiso para crear etapas.');
        }
        
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

    public function reorder(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'stages' => 'required|array',
            'stages.*.id' => 'required|uuid|exists:stages,id',
            'stages.*.order' => 'required|integer',
        ]);

        $stages = $request->input('stages');

        DB::transaction(function () use ($stages) {
            foreach ($stages as $stageData) {
                if(isset($stageData['id']) && isset($stageData['order'])){
                     Stage::where('id', $stageData['id'])->update(['order' => $stageData['order']]);
                }
            }
        });

        return redirect()->back();
    }

    public function destroy(Stage $stage): RedirectResponse
    {
        if (Auth::user()->company_id) {
            abort(403, 'Acción no permitida.');
        }

        // Soft delete tasks first (they have SoftDeletes)
        $stage->tasks()->delete();
        $stage->delete();

        return redirect()->back()->with('success', 'Etapa eliminada.');
    }
}
