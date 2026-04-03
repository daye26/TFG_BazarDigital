<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductManagementController extends Controller
{
    public function index(): View
    {
        $totalProducts = Product::query()->count();
        $activeProducts = Product::query()->where('is_active', true)->count();

        return view('admin.index', [
            'latestProducts' => Product::query()
                ->with('category')
                ->latest()
                ->take(8)
                ->get(),
            'stats' => [
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'inactive_products' => max($totalProducts - $activeProducts, 0),
                'active_categories' => Category::query()->where('is_active', true)->count(),
            ],
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

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->storePublicly('products', 'public')
            : null;

        Product::create([
            'barcode' => $validated['barcode'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'tax' => $tax,
            'cost_price' => number_format($costPrice, 4, '.', ''),
            'sale_price' => number_format($salePrice, 2, '.', ''),
            'margin_multiplier' => number_format($marginMultiplier, 4, '.', ''),
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
}
