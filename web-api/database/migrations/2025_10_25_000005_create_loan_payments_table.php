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
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->foreignId('loan_schedule_id')->nullable()->constrained()->onDelete('set null');
            $table->bigInteger('amount'); // payment amount
            $table->string('payment_method', 20)->default('zarinpal'); // zarinpal, manual, etc.
            $table->string('status', 20)->default('pending'); // pending, completed, failed, refunded
            $table->string('gateway_reference')->nullable(); // gateway transaction reference
            $table->text('gateway_response')->nullable(); // full gateway response
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('loan_id');
            $table->index('status');
            $table->index('gateway_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
    }
};
