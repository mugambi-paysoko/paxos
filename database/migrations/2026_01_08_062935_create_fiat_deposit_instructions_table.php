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
        Schema::create('fiat_deposit_instructions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('profile_id')->constrained('profiles')->onDelete('cascade');
            $table->foreignId('fiat_account_id')->nullable()->constrained('fiat_accounts')->onDelete('set null');
            $table->uuid('paxos_deposit_instruction_id')->unique();
            $table->string('paxos_profile_id');
            $table->uuid('paxos_identity_id')->nullable();
            $table->uuid('paxos_account_id')->nullable();
            $table->string('fiat_network'); // WIRE, DBS_ACT, CUBIX, SCB
            $table->string('ref_id')->unique();
            $table->string('routing_number_type')->nullable(); // ABA, SWIFT
            $table->string('memo_id')->nullable(); // Important for wire transfers
            $table->enum('status', ['VALID', 'DEPRECATED'])->nullable();
            $table->json('fiat_network_instructions')->nullable();
            $table->json('fiat_account_owner')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paxos_created_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiat_deposit_instructions');
    }
};
