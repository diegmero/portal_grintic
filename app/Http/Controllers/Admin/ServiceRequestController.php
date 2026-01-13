<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = ServiceRequest::query()
            ->with(['user', 'product'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Requests/Index', [
            'requests' => $requests,
            'filters' => $request->only(['status']),
        ]);
    }

    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,completed,pending',
            'notes' => 'nullable|string'
        ]);

        $serviceRequest->update([
            'status' => $validated['status']
        ]);

        // Logic to automatically create ClientService if approved could go here
        // For now, just status update.

        return back()->with('success', 'Estado de la solicitud actualizado.');
    }
}
