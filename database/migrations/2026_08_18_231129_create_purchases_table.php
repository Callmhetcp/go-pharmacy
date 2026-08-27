<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('supplier_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('reference')->unique();

            $table->date('purchase_date');

            $table->decimal('subtotal', 12, 2)->default(0);

            $table->decimal('discount', 12, 2)->default(0);

            $table->decimal('total_amount', 12, 2)->default(0);

            $table->enum('status', [
                'draft',
                'ordered',
                'received',
                'cancelled',
            ])->default('draft');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('supplier_id');
            $table->index('user_id');
            $table->index('purchase_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};