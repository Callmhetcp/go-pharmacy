<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE purchase_items
            MODIFY status ENUM(
                'active',
                'expired',
                'returned',
                'partially_returned',
                'disposed'
            )
            NOT NULL
            DEFAULT 'active'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE purchase_items
            MODIFY status ENUM(
                'active',
                'expired',
                'returned',
                'partially_returned'
            )
            NOT NULL
            DEFAULT 'active'
        ");
    }
};