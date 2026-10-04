# Phase 1 Data Model & Analytical Structures: Reporting & Dashboards (Phase 9)

## 1. Executive Dashboard KPI Aggregation Structure

The `/dashboard` route aggregates metrics from underlying database tables:

### KPI Metric Definitions

1. **Active Patients Count (`active_patients`)**:
   - Source: `WC_REGISTRATION_TB`
   - Filter: `WC_REG_STATUS = 'REGISTERED'`
   - Output: Integer

2. **Average Treatment Duration (`avg_treatment_days`)**:
   - Source: `WC_REGISTRATION_TB`
   - Formula:
     - Discharged: `DATEDIFF(WC_REG_DISCHARGE_DATE, WC_REG_REGISTRATION_DATE)`
     - Active: `DATEDIFF(CURRENT_DATE, WC_REG_REGISTRATION_DATE)`
   - Output: Rounded Integer (Days)

3. **Outcome Distribution (`outcome_distribution`)**:
   - Source: `WC_DAILY_REVIEW_TB`
   - Status Groups:
     - `DISCHARGED`: Improved & Discharged (`WC_DREV_STATUS_EVAL = 'DISCHARGED'`)
     - `FOLLOWUP`: Improved & Needs Follow-up (`WC_DREV_STATUS_EVAL = 'FOLLOWUP'`)
     - `WORSENED`: Worsened Condition (`WC_DREV_STATUS_EVAL = 'WORSENED'`)
     - `DECEASED`: Deceased (`WC_DREV_STATUS_EVAL = 'DECEASED'`)
   - Output: Map of `{ status: string, count: int, percentage: float }`

4. **Missed Appointment Rate (`missed_appointment_rate`)**:
   - Source: `WC_SCHEDULED_REVIEW_TB`
   - Formula: `(COUNT(where status = 'MISSED') / COUNT(where status IN ('COMPLETED', 'MISSED', 'CANCELLED'))) * 100`
   - Output: Percentage float rounded to 1 decimal place (e.g. `12.5%`)

---

## 2. Patient Clinical Timeline Structure

The `/reports/history` view builds a unified timeline for a patient (`MR_PATIENT_TB`):

```
Patient (MR_PATIENT_TB)
 ├── Registration Session 1 (WC_REGISTRATION_TB)
 │    ├── Initial Diagnoses / Wounds (WC_PATIENT_DIAGNOSIS_TB)
 │    ├── Daily Review Visits (WC_DAILY_REVIEW_TB)
 │    │    └── Wound Evaluations & Dressings (WC_WOUND_EVALUATION_TB)
 │    └── Scheduled Appointments (WC_SCHEDULED_REVIEW_TB)
 └── Registration Session 2 (WC_REGISTRATION_TB)
      └── ...
```

---

## 3. CSV Export Payload Data Model

The CSV export generated from `/reports/history/{patient}/export` contains structured headers:

1. **Patient Demographics Header**: MRN, Patient Name, Gender, Birth Date, Phone.
2. **Clinical Session Records**: Registration Code, Registration Date, Department, Primary Nurse, Status, Discharge Date.
3. **Daily Assessment Logs**: Visit Date, Assessment Status, Observed Changes, Nurse Name.
4. **Scheduled Appointment Logs**: Appointment Date, Target Nurse, Status (COMPLETED, MISSED, CANCELLED).
