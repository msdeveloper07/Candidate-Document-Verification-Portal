<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('dial_code', 8);
            $table->string('phone', 20);
            $table->string('code_hash');
            $table->string('purpose', 40)->default('candidate_login');
            $table->string('channel', 16)->default('sms');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index(['dial_code', 'phone', 'purpose'], 'otp_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
