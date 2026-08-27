<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unsignedInteger('quantity');

            $table->decimal('unit_cost', 12, 2);

            $table->decimal('total_cost', 12, 2);

            $table->string('batch_number')->nullable();

            $table->date('expiry_date')->nullable();

            $table->timestamps();

            $table->index('purchase_id');
            $table->index('product_id');
            $table->index('batch_number');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};