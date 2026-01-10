<?php

namespace App\Actions\Finance;

use App\Enums\InvoiceStatus;
use App\Events\PaymentRegistered;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class RegisterPaymentAction
{
    public function execute(Invoice $invoice, array $paymentData): Payment
    {
        return DB::transaction(function () use ($invoice, $paymentData) {
            // 1. Create Payment
            $payment = $invoice->payments()->create([
                'amount' => $paymentData['amount'],
                'payment_date' => $paymentData['payment_date'],
                'method' => $paymentData['method'] ?? 'bank_transfer',
                'transaction_reference' => $paymentData['transaction_reference'] ?? null,
            ]);

            // 2. Calculate new balance using BCMath (via string casting for precision)
            // Balance = Total - Sum(Payments)
            $totalPayments = $invoice->payments()->sum('amount');
            
            // Note: Laravel's decimal cast returns string or float depending on config, but here we can just use simple math 
            // if we trust the database sum. For strict BCMath in PHP:
            $balance = bcsub($invoice->total, $totalPayments, 2);

            $invoice->balance_due = max(0, $balance); // Ensure not negative

            // 3. Update Status
            if (bccomp($invoice->balance_due, '0.00', 2) === 0) {
                $invoice->status = InvoiceStatus::Paid;
            } else {
                // If it was unpaid/draft, now it's partial or sent? 
                // Usually if there's a payment it's at least "Sent" or "Partially Paid" (if we had that status).
                // Keeping it simple: If not paid, and previously Sent, stays Sent. 
                // If previously Draft, maybe move to Sent?
                if ($invoice->status === InvoiceStatus::Draft) {
                    $invoice->status = InvoiceStatus::Sent;
                }
            }
            
            $invoice->save();

            // 4. Broadcast Event
            broadcast(new PaymentRegistered($payment, $invoice))->toOthers();

            return $payment;
        });
    }
}
