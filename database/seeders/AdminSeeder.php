<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => env('SEED_ADMIN_EMAIL', 'admin@hireflow.test')],
            [
                'name'        => env('SEED_ADMIN_NAME', 'Agency Owner'),
                'password'    => env('SEED_ADMIN_PASSWORD', 'Password@123'),
                'role'        => AdminRole::SuperAdmin,
                'designation' => 'Director',
                'is_active'   => true,
            ]
        );

        Admin::updateOrCreate(
            ['email' => 'recruiter@hireflow.test'],
            [
                'name'        => 'Sara Whitfield',
                'password'    => 'Password@123',
                'role'        => AdminRole::Recruiter,
                'designation' => 'Recruitment Officer',
                'is_active'   => true,
            ]
        );
    }
}
