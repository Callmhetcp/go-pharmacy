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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Prescription Reference
            |--------------------------------------------------------------------------
            */

            $table->string('reference_number')->unique();

            /*
            |--------------------------------------------------------------------------
            | Prescription Information
            |--------------------------------------------------------------------------
            */

            $table->string('doctor_name')->nullable();

            $table->string('hospital_name')->nullable();

            $table->date('prescription_date')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Uploaded File
            |--------------------------------------------------------------------------
            */

            $table->string('file_path');

            $table->string('file_type')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Customer Notes
            |--------------------------------------------------------------------------
            */

            $table->text('notes')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Review
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'rejected',
                'fulfilled',
            ])->default('pending');

            $table->text('rejection_reason')->nullable();

            $table->text('review_notes')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Reviewer
            |--------------------------------------------------------------------------
            */

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('status');

            $table->index('prescription_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};