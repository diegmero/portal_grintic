<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Company;
use App\Http\Requests\StoreProjectRequest;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

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
}
