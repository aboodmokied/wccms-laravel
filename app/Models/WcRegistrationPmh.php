<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WcRegistrationPmh extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_registration_pmh_tb';

    protected $primaryKey = 'WC_RPMH_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_RPMH_CODE',
        'WC_RPMH_REGISTRATION_CD',
        'WC_RPMH_PMH_CD',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(WcRegistration::class, 'WC_RPMH_REGISTRATION_CD', 'WC_REG_CODE');
    }

    public function pmh(): BelongsTo
    {
        return $this->belongsTo(WcPmh::class, 'WC_RPMH_PMH_CD', 'WC_PMH_CODE');
    }
}
