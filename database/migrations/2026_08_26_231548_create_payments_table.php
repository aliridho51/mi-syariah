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
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->decimal('amount', 12, 2);
        $table->decimal('infaq_amount', 12, 2)->default(0);
        $table->enum('status', ['pending', 'waiting_confirmation', 'paid', 'failed'])->default('pending');
        $table->string('payment_method')->nullable();
        $table->string('transaction_code')->unique();
        $table->timestamp('paid_at')->nullable();
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};