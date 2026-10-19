# Data Model: Clinical Terms in English

## Changed table: `wc_assessment_choice_tb`

| Column | Type | Rules |
|---|---|---|
| `WC_ACH_LABEL_EN` | `VARCHAR(150)` | Added nullable; the migration fills every seeded `(WC_ACH_CATEGORY, WC_ACH_KEY)` from [contracts/clinical-glossary.md](contracts/clinical-glossary.md); required (max 150) when an administrator saves a choice |

- `WC_ACH_LABEL_AR` is unchanged and kept.
- `down()` drops the column (no other data is touched).
- Model `WcAssessmentChoice`: add `WC_ACH_LABEL_EN` to `$fillable`; `displayLabel(): string` returns the English name,
  else the key in words (`DELAYED_HEALING` → `Delayed Healing`).

## Glossary: `lang/ar/clinical.php` (not stored)

```text
fields.<name>            medical field labels (contracts/clinical-glossary.md "Form field labels")
overall_outcomes.<CODE>  WC_DREV_OVERALL_STATUS display names
wound_outcomes.<CODE>    WC_DREVD_STATUS display names
case_outcomes.<CODE>     monthly report Comment values
genders.<MALE|FEMALE|UNSPECIFIED>
```

An unknown outcome code falls back to the code itself.
