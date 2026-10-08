<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach (EmailTemplate::defaults() as $key => $wording) {
            // firstOrCreate, not updateOrCreate — never overwrite an admin's edits.
            EmailTemplate::firstOrCreate(['key' => $key], $wording + ['is_active' => true]);
        }
    }
}
