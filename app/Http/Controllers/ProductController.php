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
            ->with(['productCategory', 'addons']) // Eager loading
            ->when($request->category, function ($q, $cat) {
                // ... logic same
                $q->whereHas('productCategory', fn($pc) => $pc->where('id', $cat)) // UUID
                  ->orWhere('category', $cat);
            })
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->when($request->search, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(10)
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
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'addons' => 'nullable|array',
            'addons.*.name' => 'required|string|max:255',
            'addons.*.additional_price' => 'required|numeric|min:0',
            'addons.*.is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        // Fallback for legacy column
        $category = ProductCategory::find($validated['product_category_id']);
        $validated['category'] = $category->slug ?? 'other';

        $product = Product::create($validated);

        // Handle Addons
        if (!empty($validated['addons'])) {
            $product->addons()->createMany($validated['addons']);
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
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'addons' => 'nullable|array',
            'addons.*.id' => 'nullable|uuid',
            'addons.*.name' => 'required|string|max:255',
            'addons.*.additional_price' => 'required|numeric|min:0',
            'addons.*.is_active' => 'boolean',
        ]);

        if (empty($validated['slug']) && $product->name !== $validated['name']) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        // Update legacy column just in case
        $category = ProductCategory::find($validated['product_category_id']);
        $validated['category'] = $category->slug ?? 'other';

        $product->update($validated);

        // Sync Addons
        if (isset($validated['addons'])) {
            $currentAddonIds = collect($validated['addons'])->pluck('id')->filter();
            // Delete addons not present
            $product->addons()->whereNotIn('id', $currentAddonIds)->delete();

            foreach ($validated['addons'] as $addonData) {
                $product->addons()->updateOrCreate(
                    ['id' => $addonData['id'] ?? null],
                    [
                        'name' => $addonData['name'],
                        'additional_price' => $addonData['additional_price'],
                        'is_active' => $addonData['is_active'] ?? true,
                    ]
                );
            }
        } else {
             // If addons not provided (or empty array explicitly passed meaning clear all), delete all? 
             // Usually safe to assume if key exists but empty. If key missing, do nothing?
             // Front end sends empty array if clearing.
             if (array_key_exists('addons', $validated)) {
                 $product->addons()->delete();
             }
        }

        return back()->with('success', 'Producto actualizado correctamente.');
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
