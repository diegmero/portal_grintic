<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class InvoiceReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    public User $contact;
    public int $daysUntilDue;
    public string $urgencyLevel;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, User $contact)
    {
        $this->invoice = $invoice;
        $this->contact = $contact;
        
        // Calculate days until due
        $dueDate = Carbon::parse($invoice->due_date);
        $this->daysUntilDue = now()->startOfDay()->diffInDays($dueDate, false);
        
        // Determine urgency level for styling
        if ($this->daysUntilDue < 0) {
            $this->urgencyLevel = 'overdue';
        } elseif ($this->daysUntilDue <= 3) {
            $this->urgencyLevel = 'urgent';
        } elseif ($this->daysUntilDue <= 7) {
            $this->urgencyLevel = 'warning';
        } else {
            $this->urgencyLevel = 'normal';
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: "Recordatorio de Factura #{$this->invoice->number} - " . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Load items if not already loaded
        $this->invoice->load('items');
        
        return new Content(
            view: 'emails.invoice-reminder',
            with: [
                'invoice' => $this->invoice,
                'contact' => $this->contact,
                'daysUntilDue' => $this->daysUntilDue,
                'urgencyLevel' => $this->urgencyLevel,
                'company' => $this->invoice->company,
                'items' => $this->invoice->items,
                'portalUrl' => route('portal.invoices.show', $this->invoice->id),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
