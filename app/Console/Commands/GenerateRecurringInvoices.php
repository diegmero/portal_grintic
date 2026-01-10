<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateRecurringInvoices extends Command
{
    protected $signature = 'finance:generate-recurring';
    protected $description = 'Generate invoices for subscriptions due in 5 days';

    public function handle(): void
    {
        $targetDate = Carbon::today()->addDays(5);

        // Find subscriptions strictly matching the date or overdue ones? 
        // Logic: specific date match to avoid duplicates if run multiple times? 
        // Better: Check if invoice already exists for this cycle?
        // Simple Agentic Logic: Match next_billing_date <= targetDate.
        
        $subscriptions = Subscription::where('status', 'active')
            ->whereDate('next_billing_date', '<=', $targetDate)
            ->get();

        foreach ($subscriptions as $sub) {
            $this->info("Processing subscription {$sub->id}...");

            // Create Invoice
            $invoice = Invoice::create([
                'company_id' => $sub->company_id,
                'number' => 'REC-' . strtoupper(Str::random(6)),
                'date' => Carbon::today(),
                'due_date' => $sub->next_billing_date,
                'status' => \App\Enums\InvoiceStatus::Draft, // Proforma
                'total' => $sub->price,
                'balance_due' => $sub->price,
            ]);

            $invoice->items()->create([
                'description' => "Renewal: {$sub->plan_name}",
                'quantity' => 1,
                'price' => $sub->price,
                'total' => $sub->price,
            ]);

            // Update Next Billing Date
            $nextDate = Carbon::parse($sub->next_billing_date);
            if ($sub->billing_cycle === 'monthly') {
                $nextDate->addMonth();
            } else {
                $nextDate->addYear();
            }
            $sub->update(['next_billing_date' => $nextDate]);
        }
    }
}
