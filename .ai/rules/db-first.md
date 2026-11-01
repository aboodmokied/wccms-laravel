# Database-First Architecture Rules & Conventions

## Overview
This application follows a **Database-First (DB-First)** approach. The database schema in MySQL (`wccms`) is the primary source of truth. Models must map directly to pre-existing `*_tb` tables rather than assuming default Laravel conventions.

## Table & Model Mapping Conventions

1. **Table Names (`$table`)**:
   - All legacy domain tables end with `_tb`.
   - Examples: `wc_users_tb`, `wc_registration_tb`, `wc_department_tb`, `wc_daily_review_tb`, `wc_daily_review_detail_tb`, `wc_diagnosis_tb`, `wc_dressing_tb`, `wc_medication_tb`, `wc_pmh_tb`, `wc_procedure_tb`, `wc_reg_diagnosis_tb`, `wc_request_reg_tb`, `wc_scheduled_review_tb`, `wc_wound_type_tb`.

2. **Primary Keys (`$primaryKey`, `$keyType`, `$incrementing`)**:
   - Primary keys use custom UPPERCASE column names (e.g., `PER_USER_CODE`, `WC_REG_CODE`, `WC_DEPT_CODE`).
   - WCCMS primary keys are `char(36)` UUID strings. Hospital tables (below) use integer codes.
   - All WCCMS DB-First Eloquent Models MUST specify:
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
   - Columns in existing `_tb` tables use UPPERCASE prefixes (e.g. `WC_REG_PATIENT_CD`, `PER_USER_USERNAME`, `MRP_FIRST_NAME_AR`).
   - Identifiers (tables, columns, indexes, constraints, procedures) are at most 30 characters (Oracle 11g), in every engine.
   - Eloquent Model `$fillable` arrays or relationship foreign keys MUST match exact database column names.

5. **Schema Reference**:
   - **Hospital tables (schema `HOS` in production, read only, local copies in dev/tests)**: `MR_PATIENT_TB` (`MRP_PATREC_CODE`, `HOS_NO`), `EMPLOYEE_TB` (`EMP_SERIAL`, `HOS_NO`), `ADMISSION_PATIENT_TB`, `INPATIENT_ADM_TB`, `C_LOCATION_TB`, `C_SEX_TB`, `C_JOB_TB`, `C_HOSPITAL_TB`. Keys are composite with `HOS_NO`; models use `App\Models\Concerns\HospitalTable` (key = code column + global scope `HOS_NO = config('hospital.hospital_no')`). Never read them without that scope. They are read only: their models refuse every write (`App\Models\Builders\HospitalTableBuilder`, `App\Exceptions\HospitalTableReadOnly`); fill the dev/test local copies only through `App\Support\LocalHospitalCopy` (factories use `Database\Factories\Concerns\StoresLocalHospitalRows`).
   - **Employee pick lists** follow the job: `Employee::pickLists()` / `scopeInRole(App\Enums\EmployeeRole)` with the C_JOB_TB codes of `hospital.employees.job_codes`; validate with `new HospitalEmployeeExists(EmployeeRole::...)`.
   - **WCCMS rows that reference a patient or employee** carry one `*_HOS_NO` column (e.g. `WC_REG_HOS_NO`) and use `App\Models\Concerns\BelongsToHospital`, which scopes reads and checks every link before saving. Validate codes with `App\Rules\HospitalPatientExists` / `HospitalEmployeeExists`, never `exists:`.
   - **`wc_users_tb`**: `PER_USER_CODE` (PK), link `PER_USER_EMP_CD` + `PER_USER_HOS_NO` (UNIQUE together, the linked hospital employee)
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
