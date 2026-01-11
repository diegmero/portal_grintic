<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->balance_due,
            'payment_date' => 'required|date',
            'method' => 'required|string', // Bank Transfer, Cash, Stripe, etc.
            'reference' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $invoice, $validated) {
            Payment::create([
                'id' => Str::uuid(),
                'invoice_id' => $invoice->id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'method' => $validated['method'],
                'transaction_reference' => $validated['reference'],
            ]);

            // Update Invoice Balance
            $newBalance = $invoice->balance_due - $validated['amount'];
            
            // Determine Status
            $status = 'partial';
            if ($newBalance <= 0) {
                // Precision tolerance check could be added here, but database should handle exact decimals
                $status = 'paid';
            }

            $invoice->update([
                'balance_due' => $newBalance,
                'status' => $status,
            ]);
        });

        return back()->with('success', 'Pago registrado correctamente.');
    }
}
