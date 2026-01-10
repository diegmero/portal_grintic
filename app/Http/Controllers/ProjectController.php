<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Projects/Index', [
             'projects' => Project::with('company')->latest()->get(),
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
            ]),
        ]);
    }
}
