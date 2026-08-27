<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('selling_unit')
                ->default('piece')
                ->after('quantity');

            $table->string('base_unit')
                ->default('piece')
                ->after('selling_unit');

            $table->unsignedInteger('base_quantity')
                ->default(1)
                ->after('base_unit');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'selling_unit',
                'base_unit',
                'base_quantity',
            ]);
        });
    }
};