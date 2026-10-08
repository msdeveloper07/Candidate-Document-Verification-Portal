<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name'         => 'RN License',
                'slug'         => 'rn-license',
                'description'  => 'Current, unencumbered registered nurse licence',
                'instructions' => 'Upload the full licence showing your name, licence number, issuing state and expiry date. Both sides if the details are split across them.',
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size_kb'  => 8192,
                'sort_order'   => 10,
                'icon'         => 'badge',
            ],
            [
                'name'         => 'BLS (AHA)',
                'slug'         => 'bls-aha',
                'description'  => 'Basic Life Support card — American Heart Association',
                'instructions' => 'Must be the AHA card specifically. The expiry date has to be readable, and the certification cannot already be expired.',
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size_kb'  => 5120,
                'sort_order'   => 20,
                'icon'         => 'heart',
            ],
            [
                'name'         => 'COVID-19 Vaccination Record',
                'slug'         => 'covid-vaccination',
                'description'  => 'Vaccination card or an approved exemption letter',
                'instructions' => 'Upload the CDC card or your state registry record showing every dose. If you hold a medical or religious exemption, upload that letter here instead.',
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size_kb'  => 5120,
                'sort_order'   => 30,
                'icon'         => 'shield',
            ],
            [
                'name'         => 'Resume',
                'slug'         => 'resume',
                'description'  => 'Current CV with your clinical history',
                'instructions' => 'Include your specialties, unit types and the month and year for each role. Please avoid gaps longer than 30 days without a short explanation.',
                'allowed_extensions' => ['pdf', 'doc', 'docx'],
                'max_size_kb'  => 5120,
                'sort_order'   => 40,
                'icon'         => 'file',
            ],
            [
                'name'         => 'Photo ID',
                'slug'         => 'photo-id',
                'description'  => 'Government-issued photo identification',
                'instructions' => 'A driver licence, passport or state ID. The photo, full name and expiry date must all be legible — no glare or cropped corners.',
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_size_kb'  => 5120,
                'sort_order'   => 50,
                'icon'         => 'id',
            ],
        ];

        foreach ($types as $type) {
            DocumentType::updateOrCreate(
                ['slug' => $type['slug']],
                $type + ['is_required_by_default' => true, 'is_active' => true]
            );
        }

        $this->retireGenericTypes();
    }

    /**
     * Earlier builds shipped a generic checklist. On an existing database those
     * rows are still active and would appear alongside the clinical ones, so
     * switch them off — they stay in the table because candidates already
     * requested them, and anything an admin added by hand is left alone.
     */
    private function retireGenericTypes(): void
    {
        $legacy = [
            'passport-photo', 'passport-bio-page', 'education-certificate',
            'bank-details', 'proof-of-address', 'cv',
            'police-clearance', 'medical-certificate',
        ];

        DocumentType::whereIn('slug', $legacy)
            ->update(['is_active' => false, 'is_required_by_default' => false]);
    }
}
