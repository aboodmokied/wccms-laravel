<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WcPmh extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_pmh_tb';

    protected $primaryKey = 'WC_PMH_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_PMH_CODE',
        'WC_PMH_NAME',
        'WC_PMH_IS_ACTIVE',
    ];

    public function registrationLinks(): HasMany
    {
        return $this->hasMany(WcRegistrationPmh::class, 'WC_RPMH_PMH_CD', 'WC_PMH_CODE');
    }

    public function registrations(): BelongsToMany
    {
        return $this->belongsToMany(
            WcRegistration::class,
            'wc_registration_pmh_tb',
            'WC_RPMH_PMH_CD',
            'WC_RPMH_REGISTRATION_CD',
            'WC_PMH_CODE',
            'WC_REG_CODE'
        );
    }
}
