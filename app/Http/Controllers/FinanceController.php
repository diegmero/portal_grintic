<?php

namespace App\Http\Controllers;

use App\Actions\Finance\ConvertToProjectAction;
use App\Actions\Finance\RegisterPaymentAction;
use App\Models\Invoice;
use App\Models\Proposal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinanceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Finance/Index', [
            'invoices' => Invoice::with('company')->latest()->get(),
            'proposals' => Proposal::with('company')->latest()->get(),
        ]);
    }

    public function convertProposal(Proposal $proposal, ConvertToProjectAction $action): RedirectResponse
    {
        $action->execute($proposal);
        return redirect()->route('projects.index')->with('success', 'Propuesta convertida en Proyecto.');
    }

    public function storePayment(Request $request, Invoice $invoice, RegisterPaymentAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        $action->execute($invoice, $validated);

        return back()->with('success', 'Pago registrado correctamente.');
    }
}
