<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function home(): View
    {
        return view('store.home', [
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'featuredProducts' => Product::query()
                ->where('is_active', true)
                ->with('category')
                ->latest()
                ->take(4)
                ->get(),
        ]);
    }

    public function index(Request $request): View
    {
        $selectedCategory = null;
        $sort = $request->string('sort')->toString();

        $productsQuery = Product::query()
            ->where('is_active', true)
            ->with('category')
            ->when($request->filled('category'), function ($query) use ($request, &$selectedCategory) {
                $selectedCategory = Category::query()
                    ->where('is_active', true)
                    ->where('url', $request->string('category'))
                    ->first();

                if ($selectedCategory) {
                    $query->where('category_id', $selectedCategory->id);
                }
            });

        match ($sort) {
            'alphabetical' => $productsQuery->orderBy('name'),
            'alphabetical_desc' => $productsQuery->orderByDesc('name'),
            'newest' => $productsQuery->latest(),
            'price' => $productsQuery->orderByRaw('(sale_price - CASE WHEN discount_value > 0 AND discount_type = ? THEN sale_price * discount_value / 100 WHEN discount_value > 0 AND discount_type = ? THEN discount_value ELSE 0 END) asc', ['percentage', 'fixed']),
            default => $productsQuery->orderBy('name'),
        };

        $products = $productsQuery->get();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'selectedCategory' => $selectedCategory,
            'selectedSort' => in_array($sort, ['alphabetical', 'alphabetical_desc', 'newest', 'price'], true) ? $sort : 'default',
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        return view('products.show', [
            'product' => $product->load('category'),
            'relatedProducts' => Product::query()
                ->where('is_active', true)
                ->whereKeyNot($product->id)
                ->when($product->category_id, fn ($query) => $query->where('category_id', $product->category_id))
                ->take(3)
                ->get(),
        ]);
    }
}
