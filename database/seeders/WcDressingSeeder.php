<?php

namespace Database\Seeders;

use App\Models\WcDressing;
use Illuminate\Database\Seeder;

class WcDressingSeeder extends Seeder
{
    public function run(): void
    {
        $dressings = [
            'Silver Alginate Dressing',
            'Hydrocolloid Bandage',
            'Absorbent Foam Dressing',
            'Sterile Gauze & Tape',
            'Transparent Film Dressing',
        ];

        foreach ($dressings as $name) {
            WcDressing::firstOrCreate(
                ['WC_DRESS_NAME' => $name],
                ['WC_DRESS_IS_ACTIVE' => 1]
            );
        }
    }
}
