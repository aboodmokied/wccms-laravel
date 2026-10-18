# Hospital Form Field Matrix

This matrix is the acceptance reference for FR-035. “Entry location” names the owning workflow; “Display location” names where the saved value must remain visible. Final Arabic wording requires clinical-owner approval, but stable keys and ownership do not change with label edits.

## Shared Patient and Encounter Context

| Source field | Owner | Meaning / allowed values | Entry location | Display location |
|---|---|---|---|---|
| Patient name | Patient | Existing patient full name | Read-only hospital/patient record | Initial form header, review header, registration and patient history |
| Date of birth | Patient | Existing date or not recorded | Read-only hospital/patient record | Initial form header and patient demographics |
| Gender | Patient | Existing patient gender | Read-only hospital/patient record | Initial/review header and patient demographics |
| Ward / department | Registration | Active department reference | Initial request acceptance | Registration header and history |
| Registration date | Registration | System completion timestamp | System generated | Registration header and history |
| Visit date/time | Clinical visit | System completion timestamp | System generated on daily/scheduled completion | Review timeline and patient history |
| Nurse | Assessment | Required clinical employee | Initial and follow-up documentation | Every assessment audit block |
| Wound Care Team participant | Assessment | Optional clinical employee | Initial and follow-up documentation | Assessment audit block when supplied |
| Doctor | Assessment | Optional clinical employee | Initial and follow-up documentation | Assessment audit block when supplied |
| Data-entry user | Assessment | Authenticated user | System generated | Every assessment audit block |
| Allergies | Registration | `No`, or `Yes` with required description | Initial request acceptance, once | Registration summary and every diagnosis context |
| Comorbidities | Registration | Multi-select active PMH rows, or mutually exclusive `None` | Initial request acceptance, once | Registration summary and every diagnosis context |
| Shared next visit | Registration | Future/today date or frequency | Initial or follow-up shared plan | Appointments and registration history |
| Shared objective | Registration | Required narrative when next date/frequency exists | Initial or follow-up shared plan | Appointments and registration history |

## Initial Assessment Per Diagnosis

| Source field | Owner | Meaning / allowed values | Entry location | Display location |
|---|---|---|---|---|
| Diagnosis | Registration diagnosis | Active diagnosis reference | Diagnosis panel | Wound heading and all history events |
| Wound type | Registration diagnosis | Active wound-type reference: hospital form includes pressure, surgical, puncture, and crush categories when configured | Diagnosis panel | Wound heading and baseline |
| Wound status | Assessment | `FIRST_VISIT` / زيارة أولى for baseline | Initial assessment panel | Baseline and comparison timeline |
| Exudate amount | Assessment | `DRY` جاف, `LOW` قليل, `MEDIUM` متوسط, `HIGH` غزير | Initial assessment panel | Baseline and comparison timeline |
| Exudate type | Assessment | `WATERY` مائي, `CLOUDY` عكر, `PURULENT` صديدي, `CLEAR` صافي; empty only when dry | Initial assessment panel | Baseline and comparison timeline |
| Odor | Assessment | `NONE` لا يوجد, `SLIGHT` خفيف, `MODERATE` متوسط, `OFFENSIVE` كريه/شديد | Initial assessment panel | Baseline and comparison timeline |
| Pain severity | Assessment | `NONE` لا يوجد, `MODERATE` متوسط, `SEVERE` شديد | Initial assessment panel | Baseline and comparison timeline |
| Pain at dressing | Assessment | Yes/no independent of severity | Initial assessment panel | Baseline and comparison timeline |
| Fever | Assessment | `NONE` لا توجد, `LOW` منخفضة, `HIGH` مرتفعة | Initial assessment panel | Baseline |
| Wound bed | Assessment findings | Multi-select `GRANULATION` نسيج حبيبي, `NECROTIC` نسيج نخري, `SLOUGHING` نسيج متسلخ/رخو, `EPITHELIALIZING` تكوّن ظهارة | Initial assessment panel | Baseline and comparison timeline |
| Wound margin | Assessment findings | Multi-select `NONE_OBSERVED` لا توجد علامة مسجلة, `ERYTHEMA` احمرار, `EDEMA` وذمة, `DELAYED_HEALING` تأخر الالتئام, `POCKETING` جيوب؛ `NONE_OBSERVED` is exclusive | Initial assessment panel | Baseline and comparison timeline |
| Length | Assessment | Decimal millimetres, `>0` and `<=1000`, maximum two decimals | Initial assessment panel | Baseline measurements |
| Width | Assessment | Decimal millimetres, `>0` and `<=1000`, maximum two decimals | Initial assessment panel | Baseline measurements |
| Depth | Assessment | Decimal millimetres, `>=0` and `<=1000`, maximum two decimals | Initial assessment panel | Baseline measurements |
| Anatomical location | Assessment | Head, abdomen, back, leg, buttocks, foot, hand, forearm, or other | Initial assessment panel | Baseline and wound identity summary |
| Laterality | Assessment | Not applicable, left, right, or bilateral | Initial assessment panel | Baseline and wound identity summary |
| Precise location | Assessment | Optional text; required when location is other | Initial assessment panel | Baseline and wound identity summary |
| Swab taken | Assessment | Yes/no; no laboratory result tracking | Initial assessment panel | Baseline |
| Nutrition | Assessment | `WELL_NOURISHED` جيد التغذية or `MALNOURISHED` سوء تغذية | Initial assessment panel | Baseline |
| Dressing | Treatment | Active dressing reference | Initial assessment panel | Baseline treatment |
| Medication | Treatment | Active medication reference | Initial assessment panel | Baseline treatment |
| Procedure | Treatment | Active procedure reference; debridement belongs here | Initial assessment panel | Baseline treatment |
| Treatment notes | Treatment | Optional narrative; at least one treatment value is required | Initial assessment panel | Baseline treatment |
| Baseline photos | Wound photo | Zero or more valid image files attached to this assessment and diagnosis | Initial assessment panel | Wound baseline and photo history |
| Signature | Assessment audit | Nurse identity plus completion timestamp; no handwritten artifact | System generated at completion | Baseline audit block |

## Daily and Scheduled Follow-Up Per Diagnosis

Daily and fulfilled scheduled reviews use the same field ownership and validation.

| Source field | Owner | Meaning / allowed values | Entry location | Display location |
|---|---|---|---|---|
| Wound identity | Registration diagnosis | Fixed diagnosis and wound type from registration; not user-reassignable | Read-only diagnosis panel heading | Review and history heading |
| Wound status | Assessment | `DETERIORATING` متدهور, `STATIC` ثابت, `IMPROVING` يتحسن | Follow-up diagnosis panel | Review and comparison timeline |
| Wound bed | Assessment findings | Same multi-select bed vocabulary as initial assessment | Follow-up diagnosis panel | Review and comparison timeline |
| Wound margin | Assessment findings | Optional follow-up observation using the same margin vocabulary; pocketing remains a margin finding | Follow-up diagnosis panel | Review and comparison timeline |
| Exudate amount | Assessment | Dry, low, medium, or high | Follow-up diagnosis panel | Review and comparison timeline |
| Exudate type | Assessment | Watery, cloudy, purulent, or clear; forbidden for dry | Follow-up diagnosis panel | Review and comparison timeline |
| Odor | Assessment | None, slight, moderate, or offensive | Follow-up diagnosis panel | Review and comparison timeline |
| Pain severity | Assessment | None, moderate, or severe | Follow-up diagnosis panel | Review and comparison timeline |
| Pain at dressing changes | Assessment | Yes/no independent of pain severity | Follow-up diagnosis panel | Review and comparison timeline |
| Measurements | Assessment | Optional length/width/depth using initial measurement constraints | Follow-up diagnosis panel | Review comparison when recorded; otherwise “لم يُسجل” |
| Dressing | Treatment | Active dressing reference; hospital gauze, foam, antimicrobial, or other configured choices | Follow-up diagnosis panel | Review treatment |
| Procedure | Treatment | Active procedure reference; debridement belongs here | Follow-up diagnosis panel | Review treatment |
| Medication | Treatment | Active medication reference | Follow-up diagnosis panel | Review treatment |
| Clinical observation | Assessment and legacy detail snapshot | Narrative observation | Follow-up diagnosis panel | Review detail and history |
| Change since prior assessment | Assessment and legacy detail snapshot | Narrative comparison | Follow-up diagnosis panel | Review detail and history |
| Existing outcome | Legacy review detail | Required existing compatible result status; `WC_REVIEW_DETAIL_TB.WC_DREVD_STATUS` is authoritative and the linked canonical assessment does not duplicate it | Follow-up diagnosis panel | Structured assessment summary, review status, and existing reports |
| Follow-up required | Assessment and legacy detail snapshot | Yes/no | Follow-up diagnosis panel | Review detail and shared planning context |
| Review photos | Wound photo | Zero or more valid image files attached to this assessment, detail, and diagnosis | Follow-up diagnosis panel | Review and photo history |
| Nurse signature | Assessment audit | Nurse identity plus completion timestamp | System generated at completion | Review audit block |

## Source-to-System Decisions

- The Excel form’s one-sheet-per-diagnosis structure becomes one repeated diagnosis panel under a shared registration header.
- The daily Word/Excel form becomes one shared visit header plus one panel for every registration diagnosis.
- “Debridement + dressing” is split into a procedure and dressing so each clinical action has one meaning.
- “At dressing” is stored as a boolean independent of pain severity.
- Pocketing is stored under wound-margin findings even where a supplied layout visually places it beside wound-bed options.
- Scheduled follow-ups reuse the daily contract; the appointment changes the visit source, not its clinical data set.
- Historical narrative is displayed verbatim; structured values are never inferred from it.
