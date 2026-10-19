<?php

namespace Database\Seeders;

use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcDressing;
use App\Models\WcMedication;
use App\Models\WcPmh;
use App\Models\WcProcedure;
use App\Models\WcWoundType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * The clinic workbook's value lists (spec 013, contracts/value-list-mapping.md).
 *
 * Safe to run repeatedly: a value is created only when its name is missing, and existing rows are never updated
 * or deactivated. Tissue states (Sloughing, Necrotic, black tissue, Granulation, Pocketing) are structured
 * assessment choices, not wound types, so they are not seeded here.
 */
class WcClinicWorkbookListsSeeder extends Seeder
{
    public const DIAGNOSES = ['DF', 'bedsore PEU', 'bedsore HAPU', 'SSI', 'Burn', 'Inf Wound', 'Row Area'];

    public const WOUND_TYPES = ['Pressure', 'Surgical', 'Puncture', 'Crush', 'Avulsion', 'Abrasion', 'Laceration', 'blister'];

    public const DRESSINGS = ['Gauze DSG', 'Foam DSG', 'Antimicrobial DSG', 'hydrogel', 'hydrocoill', 'vaslin gauze', 'wet polidine'];

    public const MEDICATIONS = [
        'Fucidin cream', 'Silvano Spray', 'Dr wound', 'Silverol cream', 'Burno', 'Calmex', 'Polydine ointment', 'Hylorin',
        'Betacorten', 'Chlorhexidine Gel', 'Bactazon', 'Sodeem', 'Zaha', 'Dexeryl', 'Iuroxol', 'mebo',
    ];

    public const PROCEDURES = ['debridement'];

    public const DEPARTMENTS = [
        'Medical Male', 'Medical Female', 'CCU', 'ICU', 'Surgical Male', 'Surgical Female', 'Special Male', 'Special Female',
        'Orthopedic Male', 'Orthopedic Female', 'PED ICU', 'SPECIAL MALE B', 'ONCOLOGY',
    ];

    public const PAST_MEDICAL_HISTORY = ['DM', 'HTN', 'Vascular Disease', 'Immune Disease'];

    public function run(): void
    {
        $this->seedList(WcDiagnosis::class, 'WC_DIAG', self::DIAGNOSES);
        $this->seedList(WcWoundType::class, 'WC_WTYPE', self::WOUND_TYPES);
        $this->seedList(WcDressing::class, 'WC_DRESS', self::DRESSINGS);
        $this->seedList(WcMedication::class, 'WC_MED', self::MEDICATIONS);
        $this->seedList(WcProcedure::class, 'WC_PROC', self::PROCEDURES);
        $this->seedList(WcDepartment::class, 'WC_DEPT', self::DEPARTMENTS);
        $this->seedList(WcPmh::class, 'WC_PMH', self::PAST_MEDICAL_HISTORY);
    }

    /**
     * @param  class-string<Model>  $model
     * @param  list<string>  $names
     */
    private function seedList(string $model, string $prefix, array $names): void
    {
        foreach ($names as $name) {
            $model::query()->firstOrCreate(
                ["{$prefix}_NAME" => $name],
                ["{$prefix}_IS_ACTIVE" => 1],
            );
        }
    }
}
