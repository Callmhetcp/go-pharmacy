<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->unique()
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unsignedInteger('quantity')->default(0);

            $table->unsignedInteger('reserved_quantity')->default(0);

            $table->unsignedInteger('minimum_stock')->default(0);

            $table->timestamps();

            $table->index('quantity');
            $table->index('minimum_stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};