<?php

namespace Tests\Feature;

use App\Actions\Finance\ConvertToProjectAction;
use App\Actions\Finance\RegisterPaymentAction;
use App\Enums\InvoiceStatus;
use App\Enums\ProposalStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_convert_proposal_to_project_and_invoice(): void
    {
        $company = Company::factory()->create();
        $proposal = Proposal::create([
            'company_id' => $company->id,
            'title' => 'Web Development',
            'total' => 1000.00,
            'status' => ProposalStatus::Sent,
        ]);
        ProposalItem::create([
            'proposal_id' => $proposal->id,
            'description' => 'Dev',
            'quantity' => 1,
            'price' => 1000.00,
            'total' => 1000.00,
        ]);

        $action = new ConvertToProjectAction();
        $project = $action->execute($proposal);

        $this->assertEquals(ProposalStatus::Accepted, $proposal->fresh()->status);
        $this->assertDatabaseHas('projects', ['company_id' => $company->id, 'name' => 'Web Development']);
        $this->assertDatabaseHas('invoices', ['company_id' => $company->id, 'total' => 1000.00]);
        $this->assertDatabaseHas('invoice_items', ['total' => 1000.00]);
    }

    public function test_register_payment_updates_balance_and_status(): void
    {
        $invoice = Invoice::create([
            'company_id' => Company::factory()->create()->id,
            'number' => 'INV-TEST',
            'date' => now(),
            'total' => 500.00,
            'balance_due' => 500.00,
            'status' => InvoiceStatus::Sent,
        ]);

        $action = new RegisterPaymentAction();
        
        // Partial Payment
        $action->execute($invoice, ['amount' => 200.00, 'payment_date' => now()]);
        $this->assertEquals(300.00, $invoice->fresh()->balance_due);
        $this->assertEquals(InvoiceStatus::Sent, $invoice->fresh()->status);

        // Full Payment
        $action->execute($invoice, ['amount' => 300.00, 'payment_date' => now()]);
        $this->assertEquals(0.00, $invoice->fresh()->balance_due);
        $this->assertEquals(InvoiceStatus::Paid, $invoice->fresh()->status);
    }

    public function test_subscription_command_generates_invoice(): void
    {
        $listingDate = Carbon::today()->addDays(5);
        $sub = Subscription::create([
            'company_id' => Company::factory()->create()->id,
            'plan_name' => 'Hosting',
            'price' => 50.00,
            'billing_cycle' => 'monthly',
            'next_billing_date' => $listingDate,
            'status' => 'active',
        ]);

        $this->artisan('finance:generate-recurring')
             ->assertExitCode(0);

        $this->assertDatabaseHas('invoices', ['total' => 50.00, 'company_id' => $sub->company_id]);
        
        // Check next billing date advanced
        $this->assertEquals($listingDate->addMonth()->toDateString(), $sub->fresh()->next_billing_date->toDateString());
    }
}
