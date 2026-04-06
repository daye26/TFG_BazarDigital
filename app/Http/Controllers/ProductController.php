<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\CatalogSearchService;
use Illuminate\Http\RedirectResponse;
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

    public function index(Request $request, CatalogSearchService $catalogSearch): View|RedirectResponse
    {
        $selectedCategory = null;
        $sort = $request->string('sort')->toString();
        $searchQuery = trim($request->string('q')->toString());
        $categoryFilter = trim($request->string('category')->toString());
        $relatedCategories = collect();

        if ($searchQuery !== '' && $matchedBarcodeProduct = $catalogSearch->findProductByExactBarcode($searchQuery)) {
            return redirect()->route('products.show', $matchedBarcodeProduct);
        }

        if ($categoryFilter !== '') {
            $selectedCategory = Category::query()
                ->where('is_active', true)
                ->when(
                    ctype_digit($categoryFilter),
                    fn ($query) => $query->whereKey((int) $categoryFilter),
                    fn ($query) => $query->where('url', $categoryFilter),
                )
                ->first();
        }

        if ($searchQuery !== '') {
            $searchResults = $catalogSearch->search(
                $searchQuery,
                categoryId: $selectedCategory?->id,
                productSort: $sort,
            );

            $products = $searchResults['products'];
            $relatedCategories = $selectedCategory ? collect() : $searchResults['categories'];
        } else {
            $productsQuery = Product::query()
                ->where('is_active', true)
                ->with('category')
                ->when($selectedCategory, fn ($query) => $query->where('category_id', $selectedCategory->id));

            match ($sort) {
                'alphabetical' => $productsQuery->orderBy('name'),
                'alphabetical_desc' => $productsQuery->orderByDesc('name'),
                'newest' => $productsQuery->latest(),
                'price' => $productsQuery->orderByRaw('(sale_price - CASE WHEN discount_value > 0 AND discount_type = ? THEN sale_price * discount_value / 100 WHEN discount_value > 0 AND discount_type = ? THEN discount_value ELSE 0 END) asc', ['percentage', 'fixed']),
                default => $productsQuery->orderBy('name'),
            };

            $products = $productsQuery->get();
        }

        return view('products.index', [
            'products' => $products,
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'selectedCategory' => $selectedCategory,
            'searchQuery' => $searchQuery,
            'relatedCategories' => $relatedCategories,
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
