<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Project;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'company_id']);
        
        $query = Invoice::with(['company', 'project', 'items'])
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($filters['company_id'] ?? null, function ($q, $companyId) {
                $q->where('company_id', $companyId);
            })
            ->latest();

        $invoices = $query->get();

        // Calculate Stats
        $stats = [
            'total_count' => $invoices->count(),
            'total_amount' => $invoices->sum('total'),
            'total_paid' => $invoices->sum(fn($i) => $i->total - $i->balance_due),
            'total_due' => $invoices->sum('balance_due'),
            'by_status' => [
                'draft' => $invoices->where('status', \App\Enums\InvoiceStatus::Draft)->count(),
                'sent' => $invoices->where('status', \App\Enums\InvoiceStatus::Sent)->count(),
                'paid' => $invoices->where('status', \App\Enums\InvoiceStatus::Paid)->count(),
                'partial' => $invoices->where('status', \App\Enums\InvoiceStatus::Partial)->count(),
                'overdue' => $invoices->where('status', \App\Enums\InvoiceStatus::Overdue)->count(),
            ]
        ];

        return Inertia::render('Finance/Invoices/Index', [
            'invoices' => $invoices,
            'companies' => Company::select('id', 'name')->orderBy('name')->get(),
            'projects' => Project::select('id', 'name', 'company_id')->get(),
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request)
    {
        $projectId = $request->query('project_id');
        $mode = $request->query('mode', 'final'); // 'advance' or 'final'
        
        $prefill = [
            'project_id' => $projectId,
            'company_id' => null,
            'items' => [],
            'date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
        ];

        if ($projectId) {
            $project = Project::with('company')->findOrFail($projectId);
            $prefill['company_id'] = $project->company_id;
            
            // Master Invoice Logic: Pre-fill with REMAINING unbilled amount
            $billed = $project->invoices()->sum('total');
            $additionalsSum = $project->additionals()->sum('amount');
            $totalBudget = $project->price + $additionalsSum;
            $remaining = max(0, $totalBudget - $billed);

            $description = $billed > 0 
                ? "Adicional / Ajuste Proyecto: {$project->name}"
                : "Servicios Profesionales / Proyecto: {$project->name}";

            $prefill['items'][] = [
                'description' => $description,
                'quantity' => 1,
                'price' => $remaining,
            ];
        }

        return Inertia::render('Finance/Invoices/Create', [
            'projects' => Project::select('id', 'name', 'company_id')->get(),
            'companies' => Company::select('id', 'name', 'tax_id')->get(),
            'prefill' => $prefill,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'project_id' => 'nullable|exists:projects,id',
            'date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.client_service_id' => 'nullable|exists:client_services,id',
        ]);

        DB::transaction(function () use ($validated, $request) {
            // Calculate totals
            $total = 0;
            foreach ($validated['items'] as $item) {
                $total += $item['quantity'] * $item['price'];
            }

            // Validate: Total cannot exceed Project Price (Cumulative)
            if (!empty($validated['project_id'])) {
                $project = Project::find($validated['project_id']);
                if ($project && $project->price) {
                    $alreadyBilled = $project->invoices()->where('id', '!=', $request->id)->sum('total'); // Exclude current if updating (though store is create)
                    $newTotal = $alreadyBilled + $total;
                    $additionalsSum = $project->additionals()->sum('amount');
                    $totalBudget = $project->price + $additionalsSum;

                    if ($newTotal > $totalBudget) {
                         $remaining = $totalBudget - $alreadyBilled;
                         throw \Illuminate\Validation\ValidationException::withMessages([
                            'items' => ["El total acumulado ($" . number_format($newTotal, 2) . ") supera el presupuesto total ($" . number_format($totalBudget, 2) . "). Disponible para facturar: $" . number_format($remaining, 2)]
                        ]);
                    }
                }
            }

            // Generate sequential number (Robust logic: Find max sequence for current year)
            $lastInvoice = Invoice::withTrashed()
                ->whereYear('created_at', now()->year)
                ->where('number', 'LIKE', 'INV-' . now()->year . '-%')
                ->latest()
                ->first();

            $sequence = 1;
            if ($lastInvoice) {
                // Extract sequence from INV-YYYY-XXXX
                $parts = explode('-', $lastInvoice->number);
                if (count($parts) === 3) {
                    $sequence = intval($parts[2]) + 1;
                }
            }

            $number = 'INV-' . now()->year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'id' => Str::uuid(),
                'company_id' => $validated['company_id'],
                'project_id' => $validated['project_id'],
                'number' => $number,
                'date' => $validated['date'],
                'due_date' => $validated['due_date'],
                'status' => 'draft', // Always start as draft or sent? Let's say draft.
                'total' => $total,
                'balance_due' => $total,
                'currency' => 'USD',
            ]);

            foreach ($validated['items'] as $item) {
                InvoiceItem::create([
                    'id' => Str::uuid(),
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price'],
                    'client_service_id' => $item['client_service_id'] ?? null,
                ]);
            }
        });

        return redirect()->route('invoices.index')->with('success', 'Factura creada exitosamente.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['company', 'project', 'items', 'payments']);
        
        return Inertia::render('Finance/Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    public function destroy(Invoice $invoice)
    {
        // Prevent deletion of paid invoices
        if ($invoice->status === 'paid' || $invoice->balance_due < $invoice->total) {
            return redirect()->back()->with('error', 'No se puede eliminar una factura con pagos registrados.');
        }

        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Factura eliminada correctamente.');
    }

    /**
     * Show form for editing a draft invoice.
     */
    public function edit(Invoice $invoice)
    {
        // Only allow editing draft invoices
        if ($invoice->status->value !== 'draft') {
            return redirect()->back()->with('error', 'Solo se pueden editar facturas en borrador.');
        }

        $invoice->load(['company', 'project', 'items']);

        return Inertia::render('Finance/Invoices/Edit', [
            'invoice' => $invoice,
            'projects' => Project::select('id', 'name', 'company_id')->get(),
            'companies' => Company::select('id', 'name', 'tax_id')->get(),
        ]);
    }

    /**
     * Update a draft invoice.
     */
    public function update(Request $request, Invoice $invoice)
    {
        // Only allow updating draft invoices
        if ($invoice->status->value !== 'draft') {
            return redirect()->back()->with('error', 'Solo se pueden editar facturas en borrador.');
        }

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'project_id' => 'nullable|exists:projects,id',
            'date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.client_service_id' => 'nullable|exists:client_services,id',
        ]);

        DB::transaction(function () use ($validated, $invoice) {
            // Calculate new total
            $total = 0;
            foreach ($validated['items'] as $item) {
                $total += $item['quantity'] * $item['price'];
            }

            // Validate budget if linked to project
            if (!empty($validated['project_id'])) {
                $project = Project::find($validated['project_id']);
                if ($project && $project->price) {
                    $alreadyBilled = $project->invoices()->where('id', '!=', $invoice->id)->sum('total');
                    $newTotal = $alreadyBilled + $total;
                    $additionalsSum = $project->additionals()->sum('amount');
                    $totalBudget = $project->price + $additionalsSum;

                    if ($newTotal > $totalBudget) {
                        $remaining = $totalBudget - $alreadyBilled;
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'items' => ["El total acumulado ($" . number_format($newTotal, 2) . ") supera el presupuesto ($" . number_format($totalBudget, 2) . "). Disponible: $" . number_format($remaining, 2)]
                        ]);
                    }
                }
            }

            // Update invoice
            $invoice->update([
                'company_id' => $validated['company_id'],
                'project_id' => $validated['project_id'],
                'date' => $validated['date'],
                'due_date' => $validated['due_date'],
                'total' => $total,
                'balance_due' => $total, // Since it's draft, no payments yet
            ]);

            // Delete existing items and recreate
            $invoice->items()->delete();
            
            foreach ($validated['items'] as $item) {
                InvoiceItem::create([
                    'id' => Str::uuid(),
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price'],
                    'client_service_id' => $item['client_service_id'] ?? null,
                ]);
            }
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Factura actualizada correctamente.');
    }

    /**
     * Send payment reminder email to the client's primary contact.
     */
    public function sendReminder(Invoice $invoice)
    {
        $invoice->load('company');
        
        // Find primary contact for this company
        $primaryContact = $invoice->company->users()
            ->where('is_primary_contact', true)
            ->first();
        
        if (!$primaryContact) {
            // Fallback to first user of the company
            $primaryContact = $invoice->company->users()->first();
        }
        
        if (!$primaryContact) {
            return redirect()->back()->with('error', 'No se encontró un contacto para enviar el recordatorio.');
        }
        
        // Send the email
        \Illuminate\Support\Facades\Mail::to($primaryContact->email)
            ->bcc(config('mail.from.address')) // Admin copy
            ->send(new \App\Mail\InvoiceReminderMail($invoice, $primaryContact));
        
        return redirect()->back()->with('success', "Recordatorio enviado a {$primaryContact->email}");
    }

    /**
     * Send invoice to client - changes status from draft to sent and emails the client.
     */
    public function sendToClient(Invoice $invoice)
    {
        // Only allow sending draft invoices
        if ($invoice->status->value !== 'draft') {
            return redirect()->back()->with('error', 'Solo se pueden enviar facturas en borrador.');
        }

        $invoice->load('company');
        
        // Find primary contact for this company
        $primaryContact = $invoice->company->users()
            ->where('is_primary_contact', true)
            ->first();
        
        if (!$primaryContact) {
            // Fallback to first active user of the company
            $primaryContact = $invoice->company->users()
                ->where('is_active', true)
                ->first();
        }
        
        if (!$primaryContact) {
            return redirect()->back()->with('error', 'No se encontró un contacto para enviar la factura. Agregue un usuario al cliente primero.');
        }
        
        // Update invoice status to 'sent'
        $invoice->update([
            'status' => \App\Enums\InvoiceStatus::Sent,
        ]);
        
        // Send the email
        \Illuminate\Support\Facades\Mail::to($primaryContact->email)
            ->bcc(config('mail.from.address')) // Admin copy
            ->send(new \App\Mail\InvoiceSentMail($invoice, $primaryContact));
        
        return redirect()->back()->with('success', "Factura enviada a {$primaryContact->email}");
    }
}
