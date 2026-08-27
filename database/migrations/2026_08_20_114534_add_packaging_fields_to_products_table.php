<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('base_unit')->default('piece')->after('strength');

            $table->string('selling_unit')->default('piece')->after('base_unit');

            $table->unsignedInteger('units_per_selling_unit')
                ->default(1)
                ->after('selling_unit');

            $table->boolean('allow_partial_sale')
                ->default(false)
                ->after('units_per_selling_unit');

            $table->string('packaging_description')
                ->nullable()
                ->after('allow_partial_sale');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'base_unit',
                'selling_unit',
                'units_per_selling_unit',
                'allow_partial_sale',
                'packaging_description',
            ]);
        });
    }
};