# Contract: Workbook Value Lists → System Lists

Source: the `Tables` sheet of the clinic workbook, plus values used in `CASES`. **Review before seeding.**
Existing demo values are never removed or deactivated. "Skip" means the value is not seeded and why.

## Diagnoses (`wc_diagnosis_tb`)

`DF`, `bedsore PEU`, `bedsore HAPU`, `SSI`, `Burn`, `Inf Wound`, `Row Area`

## Wound types (`wc_wound_type_tb`)

| Workbook value | Target |
|---|---|
| `Pressure`, `Surgical`, `Puncture`, `Crush`, `Avulsion`, `Abrasion`, `Laceration`, `blister` | wound type |
| `Sloughing` | skip — wound-bed choice `SLOUGHING` |
| `Necrotic` | skip — wound-bed choice `NECROTIC` |
| `black tissue` | skip — wound-bed choice `NECROTIC` (black necrotic tissue / eschar) |
| `Granulation` | skip — wound-bed choice `GRANULATION` |
| `Pocketing` | skip — wound-margin choice `POCKETING` |

## Dressings (`wc_dressing_tb`)

`Gauze DSG`, `Foam DSG`, `Antimicrobial DSG`, `hydrogel`, `hydrocoill`, `vaslin gauze`, `wet polidine`

## Medications (`wc_medication_tb`)

`Fucidin cream`, `Silvano Spray`, `Dr wound`, `Silverol cream`, `Burno`, `Calmex`, `Polydine ointment`, `Hylorin`,
`Betacorten`, `Chlorhexidine Gel`, `Bactazon`, `Sodeem`, `Zaha`, `Dexeryl`, `Iuroxol`, `mebo`

- `NO`: skip — means no medication.

## Procedures (`wc_procedure_tb`)

| Workbook value | Target |
|---|---|
| `debridement` | procedure |
| `Depredment` | skip — misspelling of `debridement` |
| `swap culture`, `Swape Culture` | skip — recorded as the assessment's swab field; the report prints it as `swap culture` |
| `swap culture, debridement` | skip — a combination, produced by the report |
| `NO`, `YES`, `No` | skip — flags, not procedures |

## Departments (`wc_department_tb`)

`Medical Male`, `Medical Female`, `CCU`, `ICU`, `Surgical Male`, `Surgical Female`, `Special Male`, `Special Female`,
`Orthopedic Male`, `Orthopedic Female`, `PED ICU`, `SPECIAL MALE B`, `ONCOLOGY`

## Past medical history (`wc_pmh_tb`)

`DM`, `HTN`, `Vascular Disease`, `Immune Disease`

- `No`: skip — recorded as the registration's no-comorbidities flag.

## Not seeded

Doctor specialties (`DR` column) — deferred (clarification Q4).
