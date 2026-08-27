<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name');
            $table->string('slug')->unique();

            $table->string('sku')->unique()->nullable();
            $table->string('barcode')->unique()->nullable();

            $table->string('brand')->nullable();
            $table->string('generic_name')->nullable();

            $table->text('description')->nullable();

            $table->decimal('price', 12, 2);

            $table->decimal('cost_price', 12, 2)
                ->nullable();

            $table->string('dosage_form')->nullable();
            $table->string('strength')->nullable();

            $table->boolean('requires_prescription')
                ->default(false);

            $table->boolean('is_active')
                ->default(true);

            $table->boolean('is_featured')
                ->default(false);

            $table->string('image')->nullable();

            $table->unsignedInteger('minimum_stock')
                ->default(0);

            $table->timestamps();

            $table->index('category_id');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('requires_prescription');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};