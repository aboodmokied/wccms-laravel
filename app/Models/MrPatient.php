<?php

namespace App\Models;

use App\Models\Concerns\HospitalTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A patient of the hospital system: HOS.MR_PATIENT_TB, key (MRP_PATREC_CODE, HOS_NO). Read only.
 */
class MrPatient extends Model
{
    use HasFactory, HospitalTable;

    public const HOSPITAL_TABLE = 'patients';

    /**
     * The four Arabic name parts, in display order (same order as the hospital view INPATIENT_VW).
     */
    public const NAME_COLUMNS = ['MRP_FIRST_NAME_AR', 'MRP_FATHER_NAME_AR', 'MRP_GRANDFATHER_NAME_AR', 'MRP_LAST_NAME_AR'];

    protected $primaryKey = 'MRP_PATREC_CODE';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'MRP_PATREC_CODE' => 'integer',
            'MRP_DOB' => 'date',
        ];
    }

    /**
     * Full Arabic name joined from the four name columns.
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => collect(self::NAME_COLUMNS)
            ->map(fn (string $column) => trim((string) $this->{$column}))
            ->filter()
            ->implode(' '));
    }

    /**
     * Arabic sex label from C_SEX_TB; null when the code is unknown.
     */
    protected function sexLabel(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->sex?->SEX_NAME_AR);
    }

    /**
     * MALE, FEMALE or UNKNOWN, from C_SEX_TB.SEX_NAME_EN (see config hospital.sex).
     */
    protected function sexClass(): Attribute
    {
        return Attribute::get(fn (): string => $this->sex?->classification() ?? SexLookup::UNKNOWN);
    }

    /**
     * Ward of the current hospital stay, falling back to the ward the patient was admitted to.
     */
    protected function hospitalWardName(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->currentAdmission?->currentStay?->ward?->LOC_NAME_AR
            ?? $this->currentAdmission?->location?->LOC_NAME_AR);
    }

    /**
     * Patients whose name or number matches the search text.
     *
     * Each word must match one of the four name columns (each column searched on its own, so no database-specific
     * concatenation). A search made only of digits also matches the document number (MRP_ID) or the medical record
     * number (MRP_PATREC_CODE); both are NUMBER columns, so they are compared only with digits (never ORA-01722).
     */
    public function scopeMatching(Builder $query, ?string $search): void
    {
        $search = trim((string) $search);
        if ($search === '') {
            return;
        }

        $words = preg_split('/\s+/u', $search) ?: [];

        $query->where(function (Builder $query) use ($search, $words): void {
            $query->where(function (Builder $query) use ($words): void {
                foreach ($words as $word) {
                    $query->where(function (Builder $query) use ($word): void {
                        foreach (self::NAME_COLUMNS as $column) {
                            $query->orWhere($column, 'like', "%{$word}%");
                        }
                    });
                }
            });

            if (ctype_digit($search) && strlen($search) <= 9) {
                $query->orWhere('MRP_ID', (int) $search)
                    ->orWhere('MRP_PATREC_CODE', (int) $search);
            }
        });
    }

    /**
     * Exact match on the document number or the medical record number; only digits are compared.
     */
    public function scopeByNumber(Builder $query, ?string $number): void
    {
        $number = trim((string) $number);

        if (! ctype_digit($number) || strlen($number) > 9) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(fn (Builder $query) => $query
            ->where('MRP_ID', (int) $number)
            ->orWhere('MRP_PATREC_CODE', (int) $number));
    }

    public function scopeOrderedByName(Builder $query): void
    {
        foreach (self::NAME_COLUMNS as $column) {
            $query->orderBy($column);
        }
    }

    public function sex(): BelongsTo
    {
        return $this->belongsTo(SexLookup::class, 'MRP_SEX_CD', 'SEX_CODE');
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(AdmissionPatient::class, 'ADMISSION_PATREC_CD', 'MRP_PATREC_CODE');
    }

    /**
     * The admission the patient is in hospital for now: no ADMISSION_OUT_DATE and a stay that is not cancelled
     * (same rule as HOS.INPATIENT_VW). The latest one wins when there are several.
     */
    public function currentAdmission(): HasOne
    {
        return $this->hasOne(AdmissionPatient::class, 'ADMISSION_PATREC_CD', 'MRP_PATREC_CODE')
            ->ofMany(
                ['ADMISSION_ENTER_DATE' => 'max', 'ADMISSION_CODE' => 'max'],
                fn (Builder $query) => $query->open()
            );
    }

    public function requests(): HasMany
    {
        return $this->hasMany(WcRequestReg::class, 'WC_REQ_PATIENT_CD', 'MRP_PATREC_CODE');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(WcRegistration::class, 'WC_REG_PATIENT_CD', 'MRP_PATREC_CODE');
    }
}
