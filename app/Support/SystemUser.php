<?php

namespace App\Support;

use App\Models\User;

/**
 * WCCMS has no sign-in: the hospital decides who can reach it. Every row that records "entered by"
 * (WC_*_ENTRY_USER_CD, WC_SREV_CREATED_BY_CD, WC_RPEX_USER_CD -> WC_USERS_TB) points to this one system account.
 * Who did the clinical work is recorded separately, by the nurse, doctor and wound-team employees chosen on each form.
 */
class SystemUser
{
    public const CODE = '00000000-0000-0000-0000-000000000001';

    public const USERNAME = 'WCCMS_SYSTEM';

    /**
     * PER_USER_CODE of the system account, creating its WC_USERS_TB row if it is not there yet. Called only when a
     * row is written, so the extra lookup is one indexed read per save.
     */
    public static function code(): string
    {
        User::query()->firstOrCreate(
            ['PER_USER_CODE' => self::CODE],
            [
                'PER_USER_USERNAME' => self::USERNAME,
                'PER_USER_ROLE' => null,
                'PER_USER_IS_ACTIVE' => true,
            ],
        );

        return self::CODE;
    }
}
