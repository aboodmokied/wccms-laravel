<?php

namespace App\Support;

use App\Models\JobLookup;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Once;
use Throwable;

/**
 * Which C_JOB_TB jobs are doctors and which are nurses, for the employee pick lists.
 *
 * - Doctors: HOSPITAL_DOCTOR_JOB_CODES (default 2, the hospital's own doctor lists).
 * - Nurses: HOSPITAL_NURSE_JOB_CODES when filled; otherwise found at run time in this hospital's C_JOB_TB by job
 *   name (hospital.employees.nurse_job_keywords). Every detection is logged with the codes and names it picked, or
 *   as a warning when it picked nothing or could not read C_JOB_TB (the nurse lists then show every active employee,
 *   with a notice on the screen). The result is cached for an hour (five minutes after a read error).
 */
final class StaffJobs
{
    public const SOURCE_SETTING = 'setting';

    public const SOURCE_DETECTED = 'detected';

    public const SOURCE_UNRESOLVED = 'unresolved';

    private const DETECTED_TTL_SECONDS = 3600;

    private const FAILED_TTL_SECONDS = 300;

    /**
     * @return list<int>
     */
    public static function doctorCodes(): array
    {
        return self::parseCodes(config('hospital.employees.job_codes.doctors'));
    }

    /**
     * @return list<int>
     */
    public static function nurseCodes(): array
    {
        return self::nurses()['codes'];
    }

    /**
     * @return array{codes: list<int>, source: string, names: array<int, string>, problem: ?string}
     */
    public static function nurses(): array
    {
        $setting = (string) config('hospital.employees.job_codes.nurses');
        $keywords = self::nurseKeywords();
        $hospitalNo = Hospital::number();

        // Once per request for these inputs (the pick lists ask for every employee).
        return once(fn (): array => self::resolveNurses($setting, $keywords, $hospitalNo));
    }

    /**
     * Drops the cached detection so the next nurses() reads C_JOB_TB again (and logs what it picked); used by the
     * deployment checklist.
     */
    public static function forgetDetectedNurses(): void
    {
        Cache::forget(self::cacheKey(self::nurseKeywords(), Hospital::number()));
        Once::flush();
    }

    /**
     * @param  list<string>  $keywords
     */
    private static function cacheKey(array $keywords, int $hospitalNo): string
    {
        return 'wccms:nurse-jobs:'.$hospitalNo.':'.md5(implode('|', $keywords));
    }

    /**
     * The nursing jobs of this hospital's C_JOB_TB, by name. Throws when C_JOB_TB cannot be read.
     *
     * @return array<int, string> JOB_CODE => "JOB_NAME_AR / JOB_NAME_EN"
     */
    public static function detectNurseJobs(): array
    {
        $keywords = self::nurseKeywords();
        $jobs = [];

        foreach (JobLookup::query()->orderBy('JOB_CODE')->get() as $job) {
            if (self::nameMatches($job->JOB_NAME_AR, $keywords) || self::nameMatches($job->JOB_NAME_EN, $keywords)) {
                $jobs[(int) $job->JOB_CODE] = self::jobName($job);
            }
        }

        return $jobs;
    }

    public static function jobName(JobLookup $job): string
    {
        return collect([$job->JOB_NAME_AR, $job->JOB_NAME_EN])
            ->map(fn (?string $name): string => trim((string) $name))
            ->filter()
            ->implode(' / ') ?: '(بلا اسم)';
    }

    /**
     * @return list<string>
     */
    public static function nurseKeywords(): array
    {
        return array_values(array_filter(array_map(
            fn ($keyword): string => trim((string) $keyword),
            (array) config('hospital.employees.nurse_job_keywords', [])
        )));
    }

    /**
     * @return list<int>
     */
    public static function parseCodes(mixed $codes): array
    {
        $values = is_array($codes) ? $codes : explode(',', (string) $codes);

        return array_values(array_unique(array_filter(
            array_map(fn ($code): ?int => HospitalCode::parse(is_string($code) ? trim($code) : $code), $values)
        )));
    }

    /**
     * @param  list<string>  $keywords
     * @return array{codes: list<int>, source: string, names: array<int, string>, problem: ?string}
     */
    private static function resolveNurses(string $setting, array $keywords, int $hospitalNo): array
    {
        $configured = self::parseCodes($setting);

        if ($configured !== []) {
            return ['codes' => $configured, 'source' => self::SOURCE_SETTING, 'names' => [], 'problem' => null];
        }

        $cacheKey = self::cacheKey($keywords, $hospitalNo);
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $names = self::detectNurseJobs();
        } catch (Throwable $exception) {
            $result = self::unresolved(sprintf(
                'تعذرت قراءة %s لاكتشاف وظائف التمريض: %s',
                Hospital::table('jobs'),
                $exception->getMessage()
            ));
            Log::warning('[wccms:staff-jobs] '.$result['problem'].' قوائم الممرضين تعرض كل الموظفين النشطين إلى أن يُضبط HOSPITAL_NURSE_JOB_CODES.');
            Cache::put($cacheKey, $result, self::FAILED_TTL_SECONDS);

            return $result;
        }

        if ($names === []) {
            $result = self::unresolved(sprintf(
                'لم توجد في %s للمستشفى %d وظيفة يحوي اسمها: %s.',
                Hospital::table('jobs'),
                $hospitalNo,
                implode('، ', $keywords)
            ));
            Log::warning('[wccms:staff-jobs] '.$result['problem'].' قوائم الممرضين تعرض كل الموظفين النشطين إلى أن يُضبط HOSPITAL_NURSE_JOB_CODES.');
        } else {
            $result = ['codes' => array_keys($names), 'source' => self::SOURCE_DETECTED, 'names' => $names, 'problem' => null];
            Log::info(sprintf(
                '[wccms:staff-jobs] HOSPITAL_NURSE_JOB_CODES فارغ؛ اكتُشفت وظائف التمريض من %s للمستشفى %d: %s',
                Hospital::table('jobs'),
                $hospitalNo,
                self::describe($names)
            ));
        }

        Cache::put($cacheKey, $result, self::DETECTED_TTL_SECONDS);

        return $result;
    }

    /**
     * @param  array<int, string>  $names
     */
    public static function describe(array $names): string
    {
        return collect($names)->map(fn (string $name, int $code): string => "{$code} ({$name})")->implode('، ');
    }

    /**
     * @return array{codes: list<int>, source: string, names: array<int, string>, problem: ?string}
     */
    private static function unresolved(string $problem): array
    {
        return ['codes' => [], 'source' => self::SOURCE_UNRESOLVED, 'names' => [], 'problem' => $problem];
    }

    /**
     * @param  list<string>  $keywords
     */
    private static function nameMatches(?string $name, array $keywords): bool
    {
        if ($name === null || trim($name) === '') {
            return false;
        }

        // Arabic diacritics and tatweel do not change the word.
        $normalized = mb_strtolower((string) preg_replace('/[\x{064B}-\x{0652}\x{0640}]/u', '', $name));

        foreach ($keywords as $keyword) {
            if (str_contains($normalized, mb_strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }
}
