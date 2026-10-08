<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_required')->default(true);
            $table->text('instructions')->nullable();
            $table->timestamps();

            $table->unique(['candidate_id', 'document_type_id'], 'candidate_requirement_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_requirements');
    }
};
