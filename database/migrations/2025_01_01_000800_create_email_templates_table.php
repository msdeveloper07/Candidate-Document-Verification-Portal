<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();

            // Fixed keys the code looks up — admins edit the wording, not the key.
            $table->string('key', 64)->unique();
            $table->string('name');
            $table->string('description')->nullable();

            $table->string('subject');
            $table->string('heading');
            $table->text('body');
            $table->string('button_label')->nullable();
            $table->text('footer_note')->nullable();

            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
