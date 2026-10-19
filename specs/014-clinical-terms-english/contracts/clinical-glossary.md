# Contract: Clinical Glossary (English display names)

**Review before implementation.** Left column: what the system shows today. Right column: what it will show.

## Monthly report columns (workbook headers)

`NO`, `Pt Name`, `MONTHS`, `ID`, `Gender`, `DoB`, `Diagnosis`, `Discussion`, `DoA`, `PMH`, `Type of Wound`,
`Type of DSG`, `Medication`, `Procedure`, `Department`, `Picture`, `DISCHARGE`, `Comment`; readmissions add
`Previous Registration` and `Previous Comment`. Sheets: `CASES`, `CASES-A`, `ReAdmission`.

## Form field labels (medical)

| Today | English |
|---|---|
| التشخيص | Diagnosis |
| نوع الجرح | Type of Wound |
| حالة الجرح | Wound Status |
| كمية الإفرازات | Exudate Amount |
| نوع الإفرازات | Exudate Type |
| الرائحة | Odor |
| شدة الألم | Pain Severity |
| ألم عند تغيير الضماد؟ | Pain at Dressing Change? |
| الحرارة | Fever |
| التغذية | Nutrition |
| الموقع | Location |
| الجهة | Laterality |
| تفصيل الموقع | Location Details |
| الطول / العرض / العمق | Length / Width / Depth (mm) |
| سرير الجرح | Wound Bed |
| حواف الجرح | Wound Margins |
| تم أخذ مسحة؟ | Swab Taken? |
| الضماد | Type of DSG |
| الدواء | Medication |
| الإجراء | Procedure |
| الأمراض المصاحبة | PMH |
| ملاحظات التاريخ المرضي | PMH Notes |
| هل توجد حساسية؟ | Allergies? |
| وصف الحساسية | Allergy Description |
| خطة/ملاحظات العلاج | Treatment Plan / Notes |
| الملاحظة السريرية | Clinical Observation |
| تغيّرات الجرح | Wound Changes |
| نتيجة الجرح | Wound Outcome |
| النتيجة الإجمالية | Overall Outcome |

Stay Arabic (administrative): القسم، الممرض السريري، عضو فريق الجروح، الطبيب، تاريخ الزيارة القادمة، تكرار المتابعة،
هدف المتابعة، صور الجرح، ملاحظات عامة.

## Assessment choices (`WC_ACH_LABEL_EN`)

| Category | Key → English |
|---|---|
| WOUND_STATUS | FIRST_VISIT → First Visit · DETERIORATING → Deteriorating · STATIC → Static · IMPROVING → Improving |
| WOUND_BED | GRANULATION → Granulation · NECROTIC → Necrotic · SLOUGHING → Sloughing · EPITHELIALIZING → Epithelializing |
| WOUND_MARGIN | NONE_OBSERVED → None Observed · ERYTHEMA → Erythema · EDEMA → Edema · DELAYED_HEALING → Delayed Healing · POCKETING → Pocketing |
| EXUDATE_AMOUNT | DRY → Dry · LOW → Low · MEDIUM → Moderate · HIGH → High |
| EXUDATE_TYPE | WATERY → Watery · CLOUDY → Cloudy · PURULENT → Purulent · CLEAR → Clear |
| ODOR | NONE → None · SLIGHT → Slight · MODERATE → Moderate · OFFENSIVE → Offensive |
| PAIN_SEVERITY | NONE → None · MODERATE → Moderate · SEVERE → Severe |
| FEVER | NONE → None · LOW → Low-grade · HIGH → High |
| NUTRITION | WELL_NOURISHED → Well Nourished · MALNOURISHED → Malnourished |
| LOCATION | HEAD → Head · ABDOMEN → Abdomen · BACK → Back · LEG → Leg · BUTTOCKS → Buttocks · FOOT → Foot · HAND → Hand · FOREARM → Forearm · OTHER → Other |
| LATERALITY | NOT_APPLICABLE → Not Applicable · LEFT → Left · RIGHT → Right · BILATERAL → Bilateral |

## Overall review outcomes (`WC_DREV_OVERALL_STATUS`)

| Code | Today | English |
|---|---|---|
| IMPROVED_DISCHARGE | تحسن وتخريج | Improved – Discharged |
| IMPROVED_SCHEDULED | تحسن مع موعد متابعة | Improved – Follow-up Scheduled |
| IMPROVED_STAY | تحسن مع استمرار العلاج | Improved – Continuing Treatment |
| WORSENED_SCHEDULED | تدهور مع موعد متابعة | Worsened – Follow-up Scheduled |
| WORSENED_ADMISSION | تدهور وتنويم | Worsened – Admitted |
| WORSENED_STAY | تدهور مع استمرار العلاج | Worsened – Continuing Treatment |
| DEATH | وفاة | Death |

## Per-wound outcomes (`WC_DREVD_STATUS`)

| Code | English |
|---|---|
| IMPROVED_DISCHARGE | Healed |
| IMPROVED_FOLLOWUP | Improved – Follow-up |
| WORSENED | Worsened |
| DECEASED | Deceased |

## Case outcome (monthly report Comment)

ONGOING → Ongoing Treatment · DISCHARGED → Discharged · DEATH → Death · HOSPITAL_TRANSFER → Hospital Transfer

## Gender

MALE / M → Male · FEMALE / F → Female · missing → Unspecified
