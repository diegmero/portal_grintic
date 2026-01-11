<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectAdditional;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ProjectAdditionalController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $project->additionals()->create($validated);

        return redirect()->back()->with('success', 'Adicional registrado exitosamente.');
    }

    public function destroy(ProjectAdditional $additional): RedirectResponse
    {
        $additional->delete();

        return redirect()->back()->with('success', 'Adicional eliminado.');
    }
}
