<?php

namespace App\Support;

use App\Enums\EmployeeRole;
use App\Models\Employee;
use App\Models\HospitalInfo;
use App\Models\JobLookup;
use App\Models\SexLookup;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The deployment checklist (php artisan wccms:check-hospital), also run once a day after a web request. Read only,
 * never stops the application: every finding is logged and returned, each warning with what to change.
 *
 * 1. Hospital: HOSPITAL_NO and the current hospital of this database (C_HOSPITAL_TB WHERE H_CURR = 1).
 * 2. Sex: every C_SEX_TB code of this hospital and how it is classified (exact SEX_NAME_EN).
 * 3. Doctors: the C_JOB_TB name of each HOSPITAL_DOCTOR_JOB_CODES code.
 * 4. Nurses: the codes of HOSPITAL_NURSE_JOB_CODES, or the ones detected by name, with their names.
 * 5. Staff: the "active employee" rule and the number of active doctors and nurses.
 * 6. Every C_JOB_TB job of this hospital, when a job warning was raised (to choose codes from).
 */
class HospitalStartupCheck
{
    public const INFO = 'info';

    public const WARNING = 'warning';

    public const SECTION_HOSPITAL = 'المستشفى';

    public const SECTION_SEX = 'رموز الجنس (C_SEX_TB)';

    public const SECTION_DOCTORS = 'وظائف الأطباء (C_JOB_TB)';

    public const SECTION_NURSES = 'وظائف التمريض (C_JOB_TB)';

    public const SECTION_STAFF = 'الموظفون النشطون';

    public const SECTION_JOBS = 'كل الوظائف (C_JOB_TB)';

    private const GRANT_FIX = 'تأكد أن مستخدم WCCMS يملك GRANT SELECT على جداول HOS (القسم 2 من wccms_oracle_schema.sql)، وأن HOSPITAL_SCHEMA=HOS في .env.';

    /**
     * @return list<array{level: string, section: string, message: string, fix: ?string}>
     */
    public function run(): array
    {
        $findings = [
            ...$this->hospitalSection(),
            ...$this->sexSection(),
            ...$this->doctorSection(),
            ...$this->nurseSection(),
            ...$this->staffSection(),
        ];

        if ($this->hasJobWarnings($findings)) {
            $findings[] = $this->allJobs();
        }

        foreach ($findings as $finding) {
            Log::log($finding['level'], '[wccms:check-hospital] '.$finding['message'].($finding['fix'] ? ' الحل: '.$finding['fix'] : ''));
        }

        return $findings;
    }

    /**
     * @return list<array{level: string, section: string, message: string, fix: ?string}>
     */
    private function hospitalSection(): array
    {
        $s = self::SECTION_HOSPITAL;
        $number = Hospital::number();
        $table = Hospital::table('hospitals');
        $findings = [$this->info($s, sprintf(
            'WCCMS يعمل على المستشفى رقم HOSPITAL_NO=%d (الجداول: %s).',
            $number,
            Hospital::isExternal() ? 'مخطط '.config('hospital.schema') : 'نسخة محلية للتطوير'
        ))];

        try {
            // The one deliberate read without the HOS_NO filter: it asks which hospital this database belongs to.
            $current = HospitalInfo::query()->withoutGlobalScopes()->where('H_CURR', 1)->get(['H_CODE', 'H_NAME_AR', 'H_NAME_EN']);
        } catch (Throwable $exception) {
            $findings[] = $this->warning($s, sprintf(
                'تعذرت قراءة %s للتحقق من رقم المستشفى؛ يستمر النظام بالرقم المضبوط %d. السبب: %s',
                $table, $number, $exception->getMessage()
            ), self::GRANT_FIX);

            return $findings;
        }

        if ($current->isEmpty()) {
            $findings[] = $this->warning($s, sprintf(
                'لا يوجد في %s صف فيه H_CURR = 1؛ لا يمكن التأكد من أن HOSPITAL_NO=%d هو مستشفى هذه القاعدة.',
                $table, $number
            ), 'تأكد من رقم المستشفى (H_CODE) من قائمة المستشفيات في '.$table.' ثم اضبط HOSPITAL_NO في .env.');

            return $findings;
        }

        if ($current->count() > 1) {
            $findings[] = $this->warning($s, sprintf(
                'يوجد في %s أكثر من صف فيه H_CURR = 1 (H_CODE: %s)؛ HOSPITAL_NO المضبوط هو %d.',
                $table, $current->pluck('H_CODE')->implode(', '), $number
            ), 'اختر رقم مستشفى هذه القاعدة من هذه الأرقام واضبطه في HOSPITAL_NO في .env.');

            return $findings;
        }

        $hospital = $current->first();
        $name = collect([$hospital->H_NAME_AR, $hospital->H_NAME_EN])->filter()->implode(' / ');
        $findings[] = $this->info($s, sprintf('المستشفى الحالي في %s (H_CURR = 1): %d — %s.', $table, (int) $hospital->H_CODE, $name));

        if ((int) $hospital->H_CODE !== $number) {
            $findings[] = $this->warning($s, sprintf(
                'عدم تطابق: HOSPITAL_NO المضبوط هو %d، لكن %s يقول إن هذه القاعدة للمستشفى %d (%s).',
                $number, $table, (int) $hospital->H_CODE, $name
            ), sprintf(
                'إن كانت هذه قاعدة المستشفى %d فاضبط HOSPITAL_NO=%d في .env؛ وإن كان المقصود المستشفى %d فصحّح اتصال القاعدة (DB_*).',
                (int) $hospital->H_CODE, (int) $hospital->H_CODE, $number
            ));
        } else {
            $findings[] = $this->info($s, sprintf('HOSPITAL_NO=%d يطابق مستشفى هذه القاعدة.', $number));
        }

        return $findings;
    }

    /**
     * @return list<array{level: string, section: string, message: string, fix: ?string}>
     */
    private function sexSection(): array
    {
        $s = self::SECTION_SEX;
        $table = Hospital::table('sexes');

        try {
            $sexes = SexLookup::query()->orderBy('SEX_CODE')->get();
        } catch (Throwable $exception) {
            return [$this->warning($s, sprintf(
                'تعذرت قراءة %s؛ سيظهر جنس كل المرضى "Unknown" في التقارير. السبب: %s',
                $table, $exception->getMessage()
            ), self::GRANT_FIX)];
        }

        if ($sexes->isEmpty()) {
            return [$this->warning($s, sprintf(
                'لا رموز جنس في %s للمستشفى %d؛ سيظهر جنس كل المرضى "Unknown" في التقارير.',
                $table, Hospital::number()
            ), 'تحقق من HOSPITAL_NO؛ إن كان صحيحاً فالجدول فارغ لهذا المستشفى ولا يُصلَح من .env.')];
        }

        $labels = [SexLookup::MALE => 'ذكر', SexLookup::FEMALE => 'أنثى', SexLookup::UNKNOWN => 'غير معروف'];
        $findings = $sexes->map(fn (SexLookup $sex): array => $this->info($s, sprintf(
            'الرمز %s: %s / %s — التصنيف: %s',
            $sex->SEX_CODE,
            $sex->SEX_NAME_AR ?? '(فارغ)',
            var_export($sex->SEX_NAME_EN, true),
            $labels[$sex->classification()]
        )))->values()->all();

        $unclassified = $sexes->filter(fn (SexLookup $sex): bool => $sex->classification() === SexLookup::UNKNOWN);

        if ($unclassified->isNotEmpty()) {
            $findings[] = $this->warning($s, sprintf(
                'رموز جنس في %s للمستشفى %d لا يطابق SEX_NAME_EN فيها "%s" أو "%s" تماماً، وستظهر "Unknown" في التقارير: %s',
                $table,
                Hospital::number(),
                config('hospital.sex.male_name_en'),
                config('hospital.sex.female_name_en'),
                $unclassified
                    ->map(fn (SexLookup $sex): string => sprintf('%s (SEX_NAME_EN=%s)', $sex->SEX_CODE, var_export($sex->SEX_NAME_EN, true)))
                    ->implode('، ')
            ), 'إن كان أحدها اسم الذكر أو الأنثى بكتابة أخرى (مثل MALE) فاضبط HOSPITAL_SEX_MALE_NAME_EN أو HOSPITAL_SEX_FEMALE_NAME_EN في .env بالكتابة نفسها حرفياً؛ وإلا فهذا الرمز ليس ذكراً ولا أنثى ويبقى "Unknown" عن قصد.');
        }

        return $findings;
    }

    /**
     * @return list<array{level: string, section: string, message: string, fix: ?string}>
     */
    private function doctorSection(): array
    {
        $s = self::SECTION_DOCTORS;
        $codes = StaffJobs::doctorCodes();

        if ($codes === []) {
            return [$this->warning(
                $s,
                'HOSPITAL_DOCTOR_JOB_CODES فارغ؛ قوائم الأطباء وفريق الجروح تعرض كل الموظفين النشطين.',
                'اضبط HOSPITAL_DOCTOR_JOB_CODES=2 (كود الأطباء في إجراءات المستشفى نفسه) أو أكواد الأطباء من قسم «كل الوظائف» في هذا الفحص.'
            )];
        }

        return $this->describeCodes($s, EmployeeRole::Doctor, $codes, 'من الإعداد HOSPITAL_DOCTOR_JOB_CODES');
    }

    /**
     * @return list<array{level: string, section: string, message: string, fix: ?string}>
     */
    private function nurseSection(): array
    {
        $s = self::SECTION_NURSES;
        StaffJobs::forgetDetectedNurses();
        $nurses = StaffJobs::nurses();

        return match ($nurses['source']) {
            StaffJobs::SOURCE_SETTING => $this->describeCodes($s, EmployeeRole::Nurse, $nurses['codes'], 'من الإعداد HOSPITAL_NURSE_JOB_CODES'),
            StaffJobs::SOURCE_DETECTED => [
                $this->info($s, sprintf(
                    'HOSPITAL_NURSE_JOB_CODES فارغ؛ اكتُشفت وظائف التمريض تلقائياً بالأسماء (%s): %s.',
                    implode('، ', StaffJobs::nurseKeywords()),
                    StaffJobs::describe($nurses['names'])
                )),
                $this->info($s, sprintf(
                    'راجع الأسماء أعلاه: إن كانت كلها وظائف تمريض فثبّتها في .env: HOSPITAL_NURSE_JOB_CODES=%s؛ وإن كان فيها ما ليس تمريضاً فاكتب الأكواد الصحيحة فقط.',
                    implode(',', $nurses['codes'])
                )),
            ],
            default => [$this->warning(
                $s,
                $nurses['problem'].' قوائم الممرضين وفريق الجروح تعرض كل الموظفين النشطين، مع تنبيه على الشاشة.',
                'اضبط HOSPITAL_NURSE_JOB_CODES في .env بأكواد وظائف التمريض من قسم «كل الوظائف» في هذا الفحص (مفصولة بفواصل).'
            )],
        };
    }

    /**
     * @param  list<int>  $codes
     * @return list<array{level: string, section: string, message: string, fix: ?string}>
     */
    private function describeCodes(string $section, EmployeeRole $role, array $codes, string $source): array
    {
        $table = Hospital::table('jobs');

        try {
            $jobs = JobLookup::query()->whereIn('JOB_CODE', $codes)->get()
                ->mapWithKeys(fn (JobLookup $job): array => [(int) $job->JOB_CODE => StaffJobs::jobName($job)]);
        } catch (Throwable $exception) {
            return [$this->warning($section, sprintf(
                'تعذرت قراءة %s للتحقق من أكواد وظائف %s (%s). السبب: %s',
                $table, $role->label(), implode(', ', $codes), $exception->getMessage()
            ), self::GRANT_FIX)];
        }

        $findings = [];

        if ($jobs->isNotEmpty()) {
            $findings[] = $this->info($section, sprintf('قوائم %s (%s) تعرض الوظائف: %s.', $role->label(), $source, StaffJobs::describe($jobs->all())));
        }

        $missing = array_values(array_filter($codes, fn (int $code): bool => ! $jobs->has($code)));

        if ($missing !== []) {
            $findings[] = $this->warning($section, sprintf(
                'أكواد وظائف %s غير موجودة في %s للمستشفى %d: %s.',
                $role->label(), $table, Hospital::number(), implode(', ', $missing)
            ), sprintf('صحّح %s في .env بأكواد موجودة من قسم «كل الوظائف» في هذا الفحص.', $role->envVariable()));
        }

        return $findings;
    }

    /**
     * @return list<array{level: string, section: string, message: string, fix: ?string}>
     */
    private function staffSection(): array
    {
        $s = self::SECTION_STAFF;
        $active = config('hospital.employees.active_value');
        $record = config('hospital.employees.record_status');
        $recordLists = config('hospital.employees.record_status_lists') === 'all' ? 'كل القوائم' : 'قوائم الأطباء';

        $findings = [$this->info($s, sprintf(
            'الموظف النشط: %s في كل القوائم؛ %s في %s.',
            blank($active) ? 'بلا شرط على EMP_ACTIVE' : 'EMP_ACTIVE = '.$active,
            blank($record) ? 'بلا شرط على EMP_REC_CD' : 'EMP_REC_CD = '.$record,
            $recordLists
        ))];

        try {
            foreach ([EmployeeRole::Doctor, EmployeeRole::Nurse] as $role) {
                $count = Employee::query()->active()->inRole($role)->count();
                $unfiltered = $role->isUnfiltered() ? ' (غير مصفّاة بالوظيفة: كل الموظفين النشطين)' : '';

                if ($count === 0) {
                    $findings[] = $this->warning($s, sprintf('لا يوجد أي موظف نشط في قوائم %s؛ ستكون حقولهم فارغة.', $role->label()), sprintf(
                        'راجع %s، وHOSPITAL_EMP_ACTIVE (الآن %s)، و%s.',
                        $role->envVariable(),
                        blank($active) ? 'فارغ' : $active,
                        $role === EmployeeRole::Doctor ? 'HOSPITAL_EMP_REC_CD (الآن '.(blank($record) ? 'فارغ' : $record).')' : 'HOSPITAL_EMP_REC_CD_LISTS'
                    ));
                } else {
                    $findings[] = $this->info($s, sprintf('عدد %s النشطين: %d%s.', $role->label(), $count, $unfiltered));
                }
            }
        } catch (Throwable $exception) {
            $findings[] = $this->warning($s, sprintf(
                'تعذر عدّ الموظفين النشطين في %s. السبب: %s',
                Hospital::table('employees'), $exception->getMessage()
            ), self::GRANT_FIX.' وتأكد أن الأعمدة EMP_ACTIVE و EMP_REC_CD و EMP_JOB_CD موجودة.');
        }

        return $findings;
    }

    /**
     * Every job of this hospital, to choose codes from when a job warning was raised.
     *
     * @return array{level: string, section: string, message: string, fix: ?string}
     */
    private function allJobs(): array
    {
        try {
            $jobs = JobLookup::query()->orderBy('JOB_CODE')->get()
                ->mapWithKeys(fn (JobLookup $job): array => [(int) $job->JOB_CODE => StaffJobs::jobName($job)]);
        } catch (Throwable) {
            return $this->info(self::SECTION_JOBS, 'قائمة الوظائف غير متاحة (تعذرت قراءة '.Hospital::table('jobs').').');
        }

        return $this->info(self::SECTION_JOBS, sprintf(
            'قائمة الوظائف في %s للمستشفى %d (%d وظيفة): %s',
            Hospital::table('jobs'),
            Hospital::number(),
            $jobs->count(),
            $jobs->isEmpty() ? '(فارغة)' : StaffJobs::describe($jobs->all())
        ));
    }

    /**
     * @param  list<array{level: string, section: string, message: string, fix: ?string}>  $findings
     */
    private function hasJobWarnings(array $findings): bool
    {
        return collect($findings)->contains(fn (array $finding): bool => $finding['level'] === self::WARNING
            && in_array($finding['section'], [self::SECTION_DOCTORS, self::SECTION_NURSES], true));
    }

    /**
     * @return array{level: string, section: string, message: string, fix: ?string}
     */
    private function info(string $section, string $message): array
    {
        return ['level' => self::INFO, 'section' => $section, 'message' => $message, 'fix' => null];
    }

    /**
     * @return array{level: string, section: string, message: string, fix: ?string}
     */
    private function warning(string $section, string $message, string $fix): array
    {
        return ['level' => self::WARNING, 'section' => $section, 'message' => $message, 'fix' => $fix];
    }
}
