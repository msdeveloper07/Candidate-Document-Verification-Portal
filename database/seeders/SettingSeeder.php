<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'agency_name'        => ['Cyberbells LLC', 'branding'],
            'agency_tagline'     => ['Healthcare staffing & credentialing', 'branding'],
            'support_email'      => ['', 'branding'],
            'support_phone'      => ['', 'branding'],
            'invite_valid_days'  => ['14', 'workflow'],
            'otp_length'         => ['6', 'workflow'],
            'otp_ttl_minutes'    => ['10', 'workflow'],
        ];

        foreach ($defaults as $key => [$value, $group]) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        }
    }
}
