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
        Schema::create('identity_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('identity_id')->constrained()->onDelete('cascade');
            $table->string('document_type'); // Local type: proof_of_identity, proof_of_residency, etc.
            $table->string('paxos_document_type'); // Paxos type: PROOF_OF_IDENTITY, PROOF_OF_RESIDENCY, etc.
            $table->string('file_path'); // Storage path to the file
            $table->string('file_name'); // Original file name
            $table->unsignedBigInteger('file_size'); // File size in bytes
            $table->string('mime_type'); // File MIME type
            $table->enum('upload_status', ['pending', 'uploaded', 'failed'])->default('pending');
            $table->string('paxos_document_id')->nullable(); // ID returned from Paxos after upload
            $table->timestamp('uploaded_at')->nullable(); // When uploaded to Paxos
            $table->text('error_message')->nullable(); // Error if upload failed
            $table->timestamps();

            // One document per type per identity
            $table->unique(['identity_id', 'document_type']);
            
            // Index for querying pending uploads
            $table->index('upload_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identity_documents');
    }
};
