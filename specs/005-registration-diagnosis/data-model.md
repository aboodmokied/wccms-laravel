# Data Model & Schema Specification: Phase 5 — Registration & Diagnosis

**Feature Branch**: `005-registration-diagnosis`  
**Date**: 2026-09-26  

---

## DB-First Eloquent Entity Mappings

### 1. Registration Model (`WcRegistration` → `wc_registration_tb`)

Maps clinical registration records created upon referral request acceptance.

```php
// Table: wc_registration_tb
// PK: WC_REG_CODE (char 36, UUID)
// Timestamps: false
// Incrementing: false

protected $table = 'wc_registration_tb';
protected $primaryKey = 'WC_REG_CODE';
public $incrementing = false;
protected $keyType = 'string';
public $timestamps = false;

protected $fillable = [
    'WC_REG_CODE',
    'WC_REG_REQUEST_CD',
    'WC_REG_PATIENT_CD',
    'WC_REG_DEPARTMENT_CD',
    'WC_REG_PROCEDURE_CD',
    'WC_REG_PMH_NOTES',
    'WC_REG_DATE',
    'WC_REG_NURSE_CD',
    'WC_REG_ENTRY_USER_CD',
    'WC_REG_STATUS',               // ACTIVE, DISCHARGED
    'WC_REG_DISCHARGED_AT',
    'WC_REG_DISCHARGED_BY_EMP_CD',
];
```

#### Eloquent Relationships
- `patient()`: `belongsTo(MrPatient::class, 'WC_REG_PATIENT_CD', 'MR_PAT_CODE')`
- `department()`: `belongsTo(WcDepartment::class, 'WC_REG_DEPARTMENT_CD', 'WC_DEPT_CODE')`
- `procedure()`: `belongsTo(WcProcedure::class, 'WC_REG_PROCEDURE_CD', 'WC_PROC_CODE')`
- `registeringNurse()`: `belongsTo(PerEmployee::class, 'WC_REG_NURSE_CD', 'PER_EMP_CODE')`
- `entryUser()`: `belongsTo(User::class, 'WC_REG_ENTRY_USER_CD', 'PER_USER_CODE')`
- `dischargingNurse()`: `belongsTo(PerEmployee::class, 'WC_REG_DISCHARGED_BY_EMP_CD', 'PER_EMP_CODE')`
- `request()`: `belongsTo(WcRequestReg::class, 'WC_REG_REQUEST_CD', 'WC_REQ_CODE')`
- `diagnoses()`: `hasMany(WcRegDiagnosis::class, 'WC_RDIAG_REGISTRATION_CD', 'WC_REG_CODE')`

---

### 2. Registration Diagnosis / Wound Model (`WcRegDiagnosis` → `wc_reg_diagnosis_tb`)

Maps individual initial wound diagnoses associated with a clinical registration.

```php
// Table: wc_reg_diagnosis_tb
// PK: WC_RDIAG_CODE (char 36, UUID)
// Timestamps: false
// Incrementing: false

protected $table = 'wc_reg_diagnosis_tb';
protected $primaryKey = 'WC_RDIAG_CODE';
public $incrementing = false;
protected $keyType = 'string';
public $timestamps = false;

protected $fillable = [
    'WC_RDIAG_CODE',
    'WC_RDIAG_REGISTRATION_CD',
    'WC_RDIAG_DIAGNOSIS_CD',
    'WC_RDIAG_WOUND_TYPE_CD',
    'WC_RDIAG_CLINICAL_EMP_CD',
    'WC_RDIAG_ENTRY_USER_CD',
];
```

#### Eloquent Relationships
- `registration()`: `belongsTo(WcRegistration::class, 'WC_RDIAG_REGISTRATION_CD', 'WC_REG_CODE')`
- `diagnosis()`: `belongsTo(WcDiagnosis::class, 'WC_RDIAG_DIAGNOSIS_CD', 'WC_DIAG_CODE')`
- `woundType()`: `belongsTo(WcWoundType::class, 'WC_RDIAG_WOUND_TYPE_CD', 'WC_WTYPE_CODE')`
- `clinicalEmployee()`: `belongsTo(PerEmployee::class, 'WC_RDIAG_CLINICAL_EMP_CD', 'PER_EMP_CODE')`
- `entryUser()`: `belongsTo(User::class, 'WC_RDIAG_ENTRY_USER_CD', 'PER_USER_CODE')`

---

## State Transition Diagram

```text
       ┌────────────────────────┐
       │   Referral Accepted    │
       └───────────┬────────────┘
                   │
                   ▼
       ┌────────────────────────┐
       │    WC_REG_STATUS       │
       │        ACTIVE          │
       └───────────┬────────────┘
                   │
         [Discharge Triggered]
                   │
                   ▼
       ┌────────────────────────┐
       │    WC_REG_STATUS       │
       │      DISCHARGED        │
       └────────────────────────┘
```
