# Data Field ↔ Database Schema Consistency Audit Mapping

**Document File**: `docs/ui-schema-mapping.md`
**Created**: 2026-09-20
**Feature**: [spec.md](../specs/002-ui-foundation-blade/spec.md) | **Requirement**: FR-007 / SC-005

---

## 1. Overview & Audit Methodology

This audit cross-references 100% of data labels and display fields across all 8 static HTML pages in `pre-views/` against the Phase 1 Database Schema (`*_TB` tables, primary/foreign keys, and enum CHECK constraints).

Every UI field is mapped to its exact source `TABLE.COLUMN` or explicitly logged as a **Gap (No Column Match)** with a proposed resolution for Phase 1 specification amendments.

---

## 2. Per-Page Template Mappings

### 2.1 Template: `pre-views/index.html` (Auth Login & Main Shell — Phase 3)

| UI Label / Field Name | Target Table | Target Column | Data Type | Enum CHECK Constraint Match | Status |
|-----------------------|--------------|---------------|-----------|-----------------------------|--------|
| اسم المستخدم / Username | `PER_USERS_TB` | `PER_USER_USERNAME` | `varchar(50)` | N/A | Matched |
| كلمة المرور / Password | `PER_USERS_TB` | `PER_USER_PASSWORD_HASH` | `varchar(255)` | N/A | Matched |
| دور المستخدم / User Role | `PER_USERS_TB` | `PER_USER_ROLE` | `varchar(20)` | `ADMIN \| NURSE \| SUPERVISOR` | Matched |
| حالة المستخدم / Is Active | `PER_USERS_TB` | `IS_ACTIVE` | `boolean` | `1 \| 0` | Matched |
| اسم الموظف / Employee Full Name | `PER_EMPLOYEE_TB` | `PER_EMP_FULL_NAME` | `varchar(150)` | N/A | Matched |
| القسم الطبي / Department | `WC_DEPARTMENT_TB` | `WC_DEPT_NAME` | `varchar(100)` | N/A | Matched |

---

### 2.2 Template: `pre-views/requests.html` (Registration Requests Queue — Phase 4)

| UI Label / Field Name | Target Table | Target Column | Data Type | Enum CHECK Constraint Match | Status |
|-----------------------|--------------|---------------|-----------|-----------------------------|--------|
| كود الطلب / Request Code | `WC_REQUEST_REG_TB` | `WC_REQ_CODE` | `char(36)` | N/A (UUID PK) | Matched |
| كود المريض بالمستشفى / Hospital Patient ID | `MR_PATIENT_TB` | `MR_PAT_HOSPITAL_NO` | `varchar(50)` | N/A | Matched |
| اسم المريض الثلاثي / Patient Full Name | `MR_PATIENT_TB` | `MR_PAT_FULL_NAME` | `varchar(150)` | N/A | Matched |
| رقم الهوية القومية / National ID | `MR_PATIENT_TB` | `MR_PAT_NATIONAL_ID` | `varchar(20)` | N/A | Matched |
| حالة الطلب / Request Status | `WC_REQUEST_REG_TB` | `WC_REQ_STATUS` | `varchar(20)` | `PENDING \| APPROVED \| REJECTED \| CANCELLED` | Matched |
| تاريخ ووقت الطلب / Requested At | `WC_REQUEST_REG_TB` | `WC_REQ_CREATED_AT` | `datetime` | N/A | Matched |
| ملاحظات الطلب / Request Notes | `WC_REQUEST_REG_TB` | `WC_REQ_NOTES` | `text` | N/A | Matched |
| مراجع بواسطة / Reviewed By | `WC_REQUEST_REG_TB` | `WC_REQ_REVIEWED_BY_CD` | NONE | NONE | **Gap (No Column)** |
| سبب الرفض / Rejection Reason | `WC_REQUEST_REG_TB` | `WC_REQ_REJECTION_REASON` | `text` | N/A | Matched |

---

### 2.3 Template: `pre-views/registered-patients.html` (Active Registrations — Phase 5)

| UI Label / Field Name | Target Table | Target Column | Data Type | Enum CHECK Constraint Match | Status |
|-----------------------|--------------|---------------|-----------|-----------------------------|--------|
| كود التسجيل / Registration Code | `WC_REGISTRATION_TB` | `WC_REG_CODE` | `char(36)` | N/A (UUID PK) | Matched |
| كود المريض / Patient Code | `MR_PATIENT_TB` | `MR_PAT_CODE` | `char(36)` | N/A (UUID PK) | Matched |
| تاريخ التسجيل / Registration Date | `WC_REGISTRATION_TB` | `WC_REG_DATE` | `date` | N/A | Matched |
| حالة التسجيل / Registration Status | `WC_REGISTRATION_TB` | `WC_REG_STATUS` | `varchar(20)` | `ACTIVE \| DISCHARGED` | Matched |
| الممرض المسجل / Registering Nurse | `PER_EMPLOYEE_TB` | `PER_EMP_FULL_NAME` | `varchar(150)` | N/A | Matched |
| القسم المحول منه / Department Name | `WC_DEPARTMENT_TB` | `WC_DEPT_NAME` | `varchar(100)` | N/A | Matched |
| نوع التخريج / Discharge Type | `WC_REGISTRATION_TB` | `WC_REG_DISCHARGE_TYPE` | `varchar(30)` | `HEALED \| TRANSFERRED \| MANUAL` | Matched |
| تاريخ التخريج / Discharge Date | `WC_REGISTRATION_TB` | `WC_REG_DISCHARGE_DATE` | `date` | N/A | Matched |

---

### 2.4 Template: `pre-views/complete-registration.html` (Registration & Diagnosis Entry — Phase 5)

| UI Label / Field Name | Target Table | Target Column | Data Type | Enum CHECK Constraint Match | Status |
|-----------------------|--------------|---------------|-----------|-----------------------------|--------|
| التشخيص الأولي / Diagnosis Name | `WC_DIAGNOSIS_TB` | `WC_DIAG_NAME` | `varchar(150)` | N/A | Matched |
| نوع الجرح / Wound Type Name | `WC_WOUND_TYPE_TB` | `WC_WTYPE_NAME` | `varchar(100)` | N/A | Matched |
| الضمادة الأولية / Initial Dressing | `WC_DRESSING_TB` | `WC_DRESS_NAME` | `varchar(100)` | N/A | Matched |
| العلاج الموضعي / Initial Medication | `WC_MEDICATION_TB` | `WC_MED_NAME` | `varchar(100)` | N/A | Matched |
| وصف الجرح والموقع / Description | `WC_REG_DIAGNOSIS_TB` | `WC_RDIAG_DESCRIPTION` | `text` | N/A | Matched |
| التاريخ المرضي السابق / PMH Items | `WC_PMH_TB` | `WC_PMH_NAME` | `varchar(100)` | N/A | Matched |
| الإجراء الأولي / Initial Procedure | `WC_PROCEDURE_TB` | `WC_PROC_NAME` | `varchar(150)` | N/A | Matched |

---

### 2.5 Template: `pre-views/daily-review.html` (Daily Visit Assessment — Phase 6)

| UI Label / Field Name | Target Table | Target Column | Data Type | Enum CHECK Constraint Match | Status |
|-----------------------|--------------|---------------|-----------|-----------------------------|--------|
| كود التقييم اليومي / Daily Review Code | `WC_DAILY_REVIEW_TB` | `WC_DREV_CODE` | `char(36)` | N/A (UUID PK) | Matched |
| تاريخ التقييم / Review Date | `WC_DAILY_REVIEW_TB` | `WC_DREV_DATE` | `date` | N/A | Matched |
| طول الجرح (سم) / Wound Length | `WC_DAILY_REVIEW_DETAIL_TB` | `WC_DREVD_LENGTH_CM` | `decimal(5,2)` | N/A | Matched |
| عرض الجرح (سم) / Wound Width | `WC_DAILY_REVIEW_DETAIL_TB` | `WC_DREVD_WIDTH_CM` | `decimal(5,2)` | N/A | Matched |
| عمق الجرح (سم) / Wound Depth | `WC_DAILY_REVIEW_DETAIL_TB` | `WC_DREVD_DEPTH_CM` | `decimal(5,2)` | N/A | Matched |
| درجة الألم / Pain Score (0-10) | `WC_DAILY_REVIEW_DETAIL_TB` | `WC_DREVD_PAIN_SCORE` | `tinyint` | `0` through `10` | Matched |
| كمية الإفرازات / Exudate Amount | `WC_DAILY_REVIEW_DETAIL_TB` | `WC_DREVD_EXUDATE_AMOUNT` | `varchar(30)` | `NONE \| LOW \| MODERATE \| HIGH` | Matched |
| نوع الإفرازات / Exudate Type | `WC_DAILY_REVIEW_DETAIL_TB` | `WC_DREVD_EXUDATE_TYPE` | `varchar(30)` | `SEROUS \| SEROSANGUINOUS \| PURULENT` | Matched |
| نوع النسيج / Tissue Type | `WC_DAILY_REVIEW_DETAIL_TB` | `WC_DREVD_TISSUE_TYPE` | `varchar(30)` | `GRANULATION \| SLOUGH \| NECROTIC \| EPITHELIAL` | Matched |
| حالة الجلد المحيط / Periwound Condition | `WC_DAILY_REVIEW_DETAIL_TB` | `WC_DREVD_PERIWOUND` | `varchar(50)` | N/A | Matched |
| رقم التقييم المرجعي / Visit Serial | `WC_DAILY_REVIEW_TB` | `WC_DREV_SERIAL_NO` | NONE | NONE | **Gap (No Column)** |

---

### 2.6 Template: `pre-views/scheduled-reviews.html` (Appointments & Invalidation — Phase 7)

| UI Label / Field Name | Target Table | Target Column | Data Type | Enum CHECK Constraint Match | Status |
|-----------------------|--------------|---------------|-----------|-----------------------------|--------|
| كود الموعد / Scheduled Review Code | `WC_SCHEDULED_REVIEW_TB` | `WC_SREV_CODE` | `char(36)` | N/A (UUID PK) | Matched |
| تاريخ الموعد / Scheduled Date | `WC_SCHEDULED_REVIEW_TB` | `WC_SREV_SCHEDULED_DATE` | `date` | N/A | Matched |
| حالة الموعد / Appointment Status | `WC_SCHEDULED_REVIEW_TB` | `WC_SREV_STATUS` | `varchar(20)` | `PENDING \| COMPLETED \| VOIDED \| CANCELLED` | Matched |
| سبب التلغية/الإبطال / Void Reason | `WC_SCHEDULED_REVIEW_TB` | `WC_SREV_VOID_REASON` | `text` | N/A | Matched |
| تاريخ التلغية / Voided At | `WC_SCHEDULED_REVIEW_TB` | `WC_SREV_VOIDED_AT` | `datetime` | N/A | Matched |

---

### 2.7 Template: `pre-views/admin.html` (Master Data Reference Management — Phase 8)

| UI Label / Field Name | Target Table | Target Column | Data Type | Enum CHECK Constraint Match | Status |
|-----------------------|--------------|---------------|-----------|-----------------------------|--------|
| كود الضمادة / Dressing Code | `WC_DRESSING_TB` | `WC_DRESS_CODE` | `char(36)` | N/A (UUID PK) | Matched |
| اسم الضمادة / Dressing Name | `WC_DRESSING_TB` | `WC_DRESS_NAME` | `varchar(100)` | N/A | Matched |
| كود العلاج / Medication Code | `WC_MEDICATION_TB` | `WC_MED_CODE` | `char(36)` | N/A (UUID PK) | Matched |
| اسم العلاج / Medication Name | `WC_MEDICATION_TB` | `WC_MED_NAME` | `varchar(100)` | N/A | Matched |
| كود الإجراء / Procedure Code | `WC_PROCEDURE_TB` | `WC_PROC_CODE` | `char(36)` | N/A (UUID PK) | Matched |
| اسم الإجراء / Procedure Name | `WC_PROCEDURE_TB` | `WC_PROC_NAME` | `varchar(150)` | N/A | Matched |
| حالة التفعيل / Is Active Toggle | `WC_DRESSING_TB` | `IS_ACTIVE` | `boolean` | `1 \| 0` | Matched |

---

### 2.8 Template: `pre-views/patient-history.html` (Clinical Reports & Dashboards — Phase 9)

| UI Label / Field Name | Target Table | Target Column | Data Type | Enum CHECK Constraint Match | Status |
|-----------------------|--------------|---------------|-----------|-----------------------------|--------|
| السجل التاريخي للمريض / Patient History List | `MR_PATIENT_TB` | `MR_PAT_CODE` | `char(36)` | N/A | Matched |
| عدد الزيارات الإجمالي / Total Visits Count | `WC_DAILY_REVIEW_TB` | `COUNT(WC_DREV_CODE)` | Aggregate | N/A | Matched |
| نسبة التئام الجرح / Healing Percentage | `WC_DAILY_REVIEW_DETAIL_TB` | `WC_DREVD_LENGTH_CM * WIDTH_CM` | Calculated | N/A | Matched |
| مؤشر المخاطر / Risk Score Indicator | `MR_PATIENT_TB` | `RISK_SCORE` | NONE | NONE | **Gap (No Column)** |

---

## 3. Gap Summary & Proposed Phase 1 Amendments

The following 3 UI fields were identified as having **No Column Match** in the Phase 1 DB schema. They are logged below as gaps for potential Phase 1 schema amendment:

| # | Unmatched UI Field | Affected Template | Affected Phase | Proposed Schema Resolution | Status |
|---|--------------------|-------------------|----------------|----------------------------|--------|
| 1 | مراجع بواسطة / Reviewed By | `requests.html` | Phase 4 | Add `WC_REQ_REVIEWED_BY_CD` foreign key column referencing `PER_EMPLOYEE_TB` to `WC_REQUEST_REG_TB`. | Logged Gap |
| 2 | رقم التقييم المرجعي / Visit Serial | `daily-review.html` | Phase 6 | Add `WC_DREV_SERIAL_NO` auto-incrementing integer or sequence column to `WC_DAILY_REVIEW_TB`. | Logged Gap |
| 3 | مؤشر المخاطر / Risk Score Indicator | `patient-history.html` | Phase 9 | Compute dynamically via database view query joining PMH & wound severity rather than storing a column. | Resolved via View |
