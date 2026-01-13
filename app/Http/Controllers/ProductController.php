<?php

namespace App\Http\Controllers;

use App\Enums\BillingCycle;
use App\Enums\ProductType;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Clients CAN view products (Marketplace), but Frontend should hide Edit/Delete buttons.
        
        $products = Product::query()
            ->with(['productCategory', 'variants']) // Eager load new relationship
            // Filter by product_category_id if UUID is passed, or legacy 'category' string if old link
            ->when($request->category, function ($q, $category) {
                // If it looks like a UUID
                if (\Illuminate\Support\Str::isUuid($category)) {
                    $q->where('product_category_id', $category);
                } else {
                    $q->where('category', $category);
                }
            })
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->when($request->search, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => ProductCategory::orderBy('name')->get()->map(fn($c) => [
                'value' => $c->id, // Passing UUID now
                'label' => $c->name,
                'slug' => $c->slug, // Optional helper
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
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'product_category_id' => 'required|exists:product_categories,id',
            'category' => 'nullable|string',
            'type' => 'required|string',
            'billing_cycle' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.additional_price' => 'required|numeric|min:0',
            'variants.*.is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        // Fallback for legacy column
        $category = ProductCategory::find($validated['product_category_id']);
        $validated['category'] = $category->slug ?? 'other';

        $product = Product::create($validated);

        // Handle Variants
        if (!empty($validated['variants'])) {
            $product->variants()->createMany($validated['variants']);
        }

        return back()->with('success', 'Producto creado exitosamente.');
    }

    public function update(Request $request, Product $product)
    {
        if ($request->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'product_category_id' => 'required|exists:product_categories,id',
            'type' => 'required|string',
            'billing_cycle' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|uuid',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.additional_price' => 'required|numeric|min:0',
            'variants.*.is_active' => 'boolean',
        ]);

        if (empty($validated['slug']) && $product->name !== $validated['name']) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        // Update legacy column just in case
        $category = ProductCategory::find($validated['product_category_id']);
        $validated['category'] = $category->slug ?? 'other';

        $product->update($validated);

        // Sync Variants
        // Strategy: We receive a list. If ID exists, update. If no ID, create.
        // IDs not in the list but existing in DB associated with product should be deleted?
        // Yes, "Sync" usually implies removing missing ones.

        $receivedVariantIds = collect($validated['variants'] ?? [])
            ->pluck('id')
            ->filter()
            ->toArray();

        // 1. Delete removed variants
        $product->variants()->whereNotIn('id', $receivedVariantIds)->delete();

        // 2. Update or Create
        if (!empty($validated['variants'])) {
            foreach ($validated['variants'] as $variantData) {
                $product->variants()->updateOrCreate(
                    ['id' => $variantData['id'] ?? \Illuminate\Support\Str::uuid()],
                    [
                        'name' => $variantData['name'],
                        'additional_price' => $variantData['additional_price'],
                        'is_active' => $variantData['is_active'] ?? true,
                    ]
                );
            }
        }

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
