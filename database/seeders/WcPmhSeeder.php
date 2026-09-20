<?php

namespace Database\Seeders;

use App\Models\WcPmh;
use Illuminate\Database\Seeder;

class WcPmhSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            'Diabetes Mellitus Type 2',
            'Essential Hypertension',
            'Peripheral Artery Disease',
            'Chronic Venous Insufficiency',
            'Immunosuppressed / Chemo',
        ];

        foreach ($options as $name) {
            WcPmh::firstOrCreate(
                ['WC_PMH_NAME' => $name],
                ['WC_PMH_IS_ACTIVE' => 1]
            );
        }
    }
}
