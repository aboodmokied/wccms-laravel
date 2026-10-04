# Quickstart & End-to-End Validation Guide: Reporting & Dashboards (Phase 9)

## Overview
This guide provides actionable step-by-step validation scenarios to verify live executive dashboard metrics, date filtering, patient clinical history search, and CSV export.

---

## Prerequisites
- Dev server running on `http://127.0.0.1:8000`.
- Authenticated JWT session (Nurse or Admin role).
- Seeded database with active/discharged registrations and review records.

---

## Scenario 1: Validate Executive Dashboard KPIs
1. Open browser to `http://127.0.0.1:8000/dashboard`.
2. Verify the 4 primary KPI cards:
   - **المرضى المسجلون حالياً**: Matches total active registrations (`WC_REG_STATUS = 'REGISTERED'`).
   - **متوسط مدة العلاج**: Displays dynamic average treatment days.
   - **توزيع نتائج التقييمات**: Shows percentages for improved/discharged, follow-up, worsened, and deceased.
   - **نسبة التخلف عن المواعيد**: Displays percentage of missed appointments (`MISSED`).
3. Verify recent clinical activities table loads dynamic registration and review records.

---

## Scenario 2: Validate Patient History Search & Timeline
1. Navigate to `http://127.0.0.1:8000/reports/history`.
2. Enter a patient clinical number (or MRN) in the search box and click Search.
3. Verify that:
   - Patient info card displays name, MRN, phone, gender.
   - Chronological timeline lists all past registration sessions, daily evaluations, dressings, and scheduled appointment statuses.

---

## Scenario 3: Validate CSV Export Download
1. On the patient history page (`/reports/history`), click the **تصدير ملف CSV** button.
2. Verify browser downloads a file named `patient-{code}-history.csv`.
3. Open the CSV file in Excel or text editor:
   - Confirm UTF-8 BOM encoding renders Arabic characters correctly.
   - Confirm columns contain MRN, registration dates, evaluation statuses, and nurse notes.

---

## Automated Test Command
Run PHPUnit feature test suite for reports:
```bash
php artisan test --filter=ReportsAndDashboardTest
```
