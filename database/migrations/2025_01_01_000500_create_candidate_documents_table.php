<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained()->cascadeOnDelete();

            $table->string('original_name');
            $table->string('file_path');
            $table->string('disk', 32)->default('documents');
            $table->string('mime_type', 128)->nullable();
            $table->string('extension', 12)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum', 64)->nullable();

            $table->string('status', 24)->default('pending')->index();
            $table->text('remarks')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->string('uploaded_ip', 45)->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // One live file per requirement; history lives in document_revisions.
            $table->unique(['candidate_id', 'document_type_id'], 'candidate_document_unique');
        });

        Schema::create('document_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_document_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('file_path');
            $table->string('disk', 32)->default('documents');
            $table->string('mime_type', 128)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->unsignedSmallInteger('version')->default(1);
            $table->string('status_at_replacement', 24)->nullable();
            $table->text('remarks_at_replacement')->nullable();
            $table->timestamp('replaced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_revisions');
        Schema::dropIfExists('candidate_documents');
    }
};
