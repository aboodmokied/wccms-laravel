<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class WcScheduledReview extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_scheduled_review_tb';

    protected $primaryKey = 'WC_SREV_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_SREV_CODE',
        'WC_SREV_REGISTRATION_CD',
        'WC_SREV_SCHEDULED_DATE',
        'WC_SREV_VISIT_SERIAL',
        'WC_SREV_STATUS',
    ];

    public function registration()
    {
        return $this->belongsTo(WcRegistration::class, 'WC_SREV_REGISTRATION_CD', 'WC_REG_CODE');
    }

    public function dailyReview()
    {
        return $this->hasOne(WcDailyReview::class, 'WC_DREV_SCHEDULED_REVIEW_CD', 'WC_SREV_CODE');
    }
}
