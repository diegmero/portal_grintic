<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Company;
use App\Http\Requests\StoreProjectRequest;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Projects/Index', [
             'projects' => Project::with('company')->latest()->get(),
             'companies' => Company::select('id', 'name', 'tax_id')->orderBy('name')->get(),
        ]);
    }

    public function show(Project $project): Response
    {
        return Inertia::render('Projects/Show', [
            'project' => $project->load([
                'company', 
                'stages.tasks.subtasks',
                'stages.tasks.comments.user', 
                'stages.tasks.media',
                'stages.media',
                'media',
                'invoices',
                'additionals', 
            ]),
        ]);
    }

    public function board(Project $project): Response
    {
        return Inertia::render('Projects/Board', [
            'project' => $project->load([
                'company', 
                'stages.tasks.subtasks',
                'stages.tasks.comments', 
                'stages.tasks.media',
            ]),
        ]);
    }

    public function clientView(Project $project): Response
    {
        // Client read-only view
        return Inertia::render('ClientPortal/ProjectView', [
            'project' => $project->load([
                'company', 
                'stages.tasks.subtasks',
                'stages.tasks.comments.user', 
                'stages.tasks.media',
            ]),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        Project::create($request->validated());
        return redirect()->back()->with('success', 'Proyecto creado exitosamente.');
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        // Permission Check for Clients
        if ($request->user()->company_id && !$request->user()->can('edit_project')) {
            abort(403, 'No tienes permiso para editar el proyecto.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', 'string'],
            'start_date' => 'nullable|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'price' => 'nullable|numeric|min:0',
            'additional_price' => 'nullable|numeric|min:0',
        ]);

        $project->update($validated);

        return redirect()->back()->with('success', 'Proyecto actualizado exitosamente.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        // Only admins can delete projects
        if (request()->user()->company_id) {
            abort(403, 'Acción no permitida.');
        }

        // Safety checks - project must be "clean" to delete
        $hasInvoices = $project->invoices()->exists();
        $hasStages = $project->stages()->exists();
        $hasAdditionals = $project->additionals()->exists();
        $hasMedia = $project->media()->exists();

        if ($hasInvoices) {
            return redirect()->back()->with('error', 'No se puede eliminar: el proyecto tiene facturas asociadas.');
        }

        if ($hasStages) {
            return redirect()->back()->with('error', 'No se puede eliminar: el proyecto tiene etapas. Elimínalas primero.');
        }

        if ($hasAdditionals) {
            return redirect()->back()->with('error', 'No se puede eliminar: el proyecto tiene adicionales registrados.');
        }

        // Delete media if exists (this is optional, media without invoices/stages is safe to remove)
        if ($hasMedia) {
            $project->clearMediaCollection();
        }

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Proyecto eliminado exitosamente.');
    }
}
