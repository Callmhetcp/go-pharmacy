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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            /*
            |--------------------------------------------------------------------------
            | Reviewable Model
            |--------------------------------------------------------------------------
            |
            | This allows reviews to belong to a Product now and potentially
            | other models in the future.
            |
            */

            $table->string('reviewable_type');

            $table->unsignedBigInteger('reviewable_id');

            /*
            |--------------------------------------------------------------------------
            | Review
            |--------------------------------------------------------------------------
            */

            $table->unsignedTinyInteger('rating');

            $table->text('comment')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Moderation
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_approved')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'reviewable_type',
                'reviewable_id',
            ]);

            /*
            |--------------------------------------------------------------------------
            | One review per customer per reviewable item
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'user_id',
                'reviewable_type',
                'reviewable_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};