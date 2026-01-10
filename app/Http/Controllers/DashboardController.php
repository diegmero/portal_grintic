<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();

        if ($user->hasRole('client')) {
            return $this->clientDashboard($user);
        }

        // Admin Dashboard
        return Inertia::render('Dashboard', [
            'stats' => [
                'total_clients' => DB::table('companies')->count(),
                'active_projects' => DB::table('projects')->where('status', 'active')->count(),
                'pending_billing' => DB::table('invoices')->whereNotIn('status', ['paid', 'void'])->sum('balance_due'),
            ]
        ]);
    }

    private function clientDashboard($user): Response
    {
        $companyId = $user->company_id;

        return Inertia::render('Client/Dashboard', [
            'projects' => Project::where('company_id', $companyId)->where('status', 'active')->get(),
            'pending_invoices' => Invoice::where('company_id', $companyId)->where('balance_due', '>', 0)->get(),
            'subscriptions' => Subscription::where('company_id', $companyId)->where('status', 'active')->get(),
        ]);
    }
}
