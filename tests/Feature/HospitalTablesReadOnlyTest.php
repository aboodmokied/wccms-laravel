<?php

namespace Tests\Feature;

use App\Exceptions\HospitalTableReadOnly;
use App\Models\AdmissionPatient;
use App\Models\Employee;
use App\Models\HospitalInfo;
use App\Models\HospitalLocation;
use App\Models\InpatientStay;
use App\Models\JobLookup;
use App\Models\MrPatient;
use App\Models\SexLookup;
use App\Support\LocalHospitalCopy;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * WCCMS only reads the hospital tables: no model of them can insert, update or delete a row, by any route Eloquent
 * offers, with or without model events.
 */
class HospitalTablesReadOnlyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{class-string<Model>, array<string, mixed>}>
     */
    public static function hospitalModels(): array
    {
        return [
            'MR_PATIENT_TB' => [MrPatient::class, ['MRP_ID' => 400000007]],
            'EMPLOYEE_TB' => [Employee::class],
            'ADMISSION_PATIENT_TB' => [AdmissionPatient::class, ['ADMISSION_PATREC_CD' => 7, 'ADMISSION_ENTER_DATE' => '2026-01-01 08:00:00']],
            'INPATIENT_ADM_TB' => [InpatientStay::class],
            'C_LOCATION_TB' => [HospitalLocation::class],
            'C_SEX_TB' => [SexLookup::class],
            'C_JOB_TB' => [JobLookup::class],
            'C_HOSPITAL_TB' => [HospitalInfo::class],
        ];
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $requiredColumns
     */
    #[DataProvider('hospitalModels')]
    public function test_every_write_through_the_model_is_refused_and_changes_nothing(string $modelClass, array $requiredColumns = []): void
    {
        $keyName = (new $modelClass)->getKeyName();
        $row = LocalHospitalCopy::firstOrInsert($modelClass, [$keyName => 7], $requiredColumns);
        $before = $this->tableSnapshot($row);

        $attempts = [
            'create' => fn () => $modelClass::create([$keyName => 8]),
            'forceCreate' => fn () => $modelClass::forceCreate([$keyName => 8]),
            'firstOrCreate' => fn () => $modelClass::firstOrCreate([$keyName => 8]),
            'updateOrCreate' => fn () => $modelClass::updateOrCreate([$keyName => 7], ['HOS_NO' => 1]),
            'new model save' => fn () => (new $modelClass)->forceFill([$keyName => 8])->save(),
            'saveQuietly' => fn () => (new $modelClass)->forceFill([$keyName => 8])->saveQuietly(),
            'model update' => fn () => $row->update([$keyName => 9]),
            'model save' => fn () => $row->forceFill([$keyName => 9])->save(),
            'model increment' => fn () => $row->increment('HOS_NO'),
            'model delete' => fn () => $row->delete(),
            'deleteQuietly' => fn () => $row->deleteQuietly(),
            'without events' => fn () => Model::withoutEvents(fn () => $row->delete()),
            'destroy' => fn () => $modelClass::destroy(7),
            'query insert' => fn () => $modelClass::query()->insert([$keyName => 8, 'HOS_NO' => 1]),
            'query insertGetId' => fn () => $modelClass::query()->insertGetId([$keyName => 8, 'HOS_NO' => 1]),
            'query insertOrIgnore' => fn () => $modelClass::query()->insertOrIgnore([$keyName => 8, 'HOS_NO' => 1]),
            'query upsert' => fn () => $modelClass::query()->upsert([[$keyName => 8, 'HOS_NO' => 1]], [$keyName, 'HOS_NO']),
            'query updateOrInsert' => fn () => $modelClass::query()->updateOrInsert([$keyName => 8], ['HOS_NO' => 1]),
            'query update' => fn () => $modelClass::query()->update(['HOS_NO' => 1]),
            'query increment' => fn () => $modelClass::query()->increment('HOS_NO'),
            'query decrementEach' => fn () => $modelClass::query()->decrementEach(['HOS_NO' => 1]),
            'query touch' => fn () => $modelClass::query()->touch(),
            'query delete' => fn () => $modelClass::query()->whereKey(7)->delete(),
            'query forceDelete' => fn () => $modelClass::query()->forceDelete(),
            'unscoped delete' => fn () => $modelClass::query()->withoutGlobalScopes()->delete(),
            'truncate' => fn () => $modelClass::truncate(),
        ];

        foreach ($attempts as $label => $attempt) {
            $this->assertRefused($label, $attempt);
            $row = $modelClass::query()->findOrFail(7);
        }

        $this->assertSame($before, $this->tableSnapshot($row));
    }

    public function test_the_local_copy_cannot_be_filled_when_the_real_hospital_tables_are_configured(): void
    {
        config(['hospital.schema' => 'HOS']);

        $this->expectException(HospitalTableReadOnly::class);

        LocalHospitalCopy::firstOrInsert(Employee::class, ['EMP_SERIAL' => 1]);
    }

    private function assertRefused(string $label, Closure $attempt): void
    {
        try {
            $attempt();
        } catch (HospitalTableReadOnly) {
            return;
        }

        $this->fail("{$label} was not refused.");
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function tableSnapshot(Model $row): array
    {
        return DB::table($row->getTable())->get()->map(fn ($r) => (array) $r)->all();
    }
}
