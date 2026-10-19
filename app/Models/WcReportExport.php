<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One export of the monthly case reports (spec 013, FR-013). Insert-only.
 */
class WcReportExport extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_report_export_tb';

    protected $primaryKey = 'WC_RPEX_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_RPEX_USER_CD',
        'WC_RPEX_EXPORTED_AT',
        'WC_RPEX_PERIOD_FROM',
        'WC_RPEX_PERIOD_TO',
        'WC_RPEX_DEPARTMENT_CODES',
        'WC_RPEX_CASE_COUNT',
    ];

    protected function casts(): array
    {
        return [
            'WC_RPEX_EXPORTED_AT' => 'datetime',
            'WC_RPEX_PERIOD_FROM' => 'date',
            'WC_RPEX_PERIOD_TO' => 'date',
            'WC_RPEX_DEPARTMENT_CODES' => 'array',
            'WC_RPEX_CASE_COUNT' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'WC_RPEX_USER_CD', 'PER_USER_CODE');
    }
}
