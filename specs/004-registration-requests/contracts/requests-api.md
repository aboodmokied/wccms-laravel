# Contracts: Registration Requests API & Web Endpoints

**Feature**: [`spec.md`](../spec.md) | **Branch**: `004-registration-requests`

## Endpoints Summary

All routes are defined in `routes/web.php` and protected by `hospital.jwt` authentication middleware.

| Method | Endpoint | Route Name | Access Role | Description |
|--------|----------|------------|-------------|-------------|
| `GET` | `/requests` | `requests.index` | Clinic Nurse, Admin | Incoming referral queue |
| `GET` | `/requests/my` | `requests.my` | Ward Nurse | Submitting nurse's personal requests |
| `GET` | `/requests/create` | `requests.create` | Ward Nurse, Admin | Patient search & referral form screen |
| `GET` | `/requests/search-patient` | `requests.search_patient` | Ward Nurse, Admin | Patient lookup API (JSON) |
| `POST` | `/requests` | `requests.store` | Ward Nurse, Admin | Submit new referral request |
| `GET` | `/requests/{request}` | `requests.show` | Clinic Nurse, Admin | Detailed referral & ward visit screen |
| `POST` | `/requests/{request}/visit` | `requests.visit` | Clinic Nurse, Admin | Start ward visit (`VISIT_IN_PROGRESS`) |
| `POST` | `/requests/{request}/accept` | `requests.accept` | Clinic Nurse, Admin | Accept referral & create registration |
| `POST` | `/requests/{request}/ineligible` | `requests.ineligible` | Clinic Nurse, Admin | Decline referral with justification |
| `POST` | `/requests/{request}/cancel` | `requests.cancel` | Ward Nurse, Admin | Cancel pending referral request |

---

## Endpoint Specifications

### 1. Patient Lookup (`GET /requests/search-patient`)

- **Query Parameters**:
  - `query` (string, required): National ID Number or Patient Full Name.
- **Response `200 OK` (JSON)**:
  ```json
  {
    "found": true,
    "patient": {
      "code": "3fa85f64-5717-4562-b3fc-2c963f66afa6",
      "id_number": "401234567",
      "full_name": "أحمد محمد علي",
      "gender": "MALE",
      "dob": "1985-04-12",
      "phone": "0599123456"
    },
    "admission": {
      "ward_name": "قسم الجراحة رجال",
      "attending_doctor": "د. محمود الخالدي",
      "admission_date": "2026-09-20"
    },
    "has_pending_request": false,
    "has_active_registration": false
  }
  ```

---

### 2. Submit Referral Request (`POST /requests`)

- **Form Payload**:
  - `patient_code` (UUID, required): Selected `MR_PAT_CODE`.
  - `attending_nurse_code` (UUID, required): Referring ward employee (`PER_EMP_CODE`).
  - `notes` (text, optional): Referral reason and clinical ward notes.
- **Validation Errors `422 Unprocessable Entity`**:
  ```json
  {
    "message": "بيانات غير صالحة.",
    "errors": {
      "patient_code": ["المريض لديه طلب تسجيل نشط حالياً أو قيد الانتظار."]
    }
  }
  ```
- **Response `302 Redirect`**: Redirects to `requests.my` with success flash message in Arabic ("تم تقديم طلب التحويل للعيادة بنجاح").

---

### 3. Start Ward Visit (`POST /requests/{request}/visit`)

- **URL Parameter**: `request` (UUID `WC_REQ_CODE`).
- **Behavior**: Transitions request status to `VISIT_IN_PROGRESS` and sets `WC_REQ_REVIEWED_BY_CD` to the authenticated clinic employee.
- **Response `302 Redirect`**: Redirects to `requests.show`.

---

### 4. Accept Referral & Create Registration (`POST /requests/{request}/accept`)

- **URL Parameter**: `request` (UUID `WC_REQ_CODE`).
- **Form Payload**:
  - `clinic_nurse_code` (UUID, required): Clinic nurse handling intake (`PER_EMP_CODE`).
- **Behavior**: Executes in-database stored procedure `sp_accept_registration_request`, transitioning request to `ACCEPTED` and creating `ACTIVE` record in `wc_registration_tb`.
- **Response `302 Redirect`**: Redirects to `registrations.index` with success message in Arabic ("تم قبول طلب التسجيل وإنشاء الملف الطبي بنجاح").

---

### 5. Mark Ineligible (`POST /requests/{request}/ineligible`)

- **URL Parameter**: `request` (UUID `WC_REQ_CODE`).
- **Form Payload**:
  - `reason` (text, required): Clinical justification for ineligibility.
- **Behavior**: Sets status to `NOT_ELIGIBLE` and appends reason to `WC_REQ_NOTES`.
- **Response `302 Redirect`**: Redirects to `requests.index` with flash message.
