<?php

namespace App\Actions\Finance;

use App\Enums\InvoiceStatus;
use App\Enums\ProjectStatus;
use App\Enums\ProposalStatus;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Proposal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConvertToProjectAction
{
    public function execute(Proposal $proposal): Project
    {
        return DB::transaction(function () use ($proposal) {
            // 1. Update Proposal
            $proposal->update(['status' => ProposalStatus::Accepted]);

            // 2. Create Project
            $project = Project::create([
                'company_id' => $proposal->company_id,
                'name' => $proposal->title,
                'description' => 'Generado desde propuesta ' . $proposal->title,
                'status' => ProjectStatus::Active,
                'start_date' => Carbon::today(),
            ]);

            // 3. Create Invoice
            $invoice = Invoice::create([
                'company_id' => $proposal->company_id,
                'project_id' => $project->id,
                'number' => 'INV-' . strtoupper(Str::random(6)), // Simple generator
                'date' => Carbon::today(),
                'due_date' => Carbon::today()->addDays(30),
                'status' => InvoiceStatus::Sent, // Assuming it's ready to pay
                'total' => $proposal->total,
                'balance_due' => $proposal->total,
                'currency' => 'USD', // Default or from Proposal
            ]);

            // 4. Copy Items
            foreach ($proposal->items as $item) {
                $invoice->items()->create([
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                ]);
            }

            return $project;
        });
    }
}
