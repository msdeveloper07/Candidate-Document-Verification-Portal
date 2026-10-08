<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 24)->unique();
            $table->string('first_name', 80);
            $table->string('middle_name', 80)->nullable();
            $table->string('last_name', 80)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('email')->index();

            // Stored split so international numbers stay unambiguous.
            $table->string('dial_code', 8);
            $table->string('phone', 20);
            $table->string('country_code', 2)->nullable();
            $table->string('country_name', 80)->nullable();

            // Postal address. State stays free text so provinces and emirates fit too.
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->string('postal_code', 20)->nullable();

            $table->string('position_applied')->nullable();
            $table->string('availability', 32)->nullable();
            $table->date('available_from')->nullable();
            $table->string('preferred_shift', 32)->nullable();
            $table->string('status', 32)->default('invited')->index();

            $table->foreignId('invited_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('invite_token', 96)->nullable()->unique();
            $table->timestamp('invite_sent_at')->nullable();
            $table->timestamp('invite_expires_at')->nullable();

            $table->timestamp('first_accessed_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();

            $table->text('internal_notes')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['dial_code', 'phone'], 'candidates_phone_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
