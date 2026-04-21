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
        Schema::create('fiat_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('fiat_account_id')->constrained('fiat_accounts')->onDelete('cascade');
            $table->foreignId('identity_id')->nullable()->constrained('identities')->onDelete('set null');
            $table->uuid('paxos_transfer_id')->unique();
            $table->string('ref_id')->nullable();
            $table->string('amount');
            $table->string('asset')->default('USD');
            $table->string('status')->nullable();
            $table->string('transfer_type')->nullable();
            $table->string('memo')->nullable();
            $table->json('metadata')->nullable();
            $table->json('paxos_response')->nullable();
            $table->timestamp('paxos_created_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiat_withdrawals');
    }
};
