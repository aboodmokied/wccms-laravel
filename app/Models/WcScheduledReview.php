<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Builder;
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
        'WC_SREV_CREATED_BY_CD',
        'WC_SREV_SCHEDULED_DATE',
        'WC_SREV_VISIT_SERIAL',
        'WC_SREV_STATUS',
        'WC_SREV_NOTES',
        'WC_SREV_CREATED_ON',
        'WC_SREV_COMPLETED_ON',
    ];

    /**
     * A schedule has no HOS_NO of its own: it belongs to the hospital of its registration, so it is read through
     * the registration's hospital filter.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('hospital', fn (Builder $query) => $query->whereHas('registration'));
    }

    public function registration()
    {
        return $this->belongsTo(WcRegistration::class, 'WC_SREV_REGISTRATION_CD', 'WC_REG_CODE');
    }

    public function createdBy()
    {
        return $this->belongsTo(PerUser::class, 'WC_SREV_CREATED_BY_CD', 'PER_USER_CODE');
    }

    public function dailyReview()
    {
        return $this->hasOne(WcDailyReview::class, 'WC_DREV_SCHEDULED_REVIEW_CD', 'WC_SREV_CODE');
    }
}
