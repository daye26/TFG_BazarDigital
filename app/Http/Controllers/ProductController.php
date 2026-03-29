<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
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
            'latestProductsPreview' => Product::query()
                ->where('is_active', true)
                ->with('category')
                ->latest()
                ->take(4)
                ->get(),
        ]);
    }

    public function latest(Request $request): View
    {
        $sort = $request->string('sort')->toString();

        $productsQuery = Product::query()
            ->where('is_active', true)
            ->with('category');

        match ($sort) {
            'alphabetical' => $productsQuery->orderBy('name'),
            'alphabetical_desc' => $productsQuery->orderByDesc('name'),
            'price' => $productsQuery->orderByRaw('(sale_price - CASE WHEN discount_value > 0 AND discount_type = ? THEN sale_price * discount_value / 100 WHEN discount_value > 0 AND discount_type = ? THEN discount_value ELSE 0 END) asc', ['percentage', 'fixed']),
            default => $productsQuery->latest(),
        };

        return view('products.latest', [
            'products' => $productsQuery
                ->take(20)
                ->get(),
            'selectedSort' => in_array($sort, ['alphabetical', 'alphabetical_desc', 'price'], true) ? $sort : 'default',
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

    public function show(string $product): View|Response
    {
        $productModel = Product::query()
            ->with('category')
            ->find($product);

        if (! $productModel || ! $productModel->is_active) {
            return $this->missingProductResponse();
        }

        return view('products.show', [
            'product' => $productModel,
            'relatedProducts' => Product::query()
                ->where('is_active', true)
                ->whereKeyNot($productModel->id)
                ->when($productModel->category_id, fn ($query) => $query->where('category_id', $productModel->category_id))
                ->take(3)
                ->get(),
        ]);
    }

    protected function missingProductResponse(): Response
    {
        return response()->view('products.not-found', [
            'suggestedProducts' => Product::query()
                ->where('is_active', true)
                ->with('category')
                ->latest()
                ->take(3)
                ->get(),
        ], 404);
    }
}
