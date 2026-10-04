# Phase 1: Data Model Specification — Master Data Administration

**Feature Branch**: `008-master-data-admin`  
**Date**: 2026-10-04  
**Spec**: [spec.md](./spec.md)

---

## 1. Entities & Schema Definitions

All 7 master data entities map to pre-existing MySQL `_tb` tables following the Database-First Architecture.

### 1. Departments (`WcDepartment`)
- **Table**: `wc_department_tb`
- **Primary Key**: `WC_DEPT_CODE` (CHAR 36 UUID)
- **Columns**:
  - `WC_DEPT_CODE` (PK, string, non-incrementing)
  - `WC_DEPT_NAME` (string, max 255, required, unique per table)
  - `WC_DEPT_IS_ACTIVE` (smallint/tinyint, default 1)
- **Validation Rules**:
  - Store: `name => required|string|max:255|unique:wc_department_tb,WC_DEPT_NAME`
  - Update: `name => required|string|max:255|unique:wc_department_tb,WC_DEPT_NAME,{code},WC_DEPT_CODE`

### 2. Past Medical History (`WcPmh`)
- **Table**: `wc_pmh_tb`
- **Primary Key**: `WC_PMH_CODE` (CHAR 36 UUID)
- **Columns**:
  - `WC_PMH_CODE` (PK, string, non-incrementing)
  - `WC_PMH_NAME` (string, max 255, required, unique per table)
  - `WC_PMH_IS_ACTIVE` (smallint/tinyint, default 1)
- **Validation Rules**:
  - Store: `name => required|string|max:255|unique:wc_pmh_tb,WC_PMH_NAME`
  - Update: `name => required|string|max:255|unique:wc_pmh_tb,WC_PMH_NAME,{code},WC_PMH_CODE`

### 3. Diagnoses (`WcDiagnosis`)
- **Table**: `wc_diagnosis_tb`
- **Primary Key**: `WC_DIAG_CODE` (CHAR 36 UUID)
- **Columns**:
  - `WC_DIAG_CODE` (PK, string, non-incrementing)
  - `WC_DIAG_NAME` (string, max 255, required, unique per table)
  - `WC_DIAG_IS_ACTIVE` (smallint/tinyint, default 1)
- **Validation Rules**:
  - Store: `name => required|string|max:255|unique:wc_diagnosis_tb,WC_DIAG_NAME`
  - Update: `name => required|string|max:255|unique:wc_diagnosis_tb,WC_DIAG_NAME,{code},WC_DIAG_CODE`

### 4. Wound Types (`WcWoundType`)
- **Table**: `wc_wound_type_tb`
- **Primary Key**: `WC_WTYPE_CODE` (CHAR 36 UUID)
- **Columns**:
  - `WC_WTYPE_CODE` (PK, string, non-incrementing)
  - `WC_WTYPE_NAME` (string, max 255, required, unique per table)
  - `WC_WTYPE_IS_ACTIVE` (smallint/tinyint, default 1)
- **Validation Rules**:
  - Store: `name => required|string|max:255|unique:wc_wound_type_tb,WC_WTYPE_NAME`
  - Update: `name => required|string|max:255|unique:wc_wound_type_tb,WC_WTYPE_NAME,{code},WC_WTYPE_CODE`

### 5. Dressings (`WcDressing`)
- **Table**: `wc_dressing_tb`
- **Primary Key**: `WC_DRESS_CODE` (CHAR 36 UUID)
- **Columns**:
  - `WC_DRESS_CODE` (PK, string, non-incrementing)
  - `WC_DRESS_NAME` (string, max 255, required, unique per table)
  - `WC_DRESS_IS_ACTIVE` (smallint/tinyint, default 1)
- **Validation Rules**:
  - Store: `name => required|string|max:255|unique:wc_dressing_tb,WC_DRESS_NAME`
  - Update: `name => required|string|max:255|unique:wc_dressing_tb,WC_DRESS_NAME,{code},WC_DRESS_CODE`

### 6. Medications (`WcMedication`)
- **Table**: `wc_medication_tb`
- **Primary Key**: `WC_MED_CODE` (CHAR 36 UUID)
- **Columns**:
  - `WC_MED_CODE` (PK, string, non-incrementing)
  - `WC_MED_NAME` (string, max 255, required, unique per table)
  - `WC_MED_IS_ACTIVE` (smallint/tinyint, default 1)
- **Validation Rules**:
  - Store: `name => required|string|max:255|unique:wc_medication_tb,WC_MED_NAME`
  - Update: `name => required|string|max:255|unique:wc_medication_tb,WC_MED_NAME,{code},WC_MED_CODE`

### 7. Procedures (`WcProcedure`)
- **Table**: `wc_procedure_tb`
- **Primary Key**: `WC_PROC_CODE` (CHAR 36 UUID)
- **Columns**:
  - `WC_PROC_CODE` (PK, string, non-incrementing)
  - `WC_PROC_NAME` (string, max 255, required, unique per table)
  - `WC_PROC_IS_ACTIVE` (smallint/tinyint, default 1)
- **Validation Rules**:
  - Store: `name => required|string|max:255|unique:wc_procedure_tb,WC_PROC_NAME`
  - Update: `name => required|string|max:255|unique:wc_procedure_tb,WC_PROC_NAME,{code},WC_PROC_CODE`

---

## 2. Common Model Configuration Pattern

All master data Eloquent models specify:
```php
public $incrementing = false;
protected $keyType = 'string';
public $timestamps = false;
```

## 3. Active Status Transition Rules

```text
Active (IS_ACTIVE = 1)
   ↓ (Admin toggles Deactivate)
Inactive (IS_ACTIVE = 0)
   ↓ (Admin toggles Reactivate)
Active (IS_ACTIVE = 1)
```

- When `IS_ACTIVE = 1`: Item appears in form selects (`where('IS_ACTIVE', 1)`).
- When `IS_ACTIVE = 0`: Item is hidden from form selects, but historical foreign keys continue to function in views and reports.
