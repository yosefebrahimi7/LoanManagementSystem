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
        Schema::create('loan_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('installment_number'); // 1, 2, 3, ...
            $table->bigInteger('amount_due'); // amount due for this installment
            $table->bigInteger('principal_amount'); // principal portion
            $table->bigInteger('interest_amount'); // interest portion
            $table->bigInteger('penalty_amount')->default(0); // penalty for late payment
            $table->bigInteger('paid_amount')->default(0); // amount actually paid
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->string('status', 20)->default('pending'); // pending, partial, paid, overdue
            $table->timestamps();

            $table->index('loan_id');
            $table->index('status');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_schedules');
    }
};
