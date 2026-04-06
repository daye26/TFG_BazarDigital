<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_suggestions_return_matching_categories_and_products(): void
    {
        $category = Category::create([
            'name' => 'Limpieza',
            'description' => 'Productos para la casa',
            'url' => 'limpieza',
            'is_active' => true,
        ]);

        Product::create([
            'barcode' => '1234567890123',
            'name' => 'Detergente liquido',
            'description' => 'Aroma fresco',
            'tax' => 21,
            'cost_price' => 4.0000,
            'sale_price' => 7.99,
            'margin_multiplier' => 2.0000,
            'discount_value' => 0,
            'discount_type' => 'fixed',
            'qty' => 8,
            'url' => 'detergente-liquido',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $response = $this->getJson(route('search.suggestions', ['q' => 'limpie']));

        $response
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Limpieza',
                'matchLabel' => 'Categoria',
                'url' => route('products.index', ['category' => $category->filter_key]),
            ])
            ->assertJsonFragment([
                'name' => 'Detergente liquido',
                'matchLabel' => 'Categoria',
            ])
            ->assertJsonMissing([
                'url' => route('products.index', ['category' => $category->filter_key, 'q' => 'limpie']),
            ]);
    }

    public function test_search_suggestions_support_partial_barcode_matches(): void
    {
        $category = Category::create([
            'name' => 'Oficina',
            'description' => 'Material de oficina',
            'url' => 'oficina',
            'is_active' => true,
        ]);

        Product::create([
            'barcode' => '9876543210123',
            'name' => 'Agenda semanal',
            'description' => 'Tapa dura',
            'tax' => 21,
            'cost_price' => 3.0000,
            'sale_price' => 6.50,
            'margin_multiplier' => 2.0000,
            'discount_value' => 0,
            'discount_type' => 'fixed',
            'qty' => 12,
            'url' => 'agenda-semanal',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $response = $this->getJson(route('search.suggestions', ['q' => '6543']));

        $response
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Agenda semanal',
                'barcode' => '9876543210123',
                'matchLabel' => 'Codigo de barras',
            ]);
    }

    public function test_catalog_search_accepts_text_barcode_and_typo_queries(): void
    {
        $category = Category::create([
            'name' => 'Limpieza',
            'description' => 'Categoria de test',
            'url' => 'limpieza',
            'is_active' => true,
        ]);

        Product::create([
            'barcode' => '1234500099999',
            'name' => 'Detergente liquido',
            'description' => 'Para ropa delicada',
            'tax' => 21,
            'cost_price' => 4.0000,
            'sale_price' => 9.90,
            'margin_multiplier' => 2.0000,
            'discount_value' => 0,
            'discount_type' => 'fixed',
            'qty' => 10,
            'url' => 'detergente-liquido',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        Product::create([
            'barcode' => '5555500011111',
            'name' => 'Esponja suave',
            'description' => 'Para cocina',
            'tax' => 21,
            'cost_price' => 1.0000,
            'sale_price' => 2.50,
            'margin_multiplier' => 2.0000,
            'discount_value' => 0,
            'discount_type' => 'fixed',
            'qty' => 10,
            'url' => 'esponja-suave',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $this->get(route('products.index', ['q' => 'detergnte']))
            ->assertOk()
            ->assertSee('Detergente liquido')
            ->assertDontSee('Esponja suave');

        $this->get(route('products.index', ['q' => '9999']))
            ->assertOk()
            ->assertSee('Detergente liquido')
            ->assertDontSee('Esponja suave');

        $this->get(route('products.index', ['q' => 'limpieza']))
            ->assertOk()
            ->assertSee('Detergente liquido')
            ->assertSee('Categorias relacionadas')
            ->assertSee('href="'.route('products.index', ['category' => 'limpieza']).'"', false)
            ->assertSee('aria-label="Ver categoria Limpieza"', false);
    }

    public function test_exact_barcode_search_redirects_to_the_product_page(): void
    {
        $category = Category::create([
            'name' => 'Bebidas',
            'description' => 'Categoria de bebidas',
            'url' => 'bebidas',
            'is_active' => true,
        ]);

        $product = Product::create([
            'barcode' => '8412345678901',
            'name' => 'Botella de cristal 500ml',
            'description' => 'Botella reutilizable',
            'tax' => 21,
            'cost_price' => 3.0000,
            'sale_price' => 6.90,
            'margin_multiplier' => 2.0000,
            'discount_value' => 0,
            'discount_type' => 'fixed',
            'qty' => 10,
            'url' => 'botella-cristal-500ml',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $this->get(route('products.index', ['q' => '8412345678901']))
            ->assertRedirect(route('products.show', $product, absolute: false));
    }
}
