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
        Schema::create('fiat_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('identity_id')->constrained('identities')->onDelete('cascade');
            $table->uuid('paxos_fiat_account_id')->unique();
            $table->uuid('paxos_identity_id')->nullable();
            $table->uuid('paxos_account_id')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, etc.
            $table->json('fiat_account_owner')->nullable(); // Store person_details or institution_details
            $table->json('fiat_network_instructions')->nullable(); // Store wire/cubix instructions
            $table->timestamp('paxos_created_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiat_accounts');
    }
};
