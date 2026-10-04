# Contract Specification: Master Data Administration Endpoints

## Feature Branch: `008-master-data-admin`

---

## 1. Web & API Endpoint Summary

All routes require authentication and `role:ADMIN` middleware.

| Domain | Method | Endpoint | Action | Description |
|---|---|---|---|---|
| **Departments** | `GET` | `/admin/departments` | `DepartmentController@index` | List departments with search & status filter |
| | `POST` | `/admin/departments` | `DepartmentController@store` | Create new department |
| | `PUT` | `/admin/departments/{department}` | `DepartmentController@update` | Update department name |
| | `PATCH` | `/admin/departments/{department}/toggle` | `DepartmentController@toggleActive` | Toggle active status (`1` ↔ `0`) |
| **Past Medical History** | `GET` | `/admin/pmh` | `PmhController@index` | List PMH items |
| | `POST` | `/admin/pmh` | `PmhController@store` | Create new PMH item |
| | `PUT` | `/admin/pmh/{pmh}` | `PmhController@update` | Update PMH name |
| | `PATCH` | `/admin/pmh/{pmh}/toggle` | `PmhController@toggleActive` | Toggle active status |
| **Diagnoses** | `GET` | `/admin/diagnoses` | `DiagnosisController@index` | List diagnoses |
| | `POST` | `/admin/diagnoses` | `DiagnosisController@store` | Create new diagnosis |
| | `PUT` | `/admin/diagnoses/{diagnosis}` | `DiagnosisController@update` | Update diagnosis name |
| | `PATCH` | `/admin/diagnoses/{diagnosis}/toggle` | `DiagnosisController@toggleActive` | Toggle active status |
| **Wound Types** | `GET` | `/admin/wound-types` | `WoundTypeController@index` | List wound types |
| | `POST` | `/admin/wound-types` | `WoundTypeController@store` | Create new wound type |
| | `PUT` | `/admin/wound-types/{woundType}` | `WoundTypeController@update` | Update wound type name |
| | `PATCH` | `/admin/wound-types/{woundType}/toggle` | `WoundTypeController@toggleActive` | Toggle active status |
| **Dressings** | `GET` | `/admin/dressings` | `DressingController@index` | List dressings |
| | `POST` | `/admin/dressings` | `DressingController@store` | Create new dressing |
| | `PUT` | `/admin/dressings/{dressing}` | `DressingController@update` | Update dressing name |
| | `PATCH` | `/admin/dressings/{dressing}/toggle` | `DressingController@toggleActive` | Toggle active status |
| **Medications** | `GET` | `/admin/medications` | `MedicationController@index` | List medications |
| | `POST` | `/admin/medications` | `MedicationController@store` | Create new medication |
| | `PUT` | `/admin/medications/{medication}` | `MedicationController@update` | Update medication name |
| | `PATCH` | `/admin/medications/{medication}/toggle` | `MedicationController@toggleActive` | Toggle active status |
| **Procedures** | `GET` | `/admin/procedures` | `ProcedureController@index` | List procedures |
| | `POST` | `/admin/procedures` | `ProcedureController@store` | Create new procedure |
| | `PUT` | `/admin/procedures/{procedure}` | `ProcedureController@update` | Update procedure name |
| | `PATCH` | `/admin/procedures/{procedure}/toggle` | `ProcedureController@toggleActive` | Toggle active status |

---

## 2. Standard Endpoint Specifications

### 2.1 GET `/admin/{domain}`
Returns the domain master data index view with search filter, pagination, and status metrics.

#### Query Parameters:
- `search` (optional): Filter items by name.
- `status` (optional): Filter by active status (`1` for active, `0` for inactive, `all` for both).

#### Response:
- View: `admin.master_data.index` (or domain-specific Blade view under `resources/views/admin/master_data/`).

---

### 2.2 POST `/admin/{domain}`
Creates a new reference item.

#### Request Body:
```json
{
  "name": "اسم المادة المرجعية الجديدة"
}
```

#### Validation:
- `name`: `required|string|max:255|unique:{table},{PREFIX}_NAME`

#### Response:
- Success: Redirect back with flash message `"تم إضافة العنصر المرجعي بنجاح"`.
- Error: 422 Unprocessable Entity if name is empty or duplicate.

---

### 2.3 PUT `/admin/{domain}/{code}`
Updates the name of an existing reference item.

#### Request Body:
```json
{
  "name": "الاسم المعدل للمادة المرجعية"
}
```

#### Validation:
- `name`: `required|string|max:255|unique:{table},{PREFIX}_NAME,{code},{PREFIX}_CODE`

#### Response:
- Success: Redirect back with flash message `"تم تعديل العنصر المرجعي بنجاح"`.

---

### 2.4 PATCH `/admin/{domain}/{code}/toggle`
Toggles active state between `1` and `0`.

#### Response:
- Success: Redirect back with flash message `"تم تغيير حالة تفعيل العنصر المرجعي بنجاح"`.
