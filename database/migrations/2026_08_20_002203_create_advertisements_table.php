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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Advertisement Content
            |--------------------------------------------------------------------------
            */

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image');

            /*
            |--------------------------------------------------------------------------
            | Product Target
            |--------------------------------------------------------------------------
            */

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Call To Action
            |--------------------------------------------------------------------------
            */

            $table->string('button_text')
                ->default('Shop Now');

            /*
            |--------------------------------------------------------------------------
            | Scheduling
            |--------------------------------------------------------------------------
            */

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Display Controls
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'is_active',
                'sort_order',
            ]);

            $table->index([
                'starts_at',
                'ends_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};