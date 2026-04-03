<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_product_management_panel(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.index'));

        $response
            ->assertOk()
            ->assertSee('Acciones rapidas');
    }

    public function test_admin_can_view_product_creation_page(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.products.create'));

        $response
            ->assertOk()
            ->assertSee('Crear producto');
    }

    public function test_admin_can_create_a_product_from_cost_margin_and_tax(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $category = Category::create([
            'name' => 'Hogar',
            'description' => 'Categoria de prueba',
            'url' => 'hogar',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.products.store'), [
                'barcode' => '1234567890123',
                'name' => 'Lampara de mesa',
                'description' => 'Modelo articulado',
                'tax' => 21,
                'cost_price' => 10.0000,
                'margin_multiplier' => 2,
                'sale_price' => 1,
                'pricing_mode' => 'margin',
                'discount_value' => 0,
                'discount_type' => 'fixed',
                'qty' => 5,
                'image' => UploadedFile::fake()->image('lampara.png', 1200, 1200),
                'url' => 'lampara-mesa',
                'category_id' => $category->id,
                'is_active' => '1',
            ]);

        $response
            ->assertRedirect(route('admin.index'))
            ->assertSessionHasNoErrors();

        $product = Product::firstOrFail();

        $this->assertSame('Lampara de mesa', $product->name);
        $this->assertSame('10.0000', $product->cost_price);
        $this->assertSame('2.0000', $product->margin_multiplier);
        $this->assertSame('24.20', $product->sale_price);
        $this->assertSame(21, $product->tax);
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_admin_can_create_a_product_using_sale_price_as_source(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.products.store'), [
                'barcode' => '9999999999999',
                'name' => 'Teclado',
                'tax' => 21,
                'cost_price' => 10.0000,
                'margin_multiplier' => 2,
                'sale_price' => 30.25,
                'pricing_mode' => 'sale_price',
                'discount_value' => 0,
                'discount_type' => 'fixed',
                'qty' => 2,
                'is_active' => '1',
            ]);

        $response
            ->assertRedirect(route('admin.index'))
            ->assertSessionHasNoErrors();

        $product = Product::firstOrFail();

        $this->assertSame('30.25', $product->sale_price);
        $this->assertSame('2.5000', $product->margin_multiplier);
    }

    public function test_admin_cannot_upload_an_unsupported_image_format(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.index'))
            ->post(route('admin.products.store'), [
                'barcode' => '8888888888888',
                'name' => 'Producto con imagen no valida',
                'tax' => 21,
                'cost_price' => 10.0000,
                'margin_multiplier' => 2,
                'sale_price' => 24.20,
                'pricing_mode' => 'margin',
                'discount_value' => 0,
                'discount_type' => 'fixed',
                'qty' => 1,
                'image' => UploadedFile::fake()->create('producto.svg', 40, 'image/svg+xml'),
                'is_active' => '1',
            ]);

        $response
            ->assertRedirect(route('admin.index'))
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('products', 0);
    }
}
