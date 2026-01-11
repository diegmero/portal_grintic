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
    public function index(Request $request)
    {
        $services = ClientService::query()
            ->with(['company', 'product'])
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
