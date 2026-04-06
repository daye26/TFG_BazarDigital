<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_users_are_redirected_when_visiting_the_cart(): void
    {
        $response = $this->get(route('cart.show'));

        $response->assertRedirect(route('login', absolute: false));
    }

    public function test_authenticated_user_can_add_a_product_to_the_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct([
            'name' => 'Manta termica',
            'qty' => 8,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('products.show', $product))
            ->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

        $response
            ->assertRedirect(route('products.show', $product, absolute: false))
            ->assertSessionHas('cart_status');

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertNotNull($user->fresh()->cart_created_at);
    }

    public function test_authenticated_user_can_add_a_product_to_the_cart_via_json(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct([
            'name' => 'Caja organizadora mediana',
            'qty' => 8,
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson(route('cart.items.store'), [
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Caja organizadora mediana se ha anadido al carrito.',
                'cartItemsCount' => 2,
            ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_adding_the_same_product_updates_the_existing_cart_line(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct([
            'qty' => 5,
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this
            ->actingAs($user)
            ->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'quantity' => 2,
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 4,
        ]);
    }

    public function test_user_cannot_add_more_units_than_the_available_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct([
            'name' => 'Taza esmaltada',
            'qty' => 2,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('products.show', $product))
            ->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'quantity' => 3,
            ]);

        $response
            ->assertRedirect(route('products.show', $product, absolute: false))
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_user_can_update_and_remove_cart_items(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct([
            'qty' => 6,
        ]);

        $user->forceFill([
            'cart_created_at' => now(),
        ])->save();

        $item = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this
            ->actingAs($user)
            ->patch(route('cart.items.update', $item), [
                'quantity' => 4,
            ])
            ->assertRedirect(route('cart.show', absolute: false));

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 4,
        ]);

        $this
            ->actingAs($user)
            ->delete(route('cart.items.destroy', $item))
            ->assertRedirect(route('cart.show', absolute: false));

        $this->assertDatabaseMissing('cart_items', [
            'id' => $item->id,
        ]);

        $this->assertNull($user->fresh()->cart_created_at);
    }

    public function test_user_can_update_cart_items_via_json(): void
    {
        $user = User::factory()->create();
        $firstProduct = $this->createProduct([
            'sale_price' => 1.50,
            'qty' => 8,
        ]);
        $secondProduct = $this->createProduct([
            'name' => 'Caja mediana',
            'barcode' => fake()->unique()->numerify('#############'),
            'sale_price' => 5.99,
            'qty' => 8,
            'url' => fake()->unique()->slug(2),
        ]);

        $firstItem = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $firstProduct->id,
            'quantity' => 1,
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $secondProduct->id,
            'quantity' => 2,
        ]);

        $response = $this
            ->actingAs($user)
            ->patchJson(route('cart.items.update', $firstItem), [
                'quantity' => 4,
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'La cantidad del producto se ha actualizado.',
                'cartItemsCount' => 6,
                'cartLineCount' => 2,
                'cartSubtotal' => '17,98',
            ]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $firstItem->id,
            'quantity' => 4,
        ]);
    }

    public function test_user_can_clear_the_cart_and_reset_the_cart_creation_date(): void
    {
        $user = User::factory()->create([
            'cart_created_at' => now(),
        ]);

        $product = $this->createProduct();

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this
            ->actingAs($user)
            ->delete(route('cart.clear'))
            ->assertRedirect(route('cart.show', absolute: false));

        $this->assertDatabaseCount('cart_items', 0);
        $this->assertNull($user->fresh()->cart_created_at);
    }

    protected function createProduct(array $attributes = []): Product
    {
        $category = Category::create([
            'name' => 'Hogar',
            'description' => 'Categoria para tests',
            'url' => fake()->unique()->slug(2),
            'is_active' => true,
        ]);

        return Product::create(array_merge([
            'barcode' => fake()->unique()->numerify('#############'),
            'name' => 'Producto test',
            'description' => 'Descripcion test',
            'tax' => 21,
            'cost_price' => 10.0000,
            'sale_price' => 24.20,
            'margin_multiplier' => 2.0000,
            'discount_value' => 0,
            'discount_type' => 'fixed',
            'qty' => 10,
            'url' => fake()->unique()->slug(2),
            'category_id' => $category->id,
            'is_active' => true,
        ], $attributes));
    }
}
