<?php

namespace App\Http\Controllers;

use App\Models\ClientService;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Stage;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ClientPortalController extends Controller
{
    /**
     * Get the authenticated client's company ID.
     */
    private function companyId(): string
    {
        return Auth::user()->company_id;
    }

    /**
     * Display client's projects list.
     */
    public function projects(): Response
    {
        return Inertia::render('Client/Projects/Index', [
            'projects' => Auth::user()->can('view_projects') 
                ? Project::where('company_id', $this->companyId())
                    ->with(['stages.tasks' => function ($q) {
                        $q->withCount(['comments', 'media']);
                    }])
                    ->withCount(['stages', 'invoices'])
                    ->latest()
                    ->get()
                : [],
        ]);
    }

    /**
     * Display a specific project for the client.
     */
    public function showProject(Project $project): Response
    {
        abort_if($project->company_id !== $this->companyId(), 403);

        return Inertia::render('Client/Projects/Show', [
            'project' => $project->load([
                'company',
                'stages.media',
                'stages.tasks' => function ($query) {
                     $query->withCount(['comments', 'media'])->with(['subtasks']);
                },
                'media', // Project files
                'invoices', // Project invoices
            ]),
            'files' => $project->getMedia('project_files')->map(function ($file) {
                return [
                    'id' => $file->id,
                    'file_name' => $file->file_name,
                    'size' => $file->size,
                    'mime_type' => $file->mime_type,
                    // 'original_url' => $file->original_url, 
                ];
            }),
        ]);
    }

    /**
     * Display client's invoices list.
     */
    public function invoices(): Response
    {
        return Inertia::render('Client/Invoices/Index', [
            'invoices' => Auth::user()->can('view_financials')
                ? Invoice::where('company_id', $this->companyId())
                    ->with(['project', 'items'])
                    ->latest()
                    ->get()
                : [],
        ]);
    }

    /**
     * Display a specific invoice for the client.
     */
    public function showInvoice(Invoice $invoice): Response
    {
        if (!Auth::user()->can('view_financials')) {
            abort(403, 'No tienes permiso para ver facturas.');
        }
        
        abort_if($invoice->company_id !== $this->companyId(), 403);

        return Inertia::render('Client/Invoices/Show', [
            'invoice' => $invoice->load(['company', 'project', 'items', 'payments']),
        ]);
    }

    /**
     * Display client's active services.
     */
    public function services(): Response
    {
        return Inertia::render('Client/Services/Index', [
            'services' => ClientService::where('company_id', $this->companyId())
                ->with(['product'])
                ->latest()
                ->get(),
        ]);
    }

    /**
     * Stream a media file for the client (Access Control).
     */
    public function showMedia($id)
    {
        $media = Media::findOrFail($id);
        $model = $media->model;

        $authorized = false;
        if ($model instanceof Project) {
            $authorized = $model->company_id === $this->companyId();
        } elseif ($model instanceof Stage) {
            $authorized = $model->project->company_id === $this->companyId();
        } elseif ($model instanceof Task) {
            $authorized = $model->stage->project->company_id === $this->companyId();
        }

        abort_unless($authorized, 403);

        return response()->file($media->getPath(), [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
        ]);
    }
}
