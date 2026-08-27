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
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Prescription
            |--------------------------------------------------------------------------
            */

            $table->foreignId('prescription_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            |
            | Nullable because a prescribed medicine may not yet exist
            | in the Go Pharmacy product catalogue.
            |
            */

            $table->foreignId('product_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Medicine Information
            |--------------------------------------------------------------------------
            */

            $table->string('medicine_name');

            $table->string('dosage')->nullable();

            $table->string('frequency')->nullable();

            $table->string('duration')->nullable();

            $table->unsignedInteger('quantity')->nullable();

            $table->text('instructions')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};