<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WcAssessmentChoice extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_assessment_choice_tb';

    protected $primaryKey = 'WC_ACH_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_ACH_CODE',
        'WC_ACH_CATEGORY',
        'WC_ACH_KEY',
        'WC_ACH_LABEL_AR',
        'WC_ACH_LABEL_EN',
        'WC_ACH_IS_ACTIVE',
        'WC_ACH_DISPLAY_ORDER',
    ];

    protected function casts(): array
    {
        return [
            'WC_ACH_IS_ACTIVE' => 'boolean',
            'WC_ACH_DISPLAY_ORDER' => 'integer',
        ];
    }

    /**
     * The name shown on every page: the English name (medical terms are not translated, spec 014), else the stable
     * key in words for a choice that has no English name yet.
     */
    public function displayLabel(): string
    {
        $english = trim((string) $this->WC_ACH_LABEL_EN);

        return $english !== '' ? $english : Str::of((string) $this->WC_ACH_KEY)->lower()->replace('_', ' ')->title()->toString();
    }

    public function findings(): HasMany
    {
        return $this->hasMany(WcAssessmentFinding::class, 'WC_AF_CHOICE_CD', 'WC_ACH_CODE');
    }

    public function scopeActiveInCategory(Builder $query, string $category): Builder
    {
        return $query->where('WC_ACH_CATEGORY', $category)
            ->where('WC_ACH_IS_ACTIVE', true)
            ->orderBy('WC_ACH_DISPLAY_ORDER')
            ->orderBy('WC_ACH_LABEL_AR');
    }
}
