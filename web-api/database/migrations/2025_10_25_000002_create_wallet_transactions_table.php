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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->string('type', 20); // credit, debit
            $table->bigInteger('amount'); // in smallest currency unit
            $table->bigInteger('balance_after'); // balance after this transaction
            $table->string('description')->nullable();
            $table->json('meta')->nullable(); // additional metadata
            $table->timestamps();

            $table->index('wallet_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
