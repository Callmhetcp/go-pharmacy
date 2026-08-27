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
        Schema::table('reviews', function (Blueprint $table) {
            /*
             * The original foreign key used ON DELETE SET NULL,
             * which is incompatible with a non-nullable user_id.
             *
             * Remove the existing foreign key first.
             */
            $table->dropForeign(['user_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            /*
             * Every review must belong to a customer.
             */
            $table->foreignId('user_id')
                ->nullable(false)
                ->change();

            /*
             * Delete a customer's reviews if the customer
             * account is deleted.
             */
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            /*
             * New reviews require admin approval before
             * becoming publicly visible.
             */
            $table->boolean('is_approved')
                ->default(false)
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            /*
             * Remove the cascade foreign key.
             */
            $table->dropForeign(['user_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            /*
             * Restore the original nullable user_id behavior.
             */
            $table->foreignId('user_id')
                ->nullable()
                ->change();

            /*
             * Restore the original ON DELETE SET NULL behavior.
             */
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            /*
             * Restore the original approval default.
             */
            $table->boolean('is_approved')
                ->default(true)
                ->change();
        });
    }
};