<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Address Information
            |--------------------------------------------------------------------------
            */

            $table->string('label')
                ->nullable();

            $table->string('recipient_name');

            $table->string('phone', 30);

            $table->text('address');

            $table->string('city');

            $table->string('state');

            $table->string('country')
                ->default('Nigeria');

            $table->string('postal_code')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Delivery Instructions
            |--------------------------------------------------------------------------
            */

            $table->text('delivery_notes')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Default Address
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_default')
                ->default(false);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('user_id');
            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};