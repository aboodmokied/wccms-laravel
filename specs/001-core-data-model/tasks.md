# Tasks: Phase 1 — Core Data Model

**Feature**: Phase 1 — Core Data Model  
**Branch**: `001-core-data-model`  
**Plan**: [specs/001-core-data-model/plan.md](file:///e:/wamp64/www/wccms/specs/001-core-data-model/plan.md)  
**Spec**: [specs/001-core-data-model/spec.md](file:///e:/wamp64/www/wccms/specs/001-core-data-model/spec.md)  

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization, environment setup, and test runner readiness.

- [x] T001 Configure local environment `.env` for MySQL `wccms` database connection
- [x] T002 Verify PHP 8.3 and Composer dependencies in `composer.json`
- [x] T003 [P] Configure Pint code formatting script in `package.json` / Composer scripts

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core database migration framework and base model configuration required by all clinical domain models.

- [x] T004 Create unified database migration file `database/migrations/2026_09_20_000001_create_wccms_core_tables.php` defining all 16 tables with `CHAR(36)` PKs (`PREFIX_CODE`), `_CD` FKs, `<short_prefix>_` column prefixes, explicit single-column indexes on FKs & status columns, and unique index on `MR_PAT_ID_NO`
- [x] T005 [P] Create base Eloquent Model properties trait/contract in `app/Models/Concerns/HasDbFirstAttributes.php` enforcing `$primaryKey`, `$incrementing = false`, `$keyType = 'string'`, and `$timestamps = false`

**Checkpoint**: Core migration structure ready - user story implementations can now begin.

---

## Phase 3: User Story 1 — Master Reference Data & System Foundation (Priority: P1) 🎯 MVP

**Goal**: Initialize 7 master reference tables (`WC_DEPARTMENT_TB`, `WC_PMH_TB`, `WC_DIAGNOSIS_TB`, `WC_WOUND_TYPE_TB`, `WC_DRESSING_TB`, `WC_MEDICATION_TB`, `WC_PROCEDURE_TB`) and core identity models (`PER_USERS_TB`, `PER_EMPLOYEE_TB`, `MR_PATIENT_TB`) with baseline seeders.

**Independent Test**: Execute `php artisan migrate:fresh --seed` and verify all 7 master tables are populated with active default options and identity models link correctly.

### Implementation for User Story 1

- [x] T006 [P] [US1] Create User model in `app/Models/PerUser.php` mapping `per_users_tb` (`PER_USER_CODE` PK, `PER_USER_USERNAME`, `PER_USER_PASSWORD`, `PER_USER_ROLE` [`NURSE`, `ADMIN`], `PER_USER_IS_ACTIVE`)
- [x] T007 [P] [US1] Create Employee model in `app/Models/PerEmployee.php` mapping `per_employee_tb` (`PER_EMP_CODE` PK, `PER_EMP_FULL_NAME`, `PER_EMP_JOB_TITLE`, `PER_EMP_USER_CD` FK)
- [x] T008 [P] [US1] Create Patient model in `app/Models/MrPatient.php` mapping `mr_patient_tb` (`MR_PAT_CODE` PK, `MR_PAT_ID_NO` Unique, `MR_PAT_FULL_NAME`, `MR_PAT_PHONE`, `MR_PAT_GENDER`, `MR_PAT_DOB`, `MR_PAT_LAST_SYNCED_AT`)
- [x] T009 [P] [US1] Create Department model in `app/Models/WcDepartment.php` mapping `wc_department_tb` (`WC_DEPT_CODE` PK, `WC_DEPT_NAME`, `WC_DEPT_IS_ACTIVE`)
- [x] T010 [P] [US1] Create PMH model in `app/Models/WcPmh.php` mapping `wc_pmh_tb` (`WC_PMH_CODE` PK, `WC_PMH_NAME`, `WC_PMH_IS_ACTIVE`)
- [x] T011 [P] [US1] Create Diagnosis model in `app/Models/WcDiagnosis.php` mapping `wc_diagnosis_tb` (`WC_DIAG_CODE` PK, `WC_DIAG_NAME`, `WC_DIAG_IS_ACTIVE`)
- [x] T012 [P] [US1] Create WoundType model in `app/Models/WcWoundType.php` mapping `wc_wound_type_tb` (`WC_WTYPE_CODE` PK, `WC_WTYPE_NAME`, `WC_WTYPE_IS_ACTIVE`)
- [x] T013 [P] [US1] Create Dressing model in `app/Models/WcDressing.php` mapping `wc_dressing_tb` (`WC_DRESS_CODE` PK, `WC_DRESS_NAME`, `WC_DRESS_IS_ACTIVE`)
- [x] T014 [P] [US1] Create Medication model in `app/Models/WcMedication.php` mapping `wc_medication_tb` (`WC_MED_CODE` PK, `WC_MED_NAME`, `WC_MED_IS_ACTIVE`)
- [x] T015 [P] [US1] Create Procedure model in `app/Models/WcProcedure.php` mapping `wc_procedure_tb` (`WC_PROC_CODE` PK, `WC_PROC_NAME`, `WC_PROC_IS_ACTIVE`)
- [x] T016 [P] [US1] Create Department Seeder in `database/seeders/WcDepartmentSeeder.php` populating baseline departments using `firstOrCreate`
- [x] T017 [P] [US1] Create PMH Seeder in `database/seeders/WcPmhSeeder.php` populating baseline medical history options
- [x] T018 [P] [US1] Create Diagnosis Seeder in `database/seeders/WcDiagnosisSeeder.php` populating baseline wound diagnoses
- [x] T019 [P] [US1] Create WoundType Seeder in `database/seeders/WcWoundTypeSeeder.php` populating baseline wound types
- [x] T020 [P] [US1] Create Dressing Seeder in `database/seeders/WcDressingSeeder.php` populating baseline dressings
- [x] T021 [P] [US1] Create Medication Seeder in `database/seeders/WcMedicationSeeder.php` populating baseline medications
- [x] T022 [P] [US1] Create Procedure Seeder in `database/seeders/WcProcedureSeeder.php` populating baseline procedures
- [x] T023 [US1] Wire master reference seeders into main `database/seeders/DatabaseSeeder.php`
- [x] T024 [US1] Write feature test in `tests/Feature/MasterDataSeederTest.php` verifying all 7 master tables seed cleanly within 3 seconds

**Checkpoint**: User Story 1 complete — Master reference data and core identity models fully testable independently.

---

## Phase 4: User Story 2 — Registration Request & Registration Model (Priority: P2)

**Goal**: Implement `WC_REQUEST_REG_TB`, `WC_REGISTRATION_TB`, and `WC_REG_DIAGNOSIS_TB` models with patient snapshot fields, supervisor review audit (`WC_REQ_REVIEWED_BY_CD`), and dual actor/data-entry audit columns.

**Independent Test**: Create a registration request record, approve it with a supervisor employee code, and generate an active registration record with multiple diagnoses.

### Implementation for User Story 2

- [x] T025 [P] [US2] Create Registration Request model in `app/Models/WcRequestReg.php` mapping `wc_request_reg_tb` (`WC_REQ_CODE` PK, `WC_REQ_PATIENT_CD`, `WC_REQ_PAT_NAME_SNAPSHOT`, `WC_REQ_PAT_ID_SNAPSHOT`, `WC_REQ_PAT_PHONE_SNAPSHOT`, `WC_REQ_NURSE_CD`, `WC_REQ_ENTRY_USER_CD`, `WC_REQ_REVIEWED_BY_CD`, `WC_REQ_STATUS` [`PENDING`, `APPROVED`, `REJECTED`, `CANCELLED`], `WC_REQ_NOTES`, `WC_REQ_CREATED_AT`)
- [x] T026 [P] [US2] Create Clinical Registration model in `app/Models/WcRegistration.php` mapping `wc_registration_tb` (`WC_REG_CODE` PK, `WC_REG_REQUEST_CD`, `WC_REG_PATIENT_CD`, `WC_REG_DEPARTMENT_CD`, `WC_REG_PROCEDURE_CD`, `WC_REG_PMH_NOTES`, `WC_REG_DATE`, `WC_REG_NURSE_CD`, `WC_REG_ENTRY_USER_CD`, `WC_REG_STATUS` [`ACTIVE`, `DISCHARGED`, `CLOSED`], `WC_REG_DISCHARGED_AT`, `WC_REG_DISCHARGED_BY_EMP_CD`)
- [x] T027 [P] [US2] Create Registration Diagnosis model in `app/Models/WcRegDiagnosis.php` mapping `wc_reg_diagnosis_tb` (`WC_RDIAG_CODE` PK, `WC_RDIAG_REGISTRATION_CD`, `WC_RDIAG_DIAGNOSIS_CD`, `WC_RDIAG_WOUND_TYPE_CD`, `WC_RDIAG_CLINICAL_EMP_CD`, `WC_RDIAG_ENTRY_USER_CD`)
- [x] T028 [US2] Define Eloquent relationship methods on `WcRequestReg`, `WcRegistration`, and `WcRegDiagnosis` linking `MrPatient`, `PerEmployee`, `PerUser`, and reference models
- [x] T029 [US2] Write feature test in `tests/Feature/RegistrationModelTest.php` testing request submission snapshot fields, approval status transitions, and dual audit tracking

**Checkpoint**: User Story 2 complete — Patient registration requests and merged registration models independently functional.

---

## Phase 5: User Story 3 — Daily Review & Scheduled Review Tracking (Priority: P3)

**Goal**: Implement `WC_DAILY_REVIEW_TB`, `WC_DAILY_REVIEW_DETAIL_TB`, and `WC_SCHEDULED_REVIEW_TB` models with serial visit counter (`WC_SREV_VISIT_SERIAL`) and `VOIDED` appointment status support.

**Independent Test**: Create a daily review header with multiple wound assessment details and verify appointment status updates (`PENDING`, `COMPLETED`, `VOIDED`).

### Implementation for User Story 3

- [x] T030 [P] [US3] Create Daily Review Header model in `app/Models/WcDailyReview.php` mapping `wc_daily_review_tb` (`WC_DREV_CODE` PK, `WC_DREV_REGISTRATION_CD`, `WC_DREV_SCHEDULED_REVIEW_CD`, `WC_DREV_VISIT_DATE`, `WC_DREV_NURSE_CD`, `WC_DREV_ENTRY_USER_CD`, `WC_DREV_GENERAL_NOTES`)
- [x] T031 [P] [US3] Create Daily Review Detail model in `app/Models/WcDailyReviewDetail.php` mapping `wc_daily_review_detail_tb` (`WC_DREVD_CODE` PK, `WC_DREVD_DAILY_REVIEW_CD`, `WC_DREVD_REG_DIAGNOSIS_CD`, `WC_DREVD_DRESSING_CD`, `WC_DREVD_MEDICATION_CD`, `WC_DREVD_OBSERVATION`, `WC_DREVD_OUTCOME_STATUS` [`IMPROVED_DISCHARGE`, `IMPROVED_FOLLOWUP`, `WORSENED`, `DECEASED`])
- [x] T032 [P] [US3] Create Scheduled Review model in `app/Models/WcScheduledReview.php` mapping `wc_scheduled_review_tb` (`WC_SREV_CODE` PK, `WC_SREV_REGISTRATION_CD`, `WC_SREV_SCHEDULED_DATE`, `WC_SREV_VISIT_SERIAL`, `WC_SREV_STATUS` [`PENDING`, `COMPLETED`, `MISSED`, `CANCELLED`, `VOIDED`])
- [x] T033 [US3] Define Eloquent relationship methods on `WcDailyReview`, `WcDailyReviewDetail`, and `WcScheduledReview` linking registration and wound details
- [x] T034 [US3] Write feature test in `tests/Feature/DailyReviewModelTest.php` verifying header-detail visit creation, serial visit index tracking, and `VOIDED` appointment invalidation state

**Checkpoint**: User Story 3 complete — All clinical domain models and scheduling structures fully functional.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Code formatting, full migration validation, and quickstart documentation check.

- [x] T035 Run `vendor/bin/pint --format agent` to format all modified PHP files
- [x] T036 Execute `php artisan migrate:fresh --seed` to confirm full database build with zero errors
- [x] T037 Execute full test suite `php artisan test` verifying 100% pass rate
- [x] T038 Validate runnable scenarios in `specs/001-core-data-model/quickstart.md`

---

## Dependencies & Execution Order

### Phase Dependencies

```
Setup (Phase 1) ──> Foundational (Phase 2) ──> User Story 1 (Phase 3)
                                                      │
                                                      ├──> User Story 2 (Phase 4)
                                                      │
                                                      └──> User Story 3 (Phase 5)
                                                                 │
                                                                 └──> Polish (Phase 6)
```

### Parallel Opportunities

- All models in Phase 3 (`T006` to `T015`) can be implemented in parallel.
- All reference seeders in Phase 3 (`T016` to `T022`) can be implemented in parallel.
- Domain models in Phase 4 (`T025` to `T027`) and Phase 5 (`T030` to `T032`) can be created in parallel once Phase 2 completes.

---

## Implementation Strategy

### MVP Scope (User Story 1)
1. Complete Phase 1 & 2 (Setup & Foundational Migration).
2. Complete Phase 3 (User Story 1: Identity & Reference Seeders).
3. Validate `php artisan migrate:fresh --seed`.

### Full Phase 1 Delivery
1. Complete Phase 4 & 5 (Registration, Daily Review, Scheduling Models).
2. Run `php artisan test` to verify all 16 models and relationships.
3. Perform Pint formatting check.
