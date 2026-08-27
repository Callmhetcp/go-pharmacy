<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('inventory_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->enum('type', [
                'purchase',
                'sale',
                'online_sale',
                'return',
                'adjustment',
                'damaged',
                'expired',
                'correction',
            ]);

            $table->integer('quantity');

            $table->unsignedInteger('quantity_before');

            $table->unsignedInteger('quantity_after');

            $table->string('reference')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index(['inventory_id', 'created_at']);
            $table->index('type');
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};