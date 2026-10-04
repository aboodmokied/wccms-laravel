# Interface Contract: Reporting & Dashboards API & Views (Phase 9)

## 1. Executive Dashboard Route

- **URL**: `/dashboard`
- **Method**: `GET`
- **Controller**: `App\Http\Controllers\DashboardController@index`
- **Middleware**: `['hospital.jwt']`
- **Query Parameters**:
  - `period` (optional): `all` (default), `30days`, `ytd`
- **Response**: Renders `resources/views/dashboard.blade.php` populated with `$kpis` and `$recentActivities`.

---

## 2. Patient Clinical Reports Search & History Route

- **URL**: `/reports/history`
- **Method**: `GET`
- **Controller**: `App\Http\Controllers\ReportController@history`
- **Middleware**: `['hospital.jwt']`
- **Query Parameters**:
  - `search` (optional): Patient clinical code (`MR_PAT_CODE`), MRN, national ID, or patient name.
- **Response**: Renders `resources/views/reports/history.blade.php` with search input, patient profile card, and chronological clinical timeline.

---

## 3. Patient Clinical History CSV Export Route

- **URL**: `/reports/history/{patient}/export`
- **Method**: `GET`
- **Controller**: `App\Http\Controllers\ReportController@exportCsv`
- **Middleware**: `['hospital.jwt']`
- **Response Headers**:
  - `Content-Type`: `text/csv; charset=UTF-8`
  - `Content-Disposition`: `attachment; filename="patient-{patient_code}-history.csv"`
- **Response Body**: Streamed UTF-8 BOM CSV containing patient demographics, registration history, evaluation logs, and scheduled review outcomes.
