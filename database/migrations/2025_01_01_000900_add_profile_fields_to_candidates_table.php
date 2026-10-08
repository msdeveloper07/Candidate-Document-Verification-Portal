<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The profile fields are also declared in the create migration, so a brand new
 * database already has them. This migration exists for installs that ran the
 * original create before those fields were added — every column is guarded so
 * it is safe to run either way.
 */
return new class extends Migration
{
    /** column => closure that adds it */
    private function columns(): array
    {
        return [
            'middle_name'   => fn (Blueprint $t) => $t->string('middle_name', 80)->nullable()->after('first_name'),
            'date_of_birth' => fn (Blueprint $t) => $t->date('date_of_birth')->nullable()->after('last_name'),
            'address_line1' => fn (Blueprint $t) => $t->string('address_line1')->nullable()->after('country_name'),
            'address_line2' => fn (Blueprint $t) => $t->string('address_line2')->nullable()->after('address_line1'),
            'city'          => fn (Blueprint $t) => $t->string('city', 120)->nullable()->after('address_line2'),
            'state'         => fn (Blueprint $t) => $t->string('state', 120)->nullable()->after('city'),
            'postal_code'   => fn (Blueprint $t) => $t->string('postal_code', 20)->nullable()->after('state'),
            'availability'  => fn (Blueprint $t) => $t->string('availability', 32)->nullable()->after('position_applied'),
            'available_from'=> fn (Blueprint $t) => $t->date('available_from')->nullable()->after('availability'),
            'preferred_shift' => fn (Blueprint $t) => $t->string('preferred_shift', 32)->nullable()->after('available_from'),
        ];
    }

    public function up(): void
    {
        $missing = array_filter(
            $this->columns(),
            fn ($_, $name) => ! Schema::hasColumn('candidates', $name),
            ARRAY_FILTER_USE_BOTH
        );

        if (! $missing) {
            return;
        }

        Schema::table('candidates', function (Blueprint $table) use ($missing) {
            foreach ($missing as $add) {
                $add($table);
            }
        });
    }

    public function down(): void
    {
        $present = array_filter(
            array_keys($this->columns()),
            fn ($name) => Schema::hasColumn('candidates', $name)
        );

        if (! $present) {
            return;
        }

        Schema::table('candidates', function (Blueprint $table) use ($present) {
            $table->dropColumn(array_values($present));
        });
    }
};
