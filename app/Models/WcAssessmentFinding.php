<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WcAssessmentFinding extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_assessment_finding_tb';

    protected $primaryKey = 'WC_AF_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_AF_CODE',
        'WC_AF_ASSESSMENT_CD',
        'WC_AF_CHOICE_CD',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(WcWoundAssessment::class, 'WC_AF_ASSESSMENT_CD', 'WC_WA_CODE');
    }

    public function choice(): BelongsTo
    {
        return $this->belongsTo(WcAssessmentChoice::class, 'WC_AF_CHOICE_CD', 'WC_ACH_CODE');
    }
}
