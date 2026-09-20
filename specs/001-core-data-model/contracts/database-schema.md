# Database Schema & Interface Contract: Phase 1 — Core Data Model

## Interface Overview

Phase 1 exposes the foundational database contract for the Wound Care Clinic Management System (WCCMS). This document defines the exact table signatures, primary/foreign key mappings, status enums, and Eloquent model attributes that higher-level clinical modules consume.

---

## Model Contract Specifications

### 1. Identity & Employee Mapping
- **User Account (`PerUser`)**: Exposes `PER_USER_CODE` (PK), `PER_USER_USERNAME`, `PER_USER_ROLE` (`NURSE`, `ADMIN`), and `PER_USER_IS_ACTIVE`.
- **Employee (`PerEmployee`)**: Exposes `PER_EMP_CODE` (PK), `PER_EMP_FULL_NAME`, `PER_EMP_JOB_TITLE`, and belongsTo `PerUser` via `PER_EMP_USER_CD`.

### 2. Clinical Registration Lifecycle
- **Registration Request (`WcRequestReg`)**:
  - Primary Key: `WC_REQ_CODE`
  - Foreign Keys: `WC_REQ_PATIENT_CD`, `WC_REQ_NURSE_CD` (Employee), `WC_REQ_ENTRY_USER_CD` (User), `WC_REQ_REVIEWED_BY_CD` (Employee)
  - Snapshot fields: `WC_REQ_PAT_NAME_SNAPSHOT`, `WC_REQ_PAT_ID_SNAPSHOT`, `WC_REQ_PAT_PHONE_SNAPSHOT`
  - Status contract: `PENDING`, `APPROVED`, `REJECTED`, `CANCELLED`
- **Clinical Registration (`WcRegistration`)**:
  - Primary Key: `WC_REG_CODE`
  - Merged history: `WC_REG_PROCEDURE_CD`, `WC_REG_PMH_NOTES`
  - Status contract: `ACTIVE`, `DISCHARGED`, `CLOSED`

### 3. Daily Visit & Scheduling
- **Daily Review Header (`WcDailyReview`)**:
  - Primary Key: `WC_DREV_CODE`
  - Foreign Keys: `WC_DREV_REGISTRATION_CD`, `WC_DREV_SCHEDULED_REVIEW_CD`, `WC_DREV_NURSE_CD`, `WC_DREV_ENTRY_USER_CD`
- **Daily Review Detail (`WcDailyReviewDetail`)**:
  - Primary Key: `WC_DREVD_CODE`
  - Foreign Keys: `WC_DREVD_DAILY_REVIEW_CD`, `WC_DREVD_REG_DIAGNOSIS_CD`, `WC_DREVD_DRESSING_CD`, `WC_DREVD_MEDICATION_CD`
  - Outcome contract: `IMPROVED_DISCHARGE`, `IMPROVED_FOLLOWUP`, `WORSENED`, `DECEASED`
- **Scheduled Appointment (`WcScheduledReview`)**:
  - Primary Key: `WC_SREV_CODE`
  - Serial Visit Counter: `WC_SREV_VISIT_SERIAL` (Integer >= 1)
  - Status contract: `PENDING`, `COMPLETED`, `MISSED`, `CANCELLED`, `VOIDED`

---

## Seeded Reference Data Catalog

The following 7 reference tables MUST be seeded with initial baseline entries:
1. `WC_DEPARTMENT_TB`: Emergency, Outpatient Surgery, Wound Clinic, Dermatology, General Surgery
2. `WC_PMH_TB`: Diabetes Mellitus, Hypertension, Peripheral Artery Disease, Venous Insufficiency, Immunosuppression
3. `WC_DIAGNOSIS_TB`: Diabetic Foot Ulcer, Pressure Injury, Venous Leg Ulcer, Surgical Site Infection, Arterial Ulcer
4. `WC_WOUND_TYPE_TB`: Surgical Wound, Chronic Ulcer, Burn, Traumatic Laceration, Diabetic Lesion
5. `WC_DRESSING_TB`: Hydrocolloid, Foam Dressing, Silver Alginate, Gauze & Tape, Film Dressing
6. `WC_MEDICATION_TB`: Saline Wash, Topical Antibiotic Ointment, Silver Sulfadiazine, Debriding Enzymatic Gel
7. `WC_PROCEDURE_TB`: Surgical Debridement, Mechanical Cleansing, Negative Pressure Therapy, Dressing Change
