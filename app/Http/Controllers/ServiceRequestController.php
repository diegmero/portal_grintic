<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\ServiceRequest;

use Illuminate\Support\Facades\Auth;

class ServiceRequestController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'configuration' => 'nullable|array', // Selected addons: [{id, name, price}]
            'total_price' => 'required|numeric|min:0',
        ]);

        // Security check: backend recalculation of price could be safer, 
        // but for now relying on frontend calculated total_price validated against min:0.
        // Ideally we should recalculate server-side:
        // $total = $product->base_price + addons_price...

        $serviceRequest = ServiceRequest::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'configuration' => $validated['configuration'],
            'total_price' => $validated['total_price'],
            'status' => 'pending',
        ]);

        return redirect()->route('marketplace.index')->with('success', 'Solicitud enviada correctamente. Te contactaremos pronto.');
    }
}
