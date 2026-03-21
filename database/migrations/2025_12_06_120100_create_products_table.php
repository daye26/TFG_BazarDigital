<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('tax')->default(21);
            $table->decimal('cost_price', 10, 4);
            $table->decimal('sale_price', 10, 2);
            $table->decimal('margin_multiplier', 5, 2)->default(2.00);
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->enum('discount_type', ['fixed', 'percentage'])->default('fixed');
            $table->unsignedInteger('qty')->default(0);
            $table->string('image')->nullable();
            $table->string('url')->nullable()->unique();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
