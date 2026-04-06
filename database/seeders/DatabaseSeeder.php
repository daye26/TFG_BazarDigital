<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\CartItem;
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
        $validCategoryUrls = ['hogar', 'oficina'];
        $validProductBarcodes = ['000000000001', '000000000002', '000000000003', '000000000004', '000000000005'];

        User::updateOrCreate([
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

        User::updateOrCreate([
            'email' => 'carlos@bazardigital.com',
        ], [
            'name' => 'Carlos Cliente',
            'phone' => '+34600000004',
            'email_verified_at' => now(),
            'password' => '1234',
            'role' => UserRole::USER->value,
            'cart_created_at' => now(),
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

        Product::updateOrCreate(
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

        Product::updateOrCreate(
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

        Product::updateOrCreate(
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
    }
}
