<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\ServiceRequest;

use Illuminate\Support\Facades\Auth;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $requests = ServiceRequest::query()
            ->where('company_id', $user->company_id)
            ->with(['product', 'user']) // Load user to see who requested it
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return \Inertia\Inertia::render('Portal/Requests/Index', [
            'requests' => $requests,
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'configuration' => 'nullable|array', // Selected addons: [{id, name, price}]
            'total_price' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();

        // Ensure user belongs to a company
        if (!$user->company_id) {
             return redirect()->back()->withErrors(['error' => 'Debes pertenecer a una empresa para realizar solicitudes.']);
        }

        $serviceRequest = ServiceRequest::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'configuration' => $validated['configuration'],
            'total_price' => $validated['total_price'],
            'status' => 'pending',
        ]);

        return redirect()->route('portal.requests.index')->with('success', 'Solicitud enviada correctamente. Te contactaremos pronto.');
    }
}
