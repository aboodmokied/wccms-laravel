<?php

namespace App\Console\Commands;

use App\Support\HospitalStartupCheck;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('wccms:check-hospital')]
#[Description('Deployment checklist (read only): hospital number, sex codes, doctor and nurse jobs, active staff; every warning with what to change in .env')]
class CheckHospital extends Command
{
    public function handle(HospitalStartupCheck $check): int
    {
        $findings = $check->run();

        $this->line('');
        $this->line('<options=bold>قائمة فحص التسليم — WCCMS على قاعدة المستشفى (قراءة فقط)</>');

        foreach (collect($findings)->groupBy('section') as $section => $sectionFindings) {
            $this->line('');
            $this->line("<options=bold>■ {$section}</>");

            foreach ($sectionFindings as $finding) {
                $finding['level'] === HospitalStartupCheck::WARNING
                    ? $this->line("  <fg=yellow>! {$finding['message']}</>")
                    : $this->line("  ✓ {$finding['message']}");
            }
        }

        $warnings = array_values(array_filter($findings, fn (array $finding): bool => $finding['level'] === HospitalStartupCheck::WARNING));

        $this->line('');
        $this->line('<options=bold>■ التحذيرات: '.count($warnings).'</>');

        if ($warnings === []) {
            $this->info('  لا تحذيرات. النتائج مكتوبة أيضاً في storage/logs/laravel.log.');
        }

        foreach ($warnings as $index => $warning) {
            $this->line(sprintf('  <fg=yellow>%d. %s</>', $index + 1, $warning['message']));
            $this->line('     الحل: '.$warning['fix']);
        }

        $this->line('');

        // Warnings never stop WCCMS; the command reports them and always succeeds.
        return self::SUCCESS;
    }
}
