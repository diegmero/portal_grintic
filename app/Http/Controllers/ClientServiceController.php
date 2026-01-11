<?php

namespace App\Http\Controllers;

use App\Enums\ClientServiceStatus;
use App\Models\ClientService;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientServiceController extends Controller
{
    // Fetch active services for a specific client (JSON for invoices)
    public function getActiveServices($clientId)
    {
        $services = ClientService::where('company_id', $clientId)
            ->where('status', 'active')
            ->with(['product:id,name,base_price,billing_cycle', 'invoiceItems' => function($q) {
                $q->latest()->select('id', 'client_service_id', 'created_at');
            }])
            ->get(['id', 'product_id', 'custom_price', 'notes', 'start_date', 'end_date'])
            ->map(function ($service) {
                $lastInvoice = $service->invoiceItems->first();
                $billingCycle = $service->product->billing_cycle;
                
                $isBillable = true;
                $statusMessage = 'Listo para facturar';
                $nextBillingDate = null;

                // 1. New Service (Never billed) -> Always Billable
                if (!$lastInvoice) {
                    $isBillable = true;
                    $statusMessage = 'Nueva Alta (Primer Cobro)';
                    $nextBillingDate = now();
                } 
                // 2. Existing Service Logic
                else {
                    $lastDate = $lastInvoice->created_at;
                    
                    if ($billingCycle->value === 'monthly') {
                        // Anchor to Start Date Day
                        // Find the NEXT billing date relative to NOW, essentially the next monthiversary
                        $startDate = $service->start_date;
                        $nextBillingDate = $startDate->copy();
                        
                        // Advance until we are in the future (or today) relative to the last invoice?
                        // Actually, we want the *next* due date.
                        // If Start Jan 15. Today Jan 11. Next is Jan 15.
                        // If Start Jan 15. Today Jan 16. Next is Feb 15.
                        // Valid until = Next Billing Date.
                        
                        // Simple algo: Set next billing to current month's occurrence of start day
                        $candidateDate = now()->setDay($startDate->day);
                        
                        // Handle short months (e.g. 31st in Feb) - Laravel/Carbon handles this by spilling over? 
                        // ->day() might overflow. safe method: create from format or clamp.
                        // For simplicity, let's just add months from start until > now.
                        
                        $monthsDiff = $startDate->diffInMonths(now());
                        $nextBillingDate = $startDate->copy()->addMonths($monthsDiff);
                        if ($nextBillingDate->isPast()) {
                            $nextBillingDate->addMonth();
                        }

                        // Window: 2 days before due date
                        $windowStart = $nextBillingDate->copy()->subDays(2);
                        
                        // Check if we ALREADY paid this specific cycle?
                        // If last invoice was within 20 days of this due date? 
                        // Simpler: If last invoice was created AFTER the 'previous' cycle date?
                        // Let's stick to the user's request: "Ciclo vigente hasta X".
                        // Logic: IF now < windowStart -> WAIT.
                        
                        // Double check: if last invoice is VERY fresh (e.g. < 5 days), assume paid.
                        if ($lastInvoice->created_at->diffInDays(now()) < 5) {
                             $isBillable = false;
                             // Recalculate next one
                             $nextBillingDate->addMonth();
                             $statusMessage = 'Ciclo vigente hasta ' . $nextBillingDate->format('d/m/Y');
                        } elseif (now()->lt($windowStart)) {
                            $isBillable = false;
                            $statusMessage = 'Ciclo vigente hasta ' . $nextBillingDate->format('d/m/Y');
                        }
                    } elseif ($billingCycle->value === 'annual') {
                         // Similar logic but yearly
                         $startDate = $service->start_date;
                         $yearsDiff = $startDate->diffInYears(now());
                         $nextBillingDate = $startDate->copy()->addYears($yearsDiff);
                         if ($nextBillingDate->isPast()) {
                             $nextBillingDate->addYear();
                         }
                         
                         $windowStart = $nextBillingDate->copy()->subDays(10);
                         
                         if ($lastInvoice->created_at->diffInDays(now()) < 10) {
                             $isBillable = false;
                             $nextBillingDate->addYear();
                             $statusMessage = 'Renovación hasta ' . $nextBillingDate->format('d/m/Y');
                         } elseif (now()->lt($windowStart)) {
                            $isBillable = false;
                            $statusMessage = 'Renovación hasta ' . $nextBillingDate->format('d/m/Y');
                        }
                    } elseif ($billingCycle->value === 'lifetime') {
                         $isBillable = false;
                         $statusMessage = 'Pago Único ya facturado';
                    }
                }

                // FINAL CHECK: Does next billing date exceed service end date?
                if ($nextBillingDate && $service->end_date && $nextBillingDate->gt($service->end_date)) {
                    $isBillable = false;
                    $statusMessage = 'Servicio finaliza el ' . $service->end_date->format('d/m/Y');
                    $nextBillingDate = null;
                }

                return [
                    'id' => $service->id,
                    'product_id' => $service->product_id,
                    'custom_price' => $service->custom_price,
                    'notes' => $service->notes,
                    'product' => $service->product,
                    'is_billable' => $isBillable,
                    'status_message' => $statusMessage,
                    'next_billing_date' => $nextBillingDate ? $nextBillingDate->format('Y-m-d') : null,
                ];
            });

        return response()->json($services);
    }

    public function index(Request $request)
    {
        $services = ClientService::query()
            ->with(['company', 'product'])
            ->with(['invoiceItems' => function ($query) {
                $query->latest()->with('invoice');
            }])
            ->when($request->company_id, fn($q, $id) => $q->where('company_id', $id))
            ->when($request->product_id, fn($q, $id) => $q->where('product_id', $id))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->search, function($q, $search) {
                $q->whereHas('company', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('product', fn($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Services/Index', [
            'services' => $services,
            'companies' => Company::orderBy('name')->get(['id', 'name']),
            'products' => Product::active()->orderBy('name')->get(['id', 'name', 'base_price', 'type', 'billing_cycle']),
            'statuses' => collect(ClientServiceStatus::cases())->map(fn($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
            'filters' => $request->only(['company_id', 'product_id', 'status', 'search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|uuid|exists:companies,id',
            'product_id' => 'required|uuid|exists:products,id',
            'custom_price' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|string',
            'credentials' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        // Convert credentials object to JSON string
        if (isset($validated['credentials'])) {
            $validated['credentials'] = json_encode($validated['credentials']);
        }

        ClientService::create($validated);

        return back()->with('success', 'Servicio asignado exitosamente.');
    }

    public function show(ClientService $service)
    {
        $service->load(['company', 'product']);

        return Inertia::render('Services/Show', [
            'service' => $service,
        ]);
    }

    public function update(Request $request, ClientService $service)
    {
        $validated = $request->validate([
            'custom_price' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|string',
            'credentials' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        // Convert credentials object to JSON string
        if (isset($validated['credentials'])) {
            $validated['credentials'] = json_encode($validated['credentials']);
        }

        $service->update($validated);

        return back()->with('success', 'Servicio actualizado exitosamente.');
    }

    public function destroy(ClientService $service)
    {
        $service->delete();

        return back()->with('success', 'Servicio eliminado exitosamente.');
    }
}
