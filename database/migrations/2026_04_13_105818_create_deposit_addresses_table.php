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
        Schema::create('deposit_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->string('paxos_deposit_address_id');
            $table->string('ref_id')->unique();
            $table->string('crypto_network');
            $table->text('address');
            $table->string('paxos_profile_id')->nullable();
            $table->string('paxos_identity_id')->nullable();
            $table->string('paxos_account_id')->nullable();
            $table->string('conversion_target_asset')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposit_addresses');
    }
};
