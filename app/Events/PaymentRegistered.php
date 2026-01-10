<?php

namespace App\Events;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentRegistered implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Payment $payment, public Invoice $invoice)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('invoices.' . $this->invoice->id),
            // Also broadcast to a general finance channel if we want dashboard updates
            new PrivateChannel('finance.dashboard'), 
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'new_balance' => $this->invoice->balance_due,
            'status' => $this->invoice->status,
            'payment_amount' => $this->payment->amount,
        ];
    }
}
