<?php

namespace App\Http\Controllers;

use App\Enums\BillingCycle;
use App\Enums\ProductCategory;
use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Clients CAN view products (Marketplace), but Frontend should hide Edit/Delete buttons.
        
        $products = Product::query()
            ->when($request->category, fn($q, $category) => $q->where('category', $category))
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->when($request->search, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => collect(ProductCategory::cases())->map(fn($c) => [
                'value' => $c->value,
                'label' => $c->label(),
            ]),
            'types' => collect(ProductType::cases())->map(fn($t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
            'billingCycles' => collect(BillingCycle::cases())->map(fn($b) => [
                'value' => $b->value,
                'label' => $b->label(),
            ]),
            'filters' => $request->only(['category', 'type', 'search']),
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'type' => 'required|string',
            'billing_cycle' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Product::create($validated);

        return back()->with('success', 'Producto creado exitosamente.');
    }

    public function update(Request $request, Product $product)
    {
        if ($request->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'type' => 'required|string',
            'billing_cycle' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return back()->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Product $product)
    {
        if (request()->user()->company_id) {
            abort(403);
        }

        // Check if product has active services
        if ($product->clientServices()->exists()) {
            return back()->with('error', 'No se puede eliminar el producto porque tiene servicios asociados.');
        }

        $product->delete();

        return back()->with('success', 'Producto eliminado exitosamente.');
    }
}
