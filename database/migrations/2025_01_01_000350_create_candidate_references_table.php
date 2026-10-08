<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();

            // 1, 2, 3 — fixed slots so the form always renders three rows.
            $table->unsignedTinyInteger('slot');

            $table->string('name', 120)->nullable();
            $table->string('relationship', 120)->nullable();
            $table->string('organisation', 160)->nullable();
            $table->string('email')->nullable();
            $table->string('dial_code', 8)->nullable();
            $table->string('phone', 20)->nullable();

            $table->timestamps();

            $table->unique(['candidate_id', 'slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_references');
    }
};
