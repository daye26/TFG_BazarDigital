<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\StripeCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Stripe\Checkout\Session;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_a_store_order_from_the_cart(): void
    {
        $user = User::factory()->create([
            'cart_created_at' => now(),
        ]);
        $product = $this->createProduct([
            'qty' => 8,
            'sale_price' => 4.50,
            'discount_value' => 0,
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('orders.store'), [
                'pickup_name' => 'Maria Cliente',
                'payment_method' => 'store',
            ]);

        $order = Order::firstOrFail();

        $response->assertRedirect(route('orders.show', $order, absolute: false));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $user->id,
            'pickup_name' => 'Maria Cliente',
            'status' => 'pending',
            'payment_method' => 'store',
            'payment_status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
        ]);

        $this->assertSame(6, $product->fresh()->qty);
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertNull($user->fresh()->cart_created_at);
        $this->assertStringStartsWith('WEB-', $order->order_number);
    }

    public function test_customer_can_start_online_checkout_from_the_cart(): void
    {
        $user = User::factory()->create([
            'cart_created_at' => now(),
        ]);
        $product = $this->createProduct([
            'qty' => 5,
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->mock(StripeCheckoutService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('createSession')
                ->once()
                ->andReturn(Session::constructFrom([
                    'id' => 'cs_test_123',
                    'url' => 'https://checkout.stripe.test/session/cs_test_123',
                ]));
        });

        $response = $this
            ->actingAs($user)
            ->post(route('orders.store'), [
                'pickup_name' => 'Pedido Online',
                'payment_method' => 'online',
            ]);

        $order = Order::firstOrFail();

        $response->assertRedirect('https://checkout.stripe.test/session/cs_test_123');
        $this->assertSame('online', $order->payment_method->value);
        $this->assertSame('pending', $order->payment_status->value);
        $this->assertSame(4, $product->fresh()->qty);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_customer_can_cancel_a_pending_order_and_restore_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct([
            'qty' => 7,
        ]);

        $order = Order::create([
            'order_number' => 'WEB-20260407-000001',
            'user_id' => $user->id,
            'source' => 'web',
            'pickup_name' => 'Cliente Cancelacion',
            'status' => OrderStatus::PENDING,
            'payment_method' => PaymentMethod::STORE,
            'payment_status' => PaymentStatus::PENDING,
            'subtotal' => '12.10',
            'discount_total' => '0.00',
            'tax_total' => '2.10',
            'total' => '12.10',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 3,
            'tax' => 21,
            'unit_price' => '4.03',
            'discount_type' => null,
            'discount_value' => '0.00',
            'unit_final_price' => '4.03',
            'line_total' => '12.10',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('orders.cancel', $order), [
                'cancel_reason' => 'Ya no lo necesito',
            ]);

        $response->assertRedirect(route('orders.show', $order, absolute: false));

        $this->assertSame('cancelled', $order->fresh()->status->value);
        $this->assertSame('Ya no lo necesito', $order->fresh()->cancel_reason);
        $this->assertSame(10, $product->fresh()->qty);
    }

    public function test_admin_can_mark_a_store_order_as_ready_and_completed(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);
        $customer = User::factory()->create();

        $order = Order::create([
            'order_number' => 'WEB-20260407-000002',
            'user_id' => $customer->id,
            'source' => 'web',
            'pickup_name' => 'Cliente Tienda',
            'status' => OrderStatus::PENDING,
            'payment_method' => PaymentMethod::STORE,
            'payment_status' => PaymentStatus::PENDING,
            'subtotal' => '9.99',
            'discount_total' => '0.00',
            'tax_total' => '1.73',
            'total' => '9.99',
        ]);

        $this
            ->actingAs($admin)
            ->patch(route('admin.orders.ready', $order))
            ->assertRedirect(route('admin.orders.index', absolute: false));

        $this->assertSame('ready', $order->fresh()->status->value);

        $this
            ->actingAs($admin)
            ->patch(route('admin.orders.complete', $order))
            ->assertRedirect(route('admin.orders.index', absolute: false));

        $freshOrder = $order->fresh();

        $this->assertSame('completed', $freshOrder->status->value);
        $this->assertSame('paid', $freshOrder->payment_status->value);
        $this->assertNotNull($freshOrder->paid_at);
    }

    public function test_admin_cannot_prepare_an_unpaid_online_order(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);
        $customer = User::factory()->create();

        $order = Order::create([
            'order_number' => 'WEB-20260407-000003',
            'user_id' => $customer->id,
            'source' => 'web',
            'pickup_name' => 'Cliente Online',
            'status' => OrderStatus::PENDING,
            'payment_method' => PaymentMethod::ONLINE,
            'payment_status' => PaymentStatus::PENDING,
            'subtotal' => '9.99',
            'discount_total' => '0.00',
            'tax_total' => '1.73',
            'total' => '9.99',
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.orders.index'))
            ->patch(route('admin.orders.ready', $order));

        $response
            ->assertRedirect(route('admin.orders.index', absolute: false))
            ->assertSessionHasErrors('order');

        $this->assertSame('pending', $order->fresh()->status->value);
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
            'cost_price' => 3.7190,
            'sale_price' => 9.99,
            'margin_multiplier' => 2.2000,
            'discount_value' => 0,
            'discount_type' => 'fixed',
            'qty' => 10,
            'url' => fake()->unique()->slug(2),
            'category_id' => $category->id,
            'is_active' => true,
        ], $attributes));
    }
}
