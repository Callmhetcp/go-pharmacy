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
            ALTER TABLE inventory_transactions
            MODIFY type ENUM(
                'purchase',
                'purchase_return',
                'sale',
                'online_sale',
                'pos_sale',
                'return',
                'adjustment',
                'damaged',
                'expired',
                'expired_disposal',
                'correction'
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE inventory_transactions
            MODIFY type ENUM(
                'purchase',
                'purchase_return',
                'sale',
                'online_sale',
                'pos_sale',
                'return',
                'adjustment',
                'damaged',
                'expired',
                'correction'
            )
        ");
    }
};