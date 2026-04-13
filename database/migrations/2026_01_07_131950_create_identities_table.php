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
        Schema::create('identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->uuid('paxos_identity_id')->nullable()->unique();
            $table->string('ref_id')->unique();
            $table->string('verifier_type')->default('PAXOS');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('nationality');
            $table->string('cip_id')->nullable();
            $table->string('cip_id_type')->nullable();
            $table->string('cip_id_country')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email');
            // Address fields
            $table->string('address_country');
            $table->string('address1');
            $table->string('city');
            $table->string('province')->nullable();
            $table->string('zip_code')->nullable();
            // Verification status
            $table->enum('id_verification_status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->enum('sanctions_verification_status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identities');
    }
};
