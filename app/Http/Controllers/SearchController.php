<?php

namespace App\Http\Controllers;

use App\Services\CatalogSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function suggestions(Request $request, CatalogSearchService $catalogSearch): JsonResponse
    {
        $query = trim($request->string('q')->toString());

        if (! $catalogSearch->shouldProvideSuggestions($query)) {
            return response()->json([
                'query' => $query,
                'categories' => [],
                'products' => [],
            ]);
        }

        $results = $catalogSearch->search(
            $query,
            productSort: 'default',
            productLimit: 6,
            categoryLimit: 4,
        );

        return response()->json([
            'query' => $query,
            'categories' => $results['categories']->map(fn ($category) => [
                'name' => $category->name,
                'description' => Str::limit((string) $category->description, 70),
                'url' => route('products.index', ['category' => $category->filter_key]),
                'matchLabel' => $category->search_match_label ?? 'Categoria',
            ])->values(),
            'products' => $results['products']->map(fn ($product) => [
                'name' => $product->name,
                'category' => $product->category?->name ?? 'Sin categoria',
                'barcode' => $product->barcode,
                'url' => route('products.show', $product),
                'matchLabel' => $product->search_match_label ?? 'Producto',
            ])->values(),
        ]);
    }
}
