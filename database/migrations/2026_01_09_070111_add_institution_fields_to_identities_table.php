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
        Schema::table('identities', function (Blueprint $table) {
            // Add identity type
            $table->enum('identity_type', ['PERSON', 'INSTITUTION'])->default('PERSON')->after('verifier_type');
            
            // Make person-specific fields nullable for institution identities
            $table->string('first_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
            $table->date('date_of_birth')->nullable()->change();
            $table->string('nationality')->nullable()->change();
            
            // Make address fields nullable for institution identities
            $table->string('address_country')->nullable()->change();
            $table->string('address1')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('email')->nullable()->change();
            
            // Add institution-specific fields
            $table->json('institution_details')->nullable()->after('zip_code');
            $table->json('institution_members')->nullable()->after('institution_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identities', function (Blueprint $table) {
            $table->dropColumn(['identity_type', 'institution_details', 'institution_members']);
            
            // Revert nullable changes
            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
            $table->date('date_of_birth')->nullable(false)->change();
            $table->string('nationality')->nullable(false)->change();
            $table->string('address_country')->nullable(false)->change();
            $table->string('address1')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
        });
    }
};
