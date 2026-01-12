<?php

namespace App\Http\Controllers;

use App\Models\ClientService;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

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
            'projects' => Project::where('company_id', $this->companyId())
                ->with(['stages.tasks'])
                ->withCount(['stages', 'invoices'])
                ->latest()
                ->get(),
        ]);
    }

    /**
     * Display a specific project for the client.
     */
    public function showProject(Project $project): Response
    {
        abort_if($project->company_id !== $this->companyId(), 403);

        return Inertia::render('ClientPortal/ProjectView', [
            'project' => $project->load([
                'company',
                'stages.tasks.subtasks',
                'stages.tasks.comments.user',
                'stages.tasks.media',
            ]),
        ]);
    }

    /**
     * Display client's invoices list.
     */
    public function invoices(): Response
    {
        return Inertia::render('Client/Invoices/Index', [
            'invoices' => Invoice::where('company_id', $this->companyId())
                ->with(['project', 'items'])
                ->latest()
                ->get(),
        ]);
    }

    /**
     * Display a specific invoice for the client.
     */
    public function showInvoice(Invoice $invoice): Response
    {
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
}
