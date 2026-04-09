<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seedNow = now();
        $validCategoryUrls = ['hogar', 'oficina'];
        $validProductBarcodes = [
            '000000000001',
            '000000000002',
            '000000000003',
            '000000000004',
            '000000000005',
            '000000000006',
            '000000000007',
            '000000000008',
            '000000000009',
            '000000000010',
            '000000000011',
        ];

        $testUser = User::updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'phone' => '+34600000001',
            'email_verified_at' => now(),
            'password' => '1234',
            'role' => UserRole::USER->value,
        ]);

        User::updateOrCreate([
            'email' => 'admin@bazardigital.com',
        ], [
            'name' => 'Admin Bazar Digital',
            'phone' => '+34600000002',
            'email_verified_at' => now(),
            'password' => 'admin1234',
            'role' => UserRole::ADMIN->value,
        ]);

        $cartUser = User::updateOrCreate([
            'email' => 'maria@bazardigital.com',
        ], [
            'name' => 'Maria Cliente',
            'phone' => '+34600000003',
            'email_verified_at' => now(),
            'password' => '1234',
            'role' => UserRole::USER->value,
        ]);

        $carlosUser = User::updateOrCreate([
            'email' => 'carlos@bazardigital.com',
        ], [
            'name' => 'Carlos Cliente',
            'phone' => '+34600000004',
            'email_verified_at' => now(),
            'password' => '1234',
            'role' => UserRole::USER->value,
            'cart_created_at' => now(),
        ]);

        $yaizaUser = User::updateOrCreate([
            'email' => 'yaiza@bazardigital.com',
        ], [
            'name' => 'Yaiza',
            'phone' => '+34600000005',
            'email_verified_at' => now(),
            'password' => '1234',
            'role' => UserRole::USER->value,
        ]);

        Product::query()
            ->whereNotIn('barcode', $validProductBarcodes)
            ->delete();

        Category::query()
            ->whereNotIn('url', $validCategoryUrls)
            ->delete();

        $homeCategory = Category::updateOrCreate(
            ['url' => 'hogar'],
            [
                'name' => 'Hogar',
                'description' => 'Productos generales para el hogar y de uso diario.',
                'url' => 'hogar',
                'is_active' => true,
            ]
        );

        $officeCategory = Category::updateOrCreate(
            ['url' => 'oficina'],
            [
                'name' => 'Oficina',
                'description' => 'Productos de oficina, colegio y papeleria.',
                'url' => 'oficina',
                'is_active' => true,
            ]
        );

        $glassBottle = Product::updateOrCreate(
            ['barcode' => '000000000001'],
            [
                'name' => 'Botella de cristal 500ml',
                'description' => 'Botella reutilizable de cristal para uso diario.',
                'tax' => 21,
                'cost_price' => 1.8425,
                'sale_price' => 4.99,
                'margin_multiplier' => 2.70,
                'discount_value' => 0,
                'discount_type' => 'fixed',
                'qty' => 25,
                'image' => null,
                'url' => null,
                'category_id' => $homeCategory->id,
                'is_active' => true,
            ]
        );

        $organizerBox = Product::updateOrCreate(
            ['barcode' => '000000000002'],
            [
                'name' => 'Caja organizadora mediana',
                'description' => 'Caja organizadora de plastico con tapa.',
                'tax' => 21,
                'cost_price' => 3.1500,
                'sale_price' => 7.99,
                'margin_multiplier' => 2.54,
                'discount_value' => 1.00,
                'discount_type' => 'fixed',
                'qty' => 12,
                'image' => null,
                'url' => null,
                'category_id' => $homeCategory->id,
                'is_active' => true,
            ]
        );

        $notebook = Product::updateOrCreate(
            ['barcode' => '000000000003'],
            [
                'name' => 'Cuaderno A5',
                'description' => 'Cuaderno tamano A5 con 80 hojas rayadas.',
                'tax' => 21,
                'cost_price' => 0.9200,
                'sale_price' => 2.50,
                'margin_multiplier' => 2.72,
                'discount_value' => 10,
                'discount_type' => 'percentage',
                'qty' => 40,
                'image' => null,
                'url' => null,
                'category_id' => $officeCategory->id,
                'is_active' => true,
            ]
        );

        $bluePens = Product::updateOrCreate(
            ['barcode' => '000000000004'],
            [
                'name' => 'Pack de boligrafos azules',
                'description' => 'Pack de 10 boligrafos de tinta azul.',
                'tax' => 21,
                'cost_price' => 1.4300,
                'sale_price' => 3.99,
                'margin_multiplier' => 2.79,
                'discount_value' => 0,
                'discount_type' => 'fixed',
                'qty' => 30,
                'image' => null,
                'url' => null,
                'category_id' => $officeCategory->id,
                'is_active' => true,
            ]
        );

        $ringBinder = Product::updateOrCreate(
            ['barcode' => '000000000005'],
            [
                'name' => 'Archivador de anillas',
                'description' => 'Archivador de carton forrado para documentos y apuntes.',
                'tax' => 21,
                'cost_price' => 1.7800,
                'sale_price' => 4.25,
                'margin_multiplier' => 2.39,
                'discount_value' => 0,
                'discount_type' => 'fixed',
                'qty' => 0,
                'image' => null,
                'url' => null,
                'category_id' => $officeCategory->id,
                'is_active' => true,
            ]
        );

        $thermometer = Product::updateOrCreate(
            ['barcode' => '000000000006'],
            [
                'name' => 'Termometro de mercurio',
                'description' => 'Termometro clasico de vidrio para botiquin domestico.',
                'tax' => 21,
                'cost_price' => 1.1500,
                'sale_price' => 3.25,
                'margin_multiplier' => 2.33,
                'discount_value' => 0,
                'discount_type' => 'fixed',
                'qty' => 6,
                'image' => null,
                'url' => null,
                'category_id' => $homeCategory->id,
                'is_active' => false,
            ]
        );

        $storageJar = Product::updateOrCreate(
            ['barcode' => '000000000007'],
            [
                'name' => 'Tarro hermetico de cocina',
                'description' => 'Tarro de cristal con cierre metalico para guardar alimentos secos.',
                'tax' => 21,
                'cost_price' => 2.2500,
                'sale_price' => 5.95,
                'margin_multiplier' => 2.18,
                'discount_value' => 0,
                'discount_type' => 'fixed',
                'qty' => 18,
                'image' => null,
                'url' => null,
                'category_id' => $homeCategory->id,
                'is_active' => true,
            ]
        );

        $deskLamp = Product::updateOrCreate(
            ['barcode' => '000000000008'],
            [
                'name' => 'Lampara LED de escritorio',
                'description' => 'Lampara articulada con luz LED fria para estudio o trabajo.',
                'tax' => 21,
                'cost_price' => 6.8000,
                'sale_price' => 16.90,
                'margin_multiplier' => 2.05,
                'discount_value' => 2.00,
                'discount_type' => 'fixed',
                'qty' => 14,
                'image' => null,
                'url' => null,
                'category_id' => $officeCategory->id,
                'is_active' => true,
            ]
        );

        $stickyNotes = Product::updateOrCreate(
            ['barcode' => '000000000009'],
            [
                'name' => 'Bloc de notas adhesivas',
                'description' => 'Pack con varias notas adhesivas en colores pastel.',
                'tax' => 21,
                'cost_price' => 0.7800,
                'sale_price' => 2.20,
                'margin_multiplier' => 2.32,
                'discount_value' => 0,
                'discount_type' => 'fixed',
                'qty' => 35,
                'image' => null,
                'url' => null,
                'category_id' => $officeCategory->id,
                'is_active' => true,
            ]
        );

        $microfiberCloth = Product::updateOrCreate(
            ['barcode' => '000000000010'],
            [
                'name' => 'Pack de panos microfibra',
                'description' => 'Pack de 4 panos reutilizables para limpieza delicada.',
                'tax' => 21,
                'cost_price' => 1.6400,
                'sale_price' => 4.75,
                'margin_multiplier' => 2.39,
                'discount_value' => 0,
                'discount_type' => 'fixed',
                'qty' => 22,
                'image' => null,
                'url' => null,
                'category_id' => $homeCategory->id,
                'is_active' => true,
            ]
        );

        $scissors = Product::updateOrCreate(
            ['barcode' => '000000000011'],
            [
                'name' => 'Tijeras escolares',
                'description' => 'Tijeras ligeras con punta redondeada para uso diario.',
                'tax' => 21,
                'cost_price' => 0.9500,
                'sale_price' => 2.60,
                'margin_multiplier' => 2.26,
                'discount_value' => 0,
                'discount_type' => 'fixed',
                'qty' => 28,
                'image' => null,
                'url' => null,
                'category_id' => $officeCategory->id,
                'is_active' => true,
            ]
        );

        $baseProductQuantities = [
            $glassBottle->id => 25,
            $organizerBox->id => 12,
            $notebook->id => 40,
            $bluePens->id => 30,
            $ringBinder->id => 0,
            $thermometer->id => 6,
            $storageJar->id => 18,
            $deskLamp->id => 14,
            $stickyNotes->id => 35,
            $microfiberCloth->id => 22,
            $scissors->id => 28,
        ];

        $seededCartItems = [
            $glassBottle->id => 2,
            $notebook->id => 3,
        ];

        CartItem::query()
            ->where('user_id', $cartUser->id)
            ->whereNotIn('product_id', array_keys($seededCartItems))
            ->delete();

        foreach ($seededCartItems as $productId => $quantity) {
            CartItem::updateOrCreate(
                [
                    'user_id' => $cartUser->id,
                    'product_id' => $productId,
                ],
                [
                    'quantity' => $quantity,
                ]
            );
        }

        $cartUser->forceFill([
            'cart_created_at' => now(),
        ])->save();

        $seededOrders = [
            [
                'order_number' => 'SEED-ORDER-STORE-PENDING-001',
                'user' => $testUser,
                'pickup_name' => 'Test User',
                'status' => OrderStatus::PENDING,
                'payment_method' => PaymentMethod::STORE,
                'payment_status' => PaymentStatus::PENDING,
                'payment_reference' => null,
                'paid_at' => null,
                'notes' => 'Seeder demo: pedido pendiente con pago en tienda.',
                'created_at' => $seedNow->copy()->subDays(48)->setTime(10, 15),
                'updated_at' => $seedNow->copy()->subDays(48)->setTime(10, 15),
                'items' => [
                    ['product' => $glassBottle, 'quantity' => 1],
                    ['product' => $notebook, 'quantity' => 2],
                ],
            ],
            [
                'order_number' => 'SEED-ORDER-ONLINE-PAID-002',
                'user' => $cartUser,
                'pickup_name' => 'Maria Cliente',
                'status' => OrderStatus::COMPLETED,
                'payment_method' => PaymentMethod::ONLINE,
                'payment_status' => PaymentStatus::PAID,
                'payment_reference' => 'seed_stripe_paid_002',
                'paid_at' => $seedNow->copy()->subDays(41)->setTime(12, 10),
                'notes' => 'Seeder demo: pedido online completado y pagado.',
                'created_at' => $seedNow->copy()->subDays(41)->setTime(10, 45),
                'updated_at' => $seedNow->copy()->subDays(40)->setTime(18, 20),
                'items' => [
                    ['product' => $organizerBox, 'quantity' => 1],
                    ['product' => $bluePens, 'quantity' => 2],
                ],
            ],
            [
                'order_number' => 'SEED-ORDER-CANCELLED-003',
                'user' => $carlosUser,
                'pickup_name' => 'Carlos Cliente',
                'status' => OrderStatus::CANCELLED,
                'cancel_reason' => 'No podia recogerlo ese fin de semana.',
                'payment_method' => PaymentMethod::STORE,
                'payment_status' => PaymentStatus::PENDING,
                'payment_reference' => null,
                'paid_at' => null,
                'notes' => 'Seeder demo: pedido cancelado por el cliente antes de recogerlo.',
                'created_at' => $seedNow->copy()->subDays(36)->setTime(17, 20),
                'updated_at' => $seedNow->copy()->subDays(35)->setTime(8, 50),
                'items' => [
                    ['product' => $notebook, 'quantity' => 1],
                    ['product' => $stickyNotes, 'quantity' => 2],
                ],
            ],
            [
                'order_number' => 'SEED-ORDER-STORE-COMPLETED-004',
                'user' => $yaizaUser,
                'pickup_name' => 'Yaiza',
                'status' => OrderStatus::COMPLETED,
                'payment_method' => PaymentMethod::STORE,
                'payment_status' => PaymentStatus::PAID,
                'payment_reference' => null,
                'paid_at' => $seedNow->copy()->subDays(31)->setTime(19, 5),
                'notes' => 'Seeder demo: pedido en tienda ya entregado a Yaiza.',
                'created_at' => $seedNow->copy()->subDays(31)->setTime(13, 30),
                'updated_at' => $seedNow->copy()->subDays(31)->setTime(19, 5),
                'items' => [
                    ['product' => $deskLamp, 'quantity' => 1],
                    ['product' => $stickyNotes, 'quantity' => 2],
                ],
            ],
            [
                'order_number' => 'SEED-ORDER-READY-005',
                'user' => $cartUser,
                'pickup_name' => 'Maria Cliente',
                'status' => OrderStatus::READY,
                'payment_method' => PaymentMethod::ONLINE,
                'payment_status' => PaymentStatus::PAID,
                'payment_reference' => 'seed_stripe_ready_005',
                'paid_at' => $seedNow->copy()->subDays(27)->setTime(10, 5),
                'notes' => 'Seeder demo: pedido listo para recoger con pago online previo.',
                'created_at' => $seedNow->copy()->subDays(27)->setTime(9, 40),
                'updated_at' => $seedNow->copy()->subDays(26)->setTime(18, 45),
                'items' => [
                    ['product' => $microfiberCloth, 'quantity' => 1],
                    ['product' => $glassBottle, 'quantity' => 1],
                ],
            ],
            [
                'order_number' => 'SEED-ORDER-COMPLETED-006',
                'user' => $testUser,
                'pickup_name' => 'Test User',
                'status' => OrderStatus::COMPLETED,
                'payment_method' => PaymentMethod::STORE,
                'payment_status' => PaymentStatus::PAID,
                'payment_reference' => null,
                'paid_at' => $seedNow->copy()->subDays(22)->setTime(18, 15),
                'notes' => 'Seeder demo: pedido en tienda ya retirado.',
                'created_at' => $seedNow->copy()->subDays(22)->setTime(11, 20),
                'updated_at' => $seedNow->copy()->subDays(22)->setTime(18, 15),
                'items' => [
                    ['product' => $organizerBox, 'quantity' => 1],
                    ['product' => $storageJar, 'quantity' => 2],
                ],
            ],
            [
                'order_number' => 'SEED-ORDER-ONLINE-UNPAID-007',
                'user' => $carlosUser,
                'pickup_name' => 'Carlos Cliente',
                'status' => OrderStatus::PENDING,
                'payment_method' => PaymentMethod::ONLINE,
                'payment_status' => PaymentStatus::PENDING,
                'payment_reference' => null,
                'paid_at' => null,
                'notes' => 'Seeder demo: pedido online pendiente de confirmacion de pago.',
                'created_at' => $seedNow->copy()->subDays(18)->setTime(17, 20),
                'updated_at' => $seedNow->copy()->subDays(18)->setTime(17, 20),
                'items' => [
                    ['product' => $notebook, 'quantity' => 1],
                    ['product' => $organizerBox, 'quantity' => 1],
                ],
            ],
            [
                'order_number' => 'SEED-ORDER-ONLINE-COMPLETED-008',
                'user' => $yaizaUser,
                'pickup_name' => 'Yaiza',
                'status' => OrderStatus::COMPLETED,
                'payment_method' => PaymentMethod::ONLINE,
                'payment_status' => PaymentStatus::PAID,
                'payment_reference' => 'seed_stripe_paid_008',
                'paid_at' => $seedNow->copy()->subDays(14)->setTime(9, 55),
                'notes' => 'Seeder demo: pedido online entregado sin incidencias.',
                'created_at' => $seedNow->copy()->subDays(14)->setTime(9, 10),
                'updated_at' => $seedNow->copy()->subDays(13)->setTime(19, 40),
                'items' => [
                    ['product' => $scissors, 'quantity' => 2],
                    ['product' => $bluePens, 'quantity' => 1],
                ],
            ],
            [
                'order_number' => 'SEED-ORDER-READY-009',
                'user' => $cartUser,
                'pickup_name' => 'Maria Cliente',
                'status' => OrderStatus::READY,
                'payment_method' => PaymentMethod::STORE,
                'payment_status' => PaymentStatus::PENDING,
                'payment_reference' => null,
                'paid_at' => null,
                'notes' => 'Seeder demo: pedido listo para recoger y pagar en tienda.',
                'created_at' => $seedNow->copy()->subDays(10)->setTime(12, 5),
                'updated_at' => $seedNow->copy()->subDays(9)->setTime(16, 30),
                'items' => [
                    ['product' => $glassBottle, 'quantity' => 1],
                    ['product' => $deskLamp, 'quantity' => 1],
                ],
            ],
            [
                'order_number' => 'SEED-ORDER-ONLINE-COMPLETED-010',
                'user' => $carlosUser,
                'pickup_name' => 'Carlos Cliente',
                'status' => OrderStatus::COMPLETED,
                'payment_method' => PaymentMethod::ONLINE,
                'payment_status' => PaymentStatus::PAID,
                'payment_reference' => 'seed_stripe_paid_010',
                'paid_at' => $seedNow->copy()->subDays(7)->setTime(8, 35),
                'notes' => 'Seeder demo: pedido online completado esta semana.',
                'created_at' => $seedNow->copy()->subDays(7)->setTime(8, 10),
                'updated_at' => $seedNow->copy()->subDays(6)->setTime(19, 0),
                'items' => [
                    ['product' => $stickyNotes, 'quantity' => 2],
                    ['product' => $scissors, 'quantity' => 1],
                ],
            ],
            [
                'order_number' => 'SEED-ORDER-STORE-PENDING-011',
                'user' => $yaizaUser,
                'pickup_name' => 'Yaiza',
                'status' => OrderStatus::PENDING,
                'payment_method' => PaymentMethod::STORE,
                'payment_status' => PaymentStatus::PENDING,
                'payment_reference' => null,
                'paid_at' => null,
                'notes' => 'Seeder demo: pedido reciente pendiente de preparar.',
                'created_at' => $seedNow->copy()->subDays(5)->setTime(14, 25),
                'updated_at' => $seedNow->copy()->subDays(5)->setTime(14, 25),
                'items' => [
                    ['product' => $storageJar, 'quantity' => 1],
                    ['product' => $microfiberCloth, 'quantity' => 1],
                ],
            ],
            [
                'order_number' => 'SEED-ORDER-STORE-COMPLETED-012',
                'user' => $cartUser,
                'pickup_name' => 'Maria Cliente',
                'status' => OrderStatus::COMPLETED,
                'payment_method' => PaymentMethod::STORE,
                'payment_status' => PaymentStatus::PAID,
                'payment_reference' => null,
                'paid_at' => $seedNow->copy()->subDays(3)->setTime(18, 5),
                'notes' => 'Seeder demo: pedido entregado hace pocos dias.',
                'created_at' => $seedNow->copy()->subDays(3)->setTime(10, 50),
                'updated_at' => $seedNow->copy()->subDays(3)->setTime(18, 5),
                'items' => [
                    ['product' => $bluePens, 'quantity' => 2],
                    ['product' => $notebook, 'quantity' => 1],
                ],
            ],
            [
                'order_number' => 'SEED-ORDER-ONLINE-PAID-013',
                'user' => $testUser,
                'pickup_name' => 'Test User',
                'status' => OrderStatus::PENDING,
                'payment_method' => PaymentMethod::ONLINE,
                'payment_status' => PaymentStatus::PAID,
                'payment_reference' => 'seed_stripe_paid_013',
                'paid_at' => $seedNow->copy()->subDays(2)->setTime(12, 15),
                'notes' => 'Seeder demo: pedido pendiente pero con pago online confirmado.',
                'created_at' => $seedNow->copy()->subDays(2)->setTime(11, 45),
                'updated_at' => $seedNow->copy()->subDays(2)->setTime(12, 15),
                'items' => [
                    ['product' => $deskLamp, 'quantity' => 1],
                    ['product' => $organizerBox, 'quantity' => 1],
                ],
            ],
            [
                'order_number' => 'SEED-ORDER-ONLINE-UNPAID-014',
                'user' => $carlosUser,
                'pickup_name' => 'Carlos Cliente',
                'status' => OrderStatus::PENDING,
                'payment_method' => PaymentMethod::ONLINE,
                'payment_status' => PaymentStatus::PENDING,
                'payment_reference' => null,
                'paid_at' => null,
                'notes' => 'Seeder demo: segundo pedido online sin pagar para revisar flujo.',
                'created_at' => $seedNow->copy()->subDay()->setTime(16, 10),
                'updated_at' => $seedNow->copy()->subDay()->setTime(16, 10),
                'items' => [
                    ['product' => $stickyNotes, 'quantity' => 1],
                    ['product' => $storageJar, 'quantity' => 1],
                    ['product' => $glassBottle, 'quantity' => 1],
                ],
            ],
            [
                'order_number' => 'SEED-ORDER-TODAY-PENDING-015',
                'user' => $yaizaUser,
                'pickup_name' => 'Yaiza',
                'status' => OrderStatus::PENDING,
                'payment_method' => PaymentMethod::STORE,
                'payment_status' => PaymentStatus::PENDING,
                'payment_reference' => null,
                'paid_at' => null,
                'notes' => 'Seeder demo: pedido del dia para pruebas de administracion.',
                'created_at' => $seedNow->copy()->setTime(10, 20),
                'updated_at' => $seedNow->copy()->setTime(10, 20),
                'items' => [
                    ['product' => $microfiberCloth, 'quantity' => 2],
                    ['product' => $scissors, 'quantity' => 1],
                ],
            ],
        ];

        foreach ($seededOrders as $seededOrder) {
            $this->upsertSeedOrder($seededOrder);
        }

        $this->syncSeededProductStock($baseProductQuantities, $seededOrders);
    }

    protected function upsertSeedOrder(array $seededOrder): void
    {
        $summary = $this->buildOrderSummary($seededOrder['items']);

        $order = Order::query()->firstOrNew([
            'order_number' => $seededOrder['order_number'],
        ]);

        $order->forceFill([
            'order_number' => $seededOrder['order_number'],
            'user_id' => $seededOrder['user']->id,
            'source' => 'web',
            'pickup_name' => $seededOrder['pickup_name'],
            'status' => $seededOrder['status']->value,
            'cancel_reason' => $seededOrder['cancel_reason'] ?? null,
            'payment_method' => $seededOrder['payment_method']->value,
            'payment_status' => $seededOrder['payment_status']->value,
            'paid_at' => $seededOrder['paid_at'],
            'payment_reference' => $seededOrder['payment_reference'],
            'notes' => $seededOrder['notes'],
            'subtotal' => number_format($summary['subtotal'], 2, '.', ''),
            'discount_total' => number_format($summary['discount_total'], 2, '.', ''),
            'tax_total' => number_format($summary['tax_total'], 2, '.', ''),
            'total' => number_format($summary['total'], 2, '.', ''),
            'created_at' => $seededOrder['created_at'],
            'updated_at' => $seededOrder['updated_at'],
        ])->save();

        $order->items()->delete();
        $order->items()->createMany($summary['items']);
    }

    protected function buildOrderSummary(array $items): array
    {
        $subtotal = 0.0;
        $discountTotal = 0.0;
        $taxTotal = 0.0;
        $total = 0.0;
        $payload = [];

        foreach ($items as $item) {
            $product = $item['product'];
            $quantity = (int) $item['quantity'];
            $unitPrice = (float) $product->sale_price;
            $unitFinalPrice = (float) $product->discounted_price;
            $lineSubtotal = round($unitPrice * $quantity, 2);
            $lineTotal = round($unitFinalPrice * $quantity, 2);
            $lineTax = $this->calculateTaxAmount($lineTotal, (int) $product->tax);

            $subtotal += $lineSubtotal;
            $discountTotal += max($lineSubtotal - $lineTotal, 0);
            $taxTotal += $lineTax;
            $total += $lineTotal;

            $payload[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'tax' => $product->tax,
                'unit_price' => number_format($unitPrice, 2, '.', ''),
                'discount_type' => $product->has_discount ? $product->discount_type : null,
                'discount_value' => number_format((float) $product->discount_value, 2, '.', ''),
                'unit_final_price' => number_format($unitFinalPrice, 2, '.', ''),
                'line_total' => number_format($lineTotal, 2, '.', ''),
            ];
        }

        return [
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'total' => $total,
            'items' => $payload,
        ];
    }

    protected function syncSeededProductStock(array $baseProductQuantities, array $seededOrders): void
    {
        $reservedQuantities = [];

        foreach ($seededOrders as $seededOrder) {
            if ($seededOrder['status'] === OrderStatus::CANCELLED) {
                continue;
            }

            foreach ($seededOrder['items'] as $item) {
                $productId = $item['product']->id;

                $reservedQuantities[$productId] = ($reservedQuantities[$productId] ?? 0) + (int) $item['quantity'];
            }
        }

        foreach ($baseProductQuantities as $productId => $baseQuantity) {
            Product::query()
                ->whereKey($productId)
                ->update([
                    'qty' => max($baseQuantity - ($reservedQuantities[$productId] ?? 0), 0),
                ]);
        }
    }

    protected function calculateTaxAmount(float $lineTotal, int $taxRate): float
    {
        if ($taxRate <= 0) {
            return 0.0;
        }

        return round($lineTotal - ($lineTotal / (1 + ($taxRate / 100))), 2);
    }
}
