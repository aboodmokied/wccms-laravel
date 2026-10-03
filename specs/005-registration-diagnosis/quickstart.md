# Quickstart Validation Guide: Phase 5 — Registration & Diagnosis

**Feature Branch**: `005-registration-diagnosis`  
**Date**: 2026-09-26  

---

## Runnable Verification Scenarios

### Scenario 1: Registered Patients Listing & Status Filters
1. Launch app server (`cmd /c php artisan serve`).
2. Log in as clinic nurse (`ward_nurse_demo` / `NURSE`).
3. Navigate to `http://127.0.0.1:8000/registrations`.
4. Verify table defaults to `ACTIVE` registrations with patient details.
5. Click `DISCHARGED` filter tab and verify empty state or discharged patients list.
6. Type a patient's National ID or name into the search bar and verify real-time filtering.

---

### Scenario 2: Complete Registration Details & Add Wound Diagnoses
1. Select an active registration from `/registrations`.
2. Click to open registration details view (`/registrations/{registration}`).
3. Select an initial procedure (`WC_PROCEDURE_TB`) and enter PMH notes, then click Save.
4. Click "إضافة تشخيص جرح جديد" (Add New Wound Diagnosis).
5. Select Wound Type, Clinical Diagnosis, Initial Dressing, Initial Medication, and Examining Nurse.
6. Click Save and verify the new wound diagnosis appears in the registration wounds table.

---

### Scenario 3: Patient Discharge Workflow
1. Open an active registration (`/registrations/{registration}`).
2. Click "تخريج المريض" (Discharge Patient).
3. Select discharging nurse and submit.
4. Verify status badge changes to `DISCHARGED`, discharge timestamp displays, and "إضافة تشخيص جرح" button becomes disabled.

---

### Scenario 4: Automated Feature Test Suite Execution
Execute narrow test suite covering Phase 5 functionality:
```bash
php artisan test --filter=RegistrationListingTest
php artisan test --filter=RegistrationDetailsTest
php artisan test --filter=WoundDiagnosisCreationTest
php artisan test --filter=RegistrationDischargeTest
```
Or execute full suite:
```bash
php artisan test --compact
```
