# Data Model & Schema Specification: Phase 1 — Core Data Model

## Entity Relationship Overview

The WCCMS core database consists of **16 tables** structured around patient registration, clinical review headers/details, appointment scheduling, identity directories, and master reference data.

```
+------------------+         +--------------------+         +-------------------+
|  MR_PATIENT_TB   | <------ | WC_REQUEST_REG_TB  | ------> |  PER_EMPLOYEE_TB  |
+------------------+         +--------------------+         +-------------------+
        ^                             |                              ^
        |                             v                              |
        |                    +--------------------+                  |
        +------------------- |  WC_REGISTRATION_TB| -----------------+
                             +--------------------+
                                      |
              +-----------------------+-----------------------+
              |                                               |
              v                                               v
  +-----------------------+                       +-----------------------+
  |  WC_REG_DIAGNOSIS_TB  |                       |WC_SCHEDULED_REVIEW_TB |
  +-----------------------+                       +-----------------------+
              ^                                               ^
              |                                               |
              v                                               v
  +-------------------------------+               +-----------------------+
  |  WC_DAILY_REVIEW_DETAIL_TB    | ------------> |   WC_DAILY_REVIEW_TB  |
  +-------------------------------+               +-----------------------+
```

---

## Data Schema & Table Definitions

### 1. `PER_USERS_TB` (User Identity Accounts)
- **Model**: `App\Models\PerUser`
- **Table**: `per_users_tb`
- **Primary Key**: `PER_USER_CODE` (`CHAR(36)`)
- **Columns**:
  - `PER_USER_CODE` (`CHAR(36)`, PK)
  - `PER_USER_USERNAME` (`VARCHAR(100)`, Unique, Not Null)
  - `PER_USER_PASSWORD` (`VARCHAR(255)`, Not Null)
  - `PER_USER_ROLE` (`VARCHAR(20)`, Not Null) — `NURSE`, `ADMIN`
  - `PER_USER_IS_ACTIVE` (`SMALLINT`, Default 1, Not Null)
- **Indexes**: Unique index on `PER_USER_USERNAME`

### 2. `PER_EMPLOYEE_TB` (Hospital Employee Directory)
- **Model**: `App\Models\PerEmployee`
- **Table**: `per_employee_tb`
- **Primary Key**: `PER_EMP_CODE` (`CHAR(36)`)
- **Columns**:
  - `PER_EMP_CODE` (`CHAR(36)`, PK)
  - `PER_EMP_FULL_NAME` (`VARCHAR(150)`, Not Null)
  - `PER_EMP_JOB_TITLE` (`VARCHAR(100)`, Nullable)
  - `PER_EMP_USER_CD` (`CHAR(36)`, FK to `PER_USERS_TB.PER_USER_CODE`, Nullable)
- **Indexes**: Index on `PER_EMP_USER_CD`

### 3. `MR_PATIENT_TB` (Patient Master Index)
- **Model**: `App\Models\MrPatient`
- **Table**: `mr_patient_tb`
- **Primary Key**: `MR_PAT_CODE` (`CHAR(36)`)
- **Columns**:
  - `MR_PAT_CODE` (`CHAR(36)`, PK)
  - `MR_PAT_ID_NO` (`VARCHAR(50)`, Unique, Not Null)
  - `MR_PAT_FULL_NAME` (`VARCHAR(150)`, Not Null)
  - `MR_PAT_PHONE` (`VARCHAR(30)`, Nullable)
  - `MR_PAT_GENDER` (`VARCHAR(10)`, Not Null) — `MALE`, `FEMALE`
  - `MR_PAT_DOB` (`DATE`, Nullable)
  - `MR_PAT_LAST_SYNCED_AT` (`DATETIME`, Nullable)
- **Indexes**: Unique index on `MR_PAT_ID_NO`

### 4. `WC_REQUEST_REG_TB` (Registration Request Queue)
- **Model**: `App\Models\WcRequestReg`
- **Table**: `wc_request_reg_tb`
- **Primary Key**: `WC_REQ_CODE` (`CHAR(36)`)
- **Columns**:
  - `WC_REQ_CODE` (`CHAR(36)`, PK)
  - `WC_REQ_PATIENT_CD` (`CHAR(36)`, FK to `MR_PATIENT_TB.MR_PAT_CODE`, Not Null)
  - `WC_REQ_PAT_NAME_SNAPSHOT` (`VARCHAR(150)`, Not Null)
  - `WC_REQ_PAT_ID_SNAPSHOT` (`VARCHAR(50)`, Not Null)
  - `WC_REQ_PAT_PHONE_SNAPSHOT` (`VARCHAR(30)`, Nullable)
  - `WC_REQ_NURSE_CD` (`CHAR(36)`, FK to `PER_EMPLOYEE_TB.PER_EMP_CODE`, Not Null)
  - `WC_REQ_ENTRY_USER_CD` (`CHAR(36)`, FK to `PER_USERS_TB.PER_USER_CODE`, Not Null)
  - `WC_REQ_REVIEWED_BY_CD` (`CHAR(36)`, FK to `PER_EMPLOYEE_TB.PER_EMP_CODE`, Nullable)
  - `WC_REQ_STATUS` (`VARCHAR(20)`, Default `'PENDING'`, Not Null) — `PENDING`, `APPROVED`, `REJECTED`, `CANCELLED`
  - `WC_REQ_NOTES` (`TEXT`, Nullable)
  - `WC_REQ_CREATED_AT` (`DATETIME`, Not Null)
- **Indexes**: Indexes on `WC_REQ_PATIENT_CD`, `WC_REQ_NURSE_CD`, `WC_REQ_ENTRY_USER_CD`, `WC_REQ_REVIEWED_BY_CD`, `WC_REQ_STATUS`

### 5. `WC_REGISTRATION_TB` (Active Clinical Registration)
- **Model**: `App\Models\WcRegistration`
- **Table**: `wc_registration_tb`
- **Primary Key**: `WC_REG_CODE` (`CHAR(36)`)
- **Columns**:
  - `WC_REG_CODE` (`CHAR(36)`, PK)
  - `WC_REG_REQUEST_CD` (`CHAR(36)`, FK to `WC_REQUEST_REG_TB.WC_REQ_CODE`, Not Null)
  - `WC_REG_PATIENT_CD` (`CHAR(36)`, FK to `MR_PATIENT_TB.MR_PAT_CODE`, Not Null)
  - `WC_REG_DEPARTMENT_CD` (`CHAR(36)`, FK to `WC_DEPARTMENT_TB.WC_DEPT_CODE`, Not Null)
  - `WC_REG_PROCEDURE_CD` (`CHAR(36)`, FK to `WC_PROCEDURE_TB.WC_PROC_CODE`, Nullable)
  - `WC_REG_PMH_NOTES` (`TEXT`, Nullable)
  - `WC_REG_DATE` (`DATETIME`, Not Null)
  - `WC_REG_NURSE_CD` (`CHAR(36)`, FK to `PER_EMPLOYEE_TB.PER_EMP_CODE`, Not Null)
  - `WC_REG_ENTRY_USER_CD` (`CHAR(36)`, FK to `PER_USERS_TB.PER_USER_CODE`, Not Null)
  - `WC_REG_STATUS` (`VARCHAR(20)`, Default `'ACTIVE'`, Not Null) — `ACTIVE`, `DISCHARGED`, `CLOSED`
  - `WC_REG_DISCHARGED_AT` (`DATETIME`, Nullable)
  - `WC_REG_DISCHARGED_BY_EMP_CD` (`CHAR(36)`, FK to `PER_EMPLOYEE_TB.PER_EMP_CODE`, Nullable)
- **Indexes**: Indexes on `WC_REG_REQUEST_CD`, `WC_REG_PATIENT_CD`, `WC_REG_DEPARTMENT_CD`, `WC_REG_PROCEDURE_CD`, `WC_REG_NURSE_CD`, `WC_REG_ENTRY_USER_CD`, `WC_REG_STATUS`

### 6. `WC_REG_DIAGNOSIS_TB` (Registration Diagnoses & Wounds)
- **Model**: `App\Models\WcRegDiagnosis`
- **Table**: `wc_reg_diagnosis_tb`
- **Primary Key**: `WC_RDIAG_CODE` (`CHAR(36)`)
- **Columns**:
  - `WC_RDIAG_CODE` (`CHAR(36)`, PK)
  - `WC_RDIAG_REGISTRATION_CD` (`CHAR(36)`, FK to `WC_REGISTRATION_TB.WC_REG_CODE`, Not Null)
  - `WC_RDIAG_DIAGNOSIS_CD` (`CHAR(36)`, FK to `WC_DIAGNOSIS_TB.WC_DIAG_CODE`, Not Null)
  - `WC_RDIAG_WOUND_TYPE_CD` (`CHAR(36)`, FK to `WC_WOUND_TYPE_TB.WC_WTYPE_CODE`, Not Null)
  - `WC_RDIAG_CLINICAL_EMP_CD` (`CHAR(36)`, FK to `PER_EMPLOYEE_TB.PER_EMP_CODE`, Not Null)
  - `WC_RDIAG_ENTRY_USER_CD` (`CHAR(36)`, FK to `PER_USERS_TB.PER_USER_CODE`, Not Null)
- **Indexes**: Indexes on `WC_RDIAG_REGISTRATION_CD`, `WC_RDIAG_DIAGNOSIS_CD`, `WC_RDIAG_WOUND_TYPE_CD`, `WC_RDIAG_CLINICAL_EMP_CD`, `WC_RDIAG_ENTRY_USER_CD`

### 7. `WC_DAILY_REVIEW_TB` (Daily Review Visit Header)
- **Model**: `App\Models\WcDailyReview`
- **Table**: `wc_daily_review_tb`
- **Primary Key**: `WC_DREV_CODE` (`CHAR(36)`)
- **Columns**:
  - `WC_DREV_CODE` (`CHAR(36)`, PK)
  - `WC_DREV_REGISTRATION_CD` (`CHAR(36)`, FK to `WC_REGISTRATION_TB.WC_REG_CODE`, Not Null)
  - `WC_DREV_SCHEDULED_REVIEW_CD` (`CHAR(36)`, FK to `WC_SCHEDULED_REVIEW_TB.WC_SREV_CODE`, Nullable)
  - `WC_DREV_VISIT_DATE` (`DATETIME`, Not Null)
  - `WC_DREV_NURSE_CD` (`CHAR(36)`, FK to `PER_EMPLOYEE_TB.PER_EMP_CODE`, Not Null)
  - `WC_DREV_ENTRY_USER_CD` (`CHAR(36)`, FK to `PER_USERS_TB.PER_USER_CODE`, Not Null)
  - `WC_DREV_GENERAL_NOTES` (`TEXT`, Nullable)
- **Indexes**: Indexes on `WC_DREV_REGISTRATION_CD`, `WC_DREV_SCHEDULED_REVIEW_CD`, `WC_DREV_NURSE_CD`, `WC_DREV_ENTRY_USER_CD`

### 8. `WC_DAILY_REVIEW_DETAIL_TB` (Per-Wound Assessment Detail)
- **Model**: `App\Models\WcDailyReviewDetail`
- **Table**: `wc_daily_review_detail_tb`
- **Primary Key**: `WC_DREVD_CODE` (`CHAR(36)`)
- **Columns**:
  - `WC_DREVD_CODE` (`CHAR(36)`, PK)
  - `WC_DREVD_DAILY_REVIEW_CD` (`CHAR(36)`, FK to `WC_DAILY_REVIEW_TB.WC_DREV_CODE`, Not Null)
  - `WC_DREVD_REG_DIAGNOSIS_CD` (`CHAR(36)`, FK to `WC_REG_DIAGNOSIS_TB.WC_RDIAG_CODE`, Not Null)
  - `WC_DREVD_DRESSING_CD` (`CHAR(36)`, FK to `WC_DRESSING_TB.WC_DRESS_CODE`, Nullable)
  - `WC_DREVD_MEDICATION_CD` (`CHAR(36)`, FK to `WC_MEDICATION_TB.WC_MED_CODE`, Nullable)
  - `WC_DREVD_OBSERVATION` (`TEXT`, Nullable)
  - `WC_DREVD_OUTCOME_STATUS` (`VARCHAR(30)`, Not Null) — `IMPROVED_DISCHARGE`, `IMPROVED_FOLLOWUP`, `WORSENED`, `DECEASED`
- **Indexes**: Indexes on `WC_DREVD_DAILY_REVIEW_CD`, `WC_DREVD_REG_DIAGNOSIS_CD`, `WC_DREVD_DRESSING_CD`, `WC_DREVD_MEDICATION_CD`

### 9. `WC_SCHEDULED_REVIEW_TB` (Appointment Schedule)
- **Model**: `App\Models\WcScheduledReview`
- **Table**: `wc_scheduled_review_tb`
- **Primary Key**: `WC_SREV_CODE` (`CHAR(36)`)
- **Columns**:
  - `WC_SREV_CODE` (`CHAR(36)`, PK)
  - `WC_SREV_REGISTRATION_CD` (`CHAR(36)`, FK to `WC_REGISTRATION_TB.WC_REG_CODE`, Not Null)
  - `WC_SREV_SCHEDULED_DATE` (`DATETIME`, Not Null)
  - `WC_SREV_VISIT_SERIAL` (`INTEGER`, Default 1, Not Null)
  - `WC_SREV_STATUS` (`VARCHAR(20)`, Default `'PENDING'`, Not Null) — `PENDING`, `COMPLETED`, `MISSED`, `CANCELLED`, `VOIDED`
- **Indexes**: Indexes on `WC_SREV_REGISTRATION_CD`, `WC_SREV_STATUS`

### 10-16. 7 Master Reference Tables
- **`WC_DEPARTMENT_TB`**: `WC_DEPT_CODE` (PK, `CHAR(36)`), `WC_DEPT_NAME` (`VARCHAR(100)`), `WC_DEPT_IS_ACTIVE` (`SMALLINT`, Default 1)
- **`WC_PMH_TB`**: `WC_PMH_CODE` (PK, `CHAR(36)`), `WC_PMH_NAME` (`VARCHAR(100)`), `WC_PMH_IS_ACTIVE` (`SMALLINT`, Default 1)
- **`WC_DIAGNOSIS_TB`**: `WC_DIAG_CODE` (PK, `CHAR(36)`), `WC_DIAG_NAME` (`VARCHAR(100)`), `WC_DIAG_IS_ACTIVE` (`SMALLINT`, Default 1)
- **`WC_WOUND_TYPE_TB`**: `WC_WTYPE_CODE` (PK, `CHAR(36)`), `WC_WTYPE_NAME` (`VARCHAR(100)`), `WC_WTYPE_IS_ACTIVE` (`SMALLINT`, Default 1)
- **`WC_DRESSING_TB`**: `WC_DRESS_CODE` (PK, `CHAR(36)`), `WC_DRESS_NAME` (`VARCHAR(100)`), `WC_DRESS_IS_ACTIVE` (`SMALLINT`, Default 1)
- **`WC_MEDICATION_TB`**: `WC_MED_CODE` (PK, `CHAR(36)`), `WC_MED_NAME` (`VARCHAR(100)`), `WC_MED_IS_ACTIVE` (`SMALLINT`, Default 1)
- **`WC_PROCEDURE_TB`**: `WC_PROC_CODE` (PK, `CHAR(36)`), `WC_PROC_NAME` (`VARCHAR(100)`), `WC_PROC_IS_ACTIVE` (`SMALLINT`, Default 1)

---

## Universal Model Base Properties

All 16 Eloquent Models MUST specify the following static properties:

```php
protected $table = 'prefix_table_tb';
protected $primaryKey = 'PREFIX_CODE';
public $incrementing = false;
protected $keyType = 'string';
public $timestamps = false;
```
