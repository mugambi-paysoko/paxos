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
        Schema::create('processed_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique(); // Paxos event ID
            $table->string('event_type'); // e.g., 'identity.documents_required'
            $table->string('identity_id')->nullable(); // If applicable
            $table->timestamp('processed_at');
            $table->json('event_data')->nullable(); // Store full event for reference
            $table->timestamps();

            $table->index('event_type');
            $table->index('identity_id');
            $table->index('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processed_events');
    }
};
