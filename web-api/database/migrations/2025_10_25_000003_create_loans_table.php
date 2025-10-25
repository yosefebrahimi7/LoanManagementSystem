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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->bigInteger('amount'); // requested loan amount in smallest currency unit
            $table->tinyInteger('term_months'); // loan term in months
            $table->decimal('interest_rate', 5, 2); // annual interest rate
            $table->bigInteger('monthly_payment'); // calculated monthly payment
            $table->bigInteger('remaining_balance'); // remaining balance to pay
            $table->string('status', 20)->default('pending'); // pending, approved, rejected, active, delinquent, paid
            $table->date('start_date')->nullable();
            $table->date('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
