<?php

namespace App\Console\Commands;

use App\Support\HospitalStartupCheck;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('wccms:check-hospital')]
#[Description('Read-only check of HOSPITAL_NO against C_HOSPITAL_TB (H_CURR = 1) and of the C_SEX_TB names; logs every finding')]
class CheckHospital extends Command
{
    public function handle(HospitalStartupCheck $check): int
    {
        foreach ($check->run() as $finding) {
            $finding['level'] === HospitalStartupCheck::WARNING
                ? $this->warn($finding['message'])
                : $this->info($finding['message']);
        }

        // Warnings never stop WCCMS; the command reports them and always succeeds.
        return self::SUCCESS;
    }
}
