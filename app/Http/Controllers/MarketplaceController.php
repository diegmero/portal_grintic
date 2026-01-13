<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Inertia\Inertia;
use App\Models\Product;
use App\Models\ProductCategory;


class MarketplaceController extends Controller
{
    /**
     * Display a listing of products/categories for the client.
     */
    public function index(Request $request)
    {
        // Only active products
        $products = Product::where('is_active', true)
            ->with(['productCategory'])
            ->when($request->category, function ($q, $cat) {
                 $q->whereHas('productCategory', fn($pc) => $pc->where('slug', $cat)->orWhere('id', $cat));
            })
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('base_price', 'asc')
            ->paginate(12)
            ->withQueryString();

        $categories = ProductCategory::withCount(['products' => fn($q) => $q->where('is_active', true)])->get();

        return Inertia::render('Marketplace/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }

        $product->load(['productCategory', 'addons' => fn($q) => $q->where('is_active', true)]);

        return Inertia::render('Marketplace/Show', [
            'product' => $product,
        ]);
    }
}
