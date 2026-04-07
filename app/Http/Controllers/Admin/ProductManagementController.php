<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\CatalogSearchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductManagementController extends Controller
{
    public function index(): View
    {
        $totalProducts = Product::query()->count();
        $activeProducts = Product::query()->where('is_active', true)->count();
        $pendingOrders = Order::query()->where('status', OrderStatus::PENDING)->count();
        $readyOrders = Order::query()->where('status', OrderStatus::READY)->count();

        return view('admin.index', [
            'latestProducts' => Product::query()
                ->with('category')
                ->latest()
                ->take(8)
                ->get(),
            'stats' => [
                'pending_orders' => $pendingOrders,
                'ready_orders' => $readyOrders,
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'inactive_products' => max($totalProducts - $activeProducts, 0),
                'active_categories' => Category::query()->where('is_active', true)->count(),
            ],
        ]);
    }

    public function manage(Request $request, CatalogSearchService $catalogSearch): View
    {
        $searchQuery = trim($request->string('q')->toString());
        $productScope = $request->string('scope')->toString();
        $selectedProduct = null;

        if ($request->filled('product')) {
            $selectedProduct = Product::query()
                ->with('category')
                ->findOrFail($request->integer('product'));
        }

        $products = $searchQuery === ''
            ? Product::query()
                ->with('category')
                ->when(
                    $productScope === 'active',
                    fn ($query) => $query->where('is_active', true)
                )
                ->when(
                    $productScope === 'inactive',
                    fn ($query) => $query->where('is_active', false)
                )
                ->orderByDesc('updated_at')
                ->orderByDesc('created_at')
                ->get()
            : $catalogSearch->searchAdminProducts($searchQuery)
                ->when(
                    $productScope === 'active',
                    fn ($collection) => $collection->where('is_active', true)
                )
                ->when(
                    $productScope === 'inactive',
                    fn ($collection) => $collection->where('is_active', false)
                )
                ->values();

        return view('admin.products.manage', [
            'products' => $products,
            'selectedProduct' => $selectedProduct,
            'categories' => Category::query()
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
            'searchQuery' => $searchQuery,
            'productScope' => in_array($productScope, ['active', 'inactive'], true) ? $productScope : 'all',
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function createCategory(): View
    {
        return view('admin.categories.create');
    }

    public function manageCategories(Request $request): View
    {
        $categoryScope = $request->string('scope')->toString();
        $selectedCategory = null;

        if ($request->filled('category')) {
            $selectedCategory = Category::query()
                ->withCount('products')
                ->findOrFail($request->integer('category'));
        }

        return view('admin.categories.manage', [
            'categories' => Category::query()
                ->withCount('products')
                ->when(
                    $categoryScope === 'active',
                    fn ($query) => $query->where('is_active', true)
                )
                ->when(
                    $categoryScope === 'inactive',
                    fn ($query) => $query->where('is_active', false)
                )
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
            'selectedCategory' => $selectedCategory,
            'categoryScope' => in_array($categoryScope, ['active', 'inactive'], true) ? $categoryScope : 'all',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'barcode' => ['required', 'string', 'max:255', 'unique:products,barcode'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'tax' => ['required', 'integer', 'min:0', 'max:99'],
            'cost_price' => ['required', 'numeric', 'gt:0'],
            'sale_price' => ['required', 'numeric', 'gt:0'],
            'margin_multiplier' => ['required', 'numeric', 'gt:0'],
            'pricing_mode' => ['nullable', 'in:margin,sale_price'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'discount_type' => ['required', 'in:fixed,percentage'],
            'qty' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=3000,max_height=3000'],
            'url' => ['nullable', 'string', 'max:255', 'unique:products,url'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $pricing = $this->resolvePricing($validated);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->storePublicly('products', 'public')
            : null;

        Product::create([
            'barcode' => $validated['barcode'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'tax' => $pricing['tax'],
            'cost_price' => $pricing['cost_price'],
            'sale_price' => $pricing['sale_price'],
            'margin_multiplier' => $pricing['margin_multiplier'],
            'discount_value' => number_format((float) ($validated['discount_value'] ?? 0), 2, '.', ''),
            'discount_type' => $validated['discount_type'],
            'qty' => (int) ($validated['qty'] ?? 0),
            'image' => $imagePath,
            'url' => ($validated['url'] ?? null) ?: null,
            'category_id' => $validated['category_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.index')
            ->with('status', 'Producto creado correctamente.');
    }

    public function updateDetails(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'barcode' => ['required', 'string', 'max:255', Rule::unique('products', 'barcode')->ignore($product)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'qty' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=3000,max_height=3000'],
            'remove_image' => ['nullable', 'boolean'],
            'url' => ['nullable', 'string', 'max:255', Rule::unique('products', 'url')->ignore($product)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'is_active' => ['nullable', 'boolean'],
            'return_query' => ['nullable', 'string'],
            'return_context' => ['nullable', 'in:manage,show'],
            'return_tab' => ['nullable', 'in:general,price'],
        ]);

        $currentImagePath = $product->image;
        $nextImagePath = $currentImagePath;

        if ($request->boolean('remove_image')) {
            $nextImagePath = null;
        }

        if ($request->hasFile('image')) {
            $nextImagePath = $request->file('image')->storePublicly('products', 'public');
        }

        $product->update([
            'barcode' => $validated['barcode'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'qty' => (int) ($validated['qty'] ?? 0),
            'image' => $nextImagePath,
            'url' => ($validated['url'] ?? null) ?: null,
            'category_id' => $validated['category_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($currentImagePath && $currentImagePath !== $nextImagePath) {
            Storage::disk('public')->delete($currentImagePath);
        }

        return $this->redirectToManage(
            $product,
            $validated['return_query'] ?? '',
            'Caracteristicas del producto actualizadas correctamente.',
            $validated['return_context'] ?? 'manage',
            $validated['return_tab'] ?? 'general',
        );
    }

    public function updatePricing(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'tax' => ['required', 'integer', 'min:0', 'max:99'],
            'cost_price' => ['required', 'numeric', 'gt:0'],
            'sale_price' => ['required', 'numeric', 'gt:0'],
            'margin_multiplier' => ['required', 'numeric', 'gt:0'],
            'pricing_mode' => ['nullable', 'in:margin,sale_price'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'discount_type' => ['required', 'in:fixed,percentage'],
            'return_query' => ['nullable', 'string'],
            'return_context' => ['nullable', 'in:manage,show'],
            'return_tab' => ['nullable', 'in:general,price'],
        ]);

        $pricing = $this->resolvePricing($validated);

        $product->update([
            'tax' => $pricing['tax'],
            'cost_price' => $pricing['cost_price'],
            'sale_price' => $pricing['sale_price'],
            'margin_multiplier' => $pricing['margin_multiplier'],
            'discount_value' => number_format((float) ($validated['discount_value'] ?? 0), 2, '.', ''),
            'discount_type' => $validated['discount_type'],
        ]);

        return $this->redirectToManage(
            $product,
            $validated['return_query'] ?? '',
            'Precio del producto actualizado correctamente.',
            $validated['return_context'] ?? 'manage',
            $validated['return_tab'] ?? 'price',
        );
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'url' => ['nullable', 'string', 'max:255', 'unique:categories,url'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Category::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'url' => ($validated['url'] ?? null) ?: null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.index')
            ->with('status', 'Categoria creada correctamente.');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'url' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'url')->ignore($category)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'url' => ($validated['url'] ?? null) ?: null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.categories.manage', ['category' => $category->id])
            ->with('status', 'Categoria actualizada correctamente.');
    }

    private function resolvePricing(array $validated): array
    {
        $costPrice = round((float) $validated['cost_price'], 4);
        $tax = (int) $validated['tax'];
        $taxFactor = 1 + ($tax / 100);
        $pricingMode = $validated['pricing_mode'] ?? 'margin';

        if ($pricingMode === 'sale_price') {
            $salePrice = round((float) $validated['sale_price'], 2);
            $marginMultiplier = round($salePrice / ($costPrice * $taxFactor), 4);
            $salePrice = round($costPrice * $marginMultiplier * $taxFactor, 2);
        } else {
            $marginMultiplier = round((float) $validated['margin_multiplier'], 4);
            $salePrice = round($costPrice * $marginMultiplier * $taxFactor, 2);
        }

        return [
            'tax' => $tax,
            'cost_price' => number_format($costPrice, 4, '.', ''),
            'sale_price' => number_format($salePrice, 2, '.', ''),
            'margin_multiplier' => number_format($marginMultiplier, 4, '.', ''),
        ];
    }

    private function redirectToManage(Product $product, string $searchQuery, string $status, string $returnContext = 'manage', string $returnTab = 'general'): RedirectResponse
    {
        if ($returnContext === 'show') {
            return redirect()
                ->route('products.show', array_filter([
                    'product' => $product->id,
                    'edit' => 1,
                    'tab' => $returnTab !== '' ? $returnTab : null,
                ]))
                ->with('status', $status);
        }

        $query = trim($searchQuery);

        return redirect()
            ->route('admin.products.manage', array_filter([
                'product' => $product->id,
                'q' => $query !== '' ? $query : null,
            ]))
            ->with('status', $status);
    }
}
