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
        Schema::create('fiat_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('fiat_deposit_instruction_id')->constrained('fiat_deposit_instructions')->onDelete('cascade');
            $table->uuid('paxos_deposit_id')->nullable()->unique();
            $table->string('amount');
            $table->string('asset')->default('USD');
            $table->string('memo_id')->nullable();
            $table->string('status')->nullable();
            $table->json('fiat_network_instructions')->nullable();
            $table->json('fiat_account_owner')->nullable();
            $table->json('paxos_response')->nullable(); // Store full response from Paxos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiat_deposits');
    }
};
