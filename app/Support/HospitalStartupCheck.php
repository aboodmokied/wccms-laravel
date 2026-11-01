<?php

namespace App\Support;

use App\Enums\EmployeeRole;
use App\Models\HospitalInfo;
use App\Models\JobLookup;
use App\Models\SexLookup;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Read-only start-up checks of the hospital configuration. Never writes, never stops the application: every finding
 * is logged (and returned for the wccms:check-hospital command).
 *
 * 1. Logs the hospital number in use (HOSPITAL_NO).
 * 2. Compares it with the hospital this database says it belongs to: C_HOSPITAL_TB WHERE H_CURR = 1.
 * 3. Lists the C_SEX_TB codes of this hospital whose SEX_NAME_EN is not exactly the configured Male/Female name;
 *    reports show those patients as "Unknown".
 * 4. Names the C_JOB_TB jobs behind the doctor and nurse pick lists (hospital.employees.job_codes), and warns about
 *    a code missing from C_JOB_TB or a role with no codes (its lists then show every active employee).
 */
class HospitalStartupCheck
{
    public const INFO = 'info';

    public const WARNING = 'warning';

    /**
     * @return list<array{level: string, message: string}>
     */
    public function run(): array
    {
        $findings = [
            $this->finding(self::INFO, sprintf(
                'WCCMS يعمل على المستشفى رقم HOSPITAL_NO=%d (الجداول: %s).',
                Hospital::number(),
                Hospital::isExternal() ? 'مخطط '.config('hospital.schema') : 'نسخة محلية'
            )),
            $this->checkCurrentHospital(),
            ...$this->checkSexCodes(),
            ...$this->checkJobCodes(),
        ];

        foreach ($findings as $finding) {
            Log::log($finding['level'], '[wccms:check-hospital] '.$finding['message']);
        }

        return $findings;
    }

    /**
     * @return array{level: string, message: string}
     */
    private function checkCurrentHospital(): array
    {
        $table = Hospital::table('hospitals');

        try {
            // The one deliberate read without the HOS_NO filter: it asks which hospital this database belongs to.
            $current = HospitalInfo::query()
                ->withoutGlobalScopes()
                ->where('H_CURR', 1)
                ->get(['H_CODE', 'H_NAME_AR']);
        } catch (Throwable $exception) {
            return $this->finding(self::WARNING, sprintf(
                'تعذرت قراءة %s للتحقق من رقم المستشفى؛ يستمر النظام بالرقم المضبوط %d. السبب: %s',
                $table,
                Hospital::number(),
                $exception->getMessage()
            ));
        }

        if ($current->isEmpty()) {
            return $this->finding(self::WARNING, sprintf(
                'لا يوجد في %s صف فيه H_CURR = 1؛ لا يمكن التأكد من أن HOSPITAL_NO=%d هو مستشفى هذه القاعدة.',
                $table,
                Hospital::number()
            ));
        }

        if ($current->count() > 1) {
            return $this->finding(self::WARNING, sprintf(
                'يوجد في %s أكثر من صف فيه H_CURR = 1 (H_CODE: %s)؛ HOSPITAL_NO المضبوط هو %d.',
                $table,
                $current->pluck('H_CODE')->implode(', '),
                Hospital::number()
            ));
        }

        $hospital = $current->first();

        if ((int) $hospital->H_CODE !== Hospital::number()) {
            return $this->finding(self::WARNING, sprintf(
                'عدم تطابق: HOSPITAL_NO المضبوط هو %d، لكن %s يقول إن هذه القاعدة للمستشفى %d (%s) (H_CURR = 1).',
                Hospital::number(),
                $table,
                (int) $hospital->H_CODE,
                (string) $hospital->H_NAME_AR
            ));
        }

        return $this->finding(self::INFO, sprintf(
            'HOSPITAL_NO=%d يطابق مستشفى هذه القاعدة في %s (%s).',
            Hospital::number(),
            $table,
            (string) $hospital->H_NAME_AR
        ));
    }

    /**
     * @return list<array{level: string, message: string}>
     */
    private function checkSexCodes(): array
    {
        $table = Hospital::table('sexes');

        try {
            $sexes = SexLookup::query()->orderBy('SEX_CODE')->get();
        } catch (Throwable $exception) {
            return [$this->finding(self::WARNING, sprintf(
                'تعذرت قراءة %s؛ سيظهر جنس كل المرضى "Unknown" في التقارير. السبب: %s',
                $table,
                $exception->getMessage()
            ))];
        }

        $unclassified = $sexes->filter(fn (SexLookup $sex): bool => $sex->classification() === SexLookup::UNKNOWN);

        if ($unclassified->isEmpty()) {
            return [$this->finding(self::INFO, sprintf(
                'كل رموز الجنس في %s للمستشفى %d مصنفة (SEX_NAME_EN = "%s" أو "%s").',
                $table,
                Hospital::number(),
                config('hospital.sex.male_name_en'),
                config('hospital.sex.female_name_en')
            ))];
        }

        return [$this->finding(self::WARNING, sprintf(
            'رموز جنس في %s للمستشفى %d لا يطابق SEX_NAME_EN فيها "%s" أو "%s" تماماً، وستظهر "Unknown" في التقارير: %s',
            $table,
            Hospital::number(),
            config('hospital.sex.male_name_en'),
            config('hospital.sex.female_name_en'),
            $unclassified
                ->map(fn (SexLookup $sex): string => sprintf('%s (SEX_NAME_EN=%s)', $sex->SEX_CODE, var_export($sex->SEX_NAME_EN, true)))
                ->implode('، ')
        ))];
    }

    /**
     * @return list<array{level: string, message: string}>
     */
    private function checkJobCodes(): array
    {
        $table = Hospital::table('jobs');
        $findings = [];

        foreach ([EmployeeRole::Doctor, EmployeeRole::Nurse] as $role) {
            $codes = $role->jobCodes();

            if ($codes === []) {
                $findings[] = $this->finding(self::WARNING, sprintf(
                    'لم تُضبط أكواد وظائف %s (%s)؛ قوائم %s وقائمة فريق الجروح تعرض كل الموظفين النشطين.',
                    $role->label(),
                    $role->envVariable(),
                    $role->label()
                ));

                continue;
            }

            try {
                $jobs = JobLookup::query()->whereIn('JOB_CODE', $codes)->pluck('JOB_NAME_AR', 'JOB_CODE');
            } catch (Throwable $exception) {
                $findings[] = $this->finding(self::WARNING, sprintf(
                    'تعذرت قراءة %s للتحقق من أكواد وظائف %s (%s). السبب: %s',
                    $table,
                    $role->label(),
                    implode(', ', $codes),
                    $exception->getMessage()
                ));

                continue;
            }

            $missing = array_values(array_filter($codes, fn (int $code): bool => ! $jobs->has($code)));

            if ($missing !== []) {
                $findings[] = $this->finding(self::WARNING, sprintf(
                    'أكواد وظائف %s غير موجودة في %s للمستشفى %d: %s (%s).',
                    $role->label(),
                    $table,
                    Hospital::number(),
                    implode(', ', $missing),
                    $role->envVariable()
                ));
            }

            if ($jobs->isNotEmpty()) {
                $findings[] = $this->finding(self::INFO, sprintf(
                    'قوائم %s تعرض الوظائف: %s.',
                    $role->label(),
                    $jobs->map(fn (?string $name, int|string $code): string => sprintf('%s (%s)', $code, $name))->implode('، ')
                ));
            }
        }

        return $findings;
    }

    /**
     * @return array{level: string, message: string}
     */
    private function finding(string $level, string $message): array
    {
        return ['level' => $level, 'message' => $message];
    }
}
