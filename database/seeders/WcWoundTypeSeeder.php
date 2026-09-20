<?php

namespace Database\Seeders;

use App\Models\WcWoundType;
use Illuminate\Database\Seeder;

class WcWoundTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Chronic Neuropathic Ulcer',
            'Surgical Incisional Wound',
            'Second Degree Burn',
            'Traumatic Laceration',
            'Venous Stasis Ulcer',
        ];

        foreach ($types as $name) {
            WcWoundType::firstOrCreate(
                ['WC_WTYPE_NAME' => $name],
                ['WC_WTYPE_IS_ACTIVE' => 1]
            );
        }
    }
}
