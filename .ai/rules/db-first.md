# Database-First Architecture Rules & Conventions

## Overview
This application follows a **Database-First (DB-First)** approach. The database schema in MySQL (`wccms`) is the primary source of truth. Models must map directly to pre-existing `*_tb` tables rather than assuming default Laravel conventions.

## Table & Model Mapping Conventions

1. **Table Names (`$table`)**:
   - All legacy domain tables end with `_tb`.
   - Examples: `mr_patient_tb`, `per_employee_tb`, `per_users_tb`, `wc_registration_tb`, `wc_department_tb`, `wc_daily_review_tb`, `wc_daily_review_detail_tb`, `wc_diagnosis_tb`, `wc_dressing_tb`, `wc_medication_tb`, `wc_pmh_tb`, `wc_procedure_tb`, `wc_reg_diagnosis_tb`, `wc_request_reg_tb`, `wc_scheduled_review_tb`, `wc_wound_type_tb`.

2. **Primary Keys (`$primaryKey`, `$keyType`, `$incrementing`)**:
   - Primary keys use custom UPPERCASE column names (e.g., `MR_PAT_CODE`, `PER_EMP_CODE`, `PER_USER_CODE`, `WC_REG_CODE`, `WC_DEPT_CODE`).
   - Primary keys are `char(36)` UUID strings.
   - All DB-First Eloquent Models MUST specify:
     ```php
     protected $primaryKey = 'PREFIX_CODE';
     public $incrementing = false;
     protected $keyType = 'string';
     ```

3. **Timestamps (`$timestamps`)**:
   - Pre-existing `_tb` tables do NOT contain default Laravel `created_at` and `updated_at` columns.
   - All DB-First Eloquent Models MUST specify:
     ```php
     public $timestamps = false;
     ```

4. **Column Names**:
   - Columns in existing `_tb` tables use UPPERCASE prefixes (e.g. `WC_REG_PATIENT_CD`, `PER_USER_USERNAME`, `MR_PAT_FULL_NAME`).
   - Eloquent Model `$fillable` arrays or relationship foreign keys MUST match exact database column names.

5. **Schema Reference**:
   - **`mr_patient_tb`**: `MR_PAT_CODE` (PK)
   - **`per_employee_tb`**: `PER_EMP_CODE` (PK), foreign key `PER_EMP_USER_CD`
   - **`per_users_tb`**: `PER_USER_CODE` (PK)
   - **`wc_registration_tb`**: `WC_REG_CODE` (PK), foreign key `WC_REG_PATIENT_CD`, `WC_REG_NURSE_CD`, `WC_REG_DEPARTMENT_CD`, `WC_REG_REQUEST_CD`, etc.
   - **`wc_department_tb`**: `WC_DEPT_CODE` (PK)
   - **`wc_daily_review_tb`**: `WC_DREV_CODE` (PK), foreign key `WC_DREV_REGISTRATION_CD`
   - **`wc_daily_review_detail_tb`**: `WC_DREVD_CODE` (PK), foreign key `WC_DREVD_DAILY_REVIEW_CD`
   - **`wc_diagnosis_tb`**: `WC_DIAG_CODE` (PK)
   - **`wc_dressing_tb`**: `WC_DRESS_CODE` (PK)
   - **`wc_medication_tb`**: `WC_MED_CODE` (PK)
   - **`wc_pmh_tb`**: `WC_PMH_CODE` (PK)
   - **`wc_procedure_tb`**: `WC_PROC_CODE` (PK)
   - **`wc_reg_diagnosis_tb`**: `WC_RDIAG_CODE` (PK)
   - **`wc_request_reg_tb`**: `WC_REQ_CODE` (PK)
   - **`wc_scheduled_review_tb`**: `WC_SREV_CODE` (PK)
   - **`wc_wound_type_tb`**: `WC_WTYPE_CODE` (PK)
