<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Product;
use App\Services\CatalogSearchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function home(): View
    {
        $latestProductsPreview = $this->latestActiveProducts(4);

        return view('store.home', [
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'featuredProducts' => $latestProductsPreview,
            'latestProductsPreview' => $latestProductsPreview,
        ]);
    }

    public function latest(Request $request): View
    {
        $sort = $this->normalizeSort($request->string('sort')->toString());

        return view('products.latest', [
            'products' => $this->sortProductCollection($this->latestActiveProducts(5), $sort),
            'selectedSort' => in_array($sort, ['alphabetical', 'alphabetical_desc', 'price_asc', 'price_desc'], true) ? $sort : 'default',
        ]);
    }

    public function index(Request $request, CatalogSearchService $catalogSearch): View|RedirectResponse
    {
        $selectedCategory = null;
        $sort = $this->normalizeSort($request->string('sort')->toString());
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

            $products = $this->paginateCollection($searchResults['products'], 16, $request);
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
                'price_asc' => $productsQuery->orderByRaw($this->discountedPriceSortExpression().' asc', ['percentage', 'fixed']),
                'price_desc' => $productsQuery->orderByRaw($this->discountedPriceSortExpression().' desc', ['percentage', 'fixed']),
                default => $productsQuery->orderBy('name'),
            };

            $products = $productsQuery
                ->paginate(20)
                ->withQueryString();
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
            'selectedSort' => in_array($sort, ['alphabetical', 'alphabetical_desc', 'newest', 'price_asc', 'price_desc'], true) ? $sort : 'default',
        ]);
    }

    private function normalizeSort(string $sort): string
    {
        return $sort === 'price' ? 'price_asc' : $sort;
    }

    private function latestActiveProducts(int $limit): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->with('category')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take($limit)
            ->get();
    }

    private function sortProductCollection(Collection $products, string $sort): Collection
    {
        return match ($sort) {
            'alphabetical' => $products
                ->sortBy(fn (Product $product) => mb_strtolower($product->name), SORT_NATURAL)
                ->values(),
            'alphabetical_desc' => $products
                ->sortBy(fn (Product $product) => mb_strtolower($product->name), SORT_NATURAL, true)
                ->values(),
            'price_asc' => $products
                ->sortBy(fn (Product $product) => $this->discountedPriceValue($product), SORT_NUMERIC)
                ->values(),
            'price_desc' => $products
                ->sortBy(fn (Product $product) => $this->discountedPriceValue($product), SORT_NUMERIC, true)
                ->values(),
            default => $products->values(),
        };
    }

    private function discountedPriceValue(Product $product): float
    {
        $salePrice = (float) $product->sale_price;
        $discountValue = (float) $product->discount_value;

        if ($discountValue <= 0) {
            return $salePrice;
        }

        return match ($product->discount_type) {
            'percentage' => max($salePrice - ($salePrice * $discountValue / 100), 0),
            'fixed' => max($salePrice - $discountValue, 0),
            default => $salePrice,
        };
    }

    private function discountedPriceSortExpression(): string
    {
        return '(sale_price - CASE WHEN discount_value > 0 AND discount_type = ? THEN sale_price * discount_value / 100 WHEN discount_value > 0 AND discount_type = ? THEN discount_value ELSE 0 END)';
    }

    private function paginateCollection(Collection $items, int $perPage, Request $request, string $pageName = 'page'): LengthAwarePaginator
    {
        $page = max(LengthAwarePaginator::resolveCurrentPage($pageName), 1);

        return (new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
            ],
        ))->appends($request->except($pageName));
    }

    public function show(Request $request, string $product): View|Response
    {
        $productModel = Product::query()
            ->with('category')
            ->find($product);

        if (! $productModel || (! $productModel->is_active && ! $request->user()?->isAdmin())) {
            return $this->missingProductResponse();
        }

        $switchableOrders = collect();

        if ($request->user() && ! $request->user()->isAdmin()) {
            $switchableOrders = $request->user()
                ->orders()
                ->withCount('items')
                ->where('payment_method', PaymentMethod::ONLINE)
                ->whereIn('payment_status', [PaymentStatus::PENDING, PaymentStatus::FAILED])
                ->whereNotIn('status', [OrderStatus::COMPLETED, OrderStatus::CANCELLED])
                ->whereHas('items', fn ($query) => $query->where('product_id', $productModel->id))
                ->latest()
                ->get();
        }

        return view('products.show', [
            'product' => $productModel,
            'adminCategories' => $request->user()?->isAdmin()
                ? Category::query()
                    ->orderByDesc('is_active')
                    ->orderBy('name')
                    ->get()
                : collect(),
            'relatedProducts' => Product::query()
                ->where('is_active', true)
                ->whereKeyNot($productModel->id)
                ->when($productModel->category_id, fn ($query) => $query->where('category_id', $productModel->category_id))
                ->take(3)
                ->get(),
            'switchableOrders' => $switchableOrders,
        ]);
    }

    protected function missingProductResponse(): Response
    {
        return response()->view('products.not-found', [
            'suggestedProducts' => $this->latestActiveProducts(3),
        ], 404);
    }
}
