# Interface Contract: Registration & Diagnosis API

**Feature Branch**: `005-registration-diagnosis`  
**Date**: 2026-09-26  

---

## Web Routes & HTTP Endpoints

| Method | URI Path | Controller Method | Description | Auth & Middleware |
|---|---|---|---|---|
| `GET` | `/registrations` | `RegistrationController@index` | List registered patients with status filter tabs and search | `hospital.jwt` |
| `GET` | `/registrations/{registration}` | `RegistrationController@show` | Display registration details, procedure, PMH, & wound diagnoses | `hospital.jwt` |
| `PUT` | `/registrations/{registration}` | `RegistrationController@update` | Update initial procedure (`WC_REG_PROCEDURE_CD`) and PMH notes | `hospital.jwt` |
| `POST` | `/registrations/{registration}/diagnoses` | `RegistrationController@storeDiagnosis` | Add initial wound diagnosis record (`WC_REG_DIAGNOSIS_TB`) | `hospital.jwt` |
| `POST` | `/registrations/{registration}/discharge` | `RegistrationController@discharge` | Discharge patient (`WC_REG_STATUS = 'DISCHARGED'`) | `hospital.jwt` |

---

## Payload Specifications

### 1. Update Registration (`PUT /registrations/{registration}`)
```json
{
  "procedure_code": "3fa85f64-5717-4562-b3fc-2c963f66afa6",
  "pmh_notes": "تاريخ مرضي سابق: السكري من النوع الثاني + ضغط الدم"
}
```

### 2. Store Wound Diagnosis (`POST /registrations/{registration}/diagnoses`)
```json
{
  "wound_type_code": "7a12bc90-1111-2222-3333-444455556666",
  "diagnosis_code": "8b23cd01-2222-3333-4444-555566667777",
  "dressing_code": "9c34de12-3333-4444-5555-666677778888",
  "medication_code": "0d45ef23-4444-5555-6666-777788889999",
  "clinical_nurse_code": "1e56fa34-5555-6666-7777-888899990000"
}
```

### 3. Discharge Registration (`POST /registrations/{registration}/discharge`)
```json
{
  "discharging_nurse_code": "1e56fa34-5555-6666-7777-888899990000"
}
```
