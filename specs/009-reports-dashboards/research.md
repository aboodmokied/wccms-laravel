# Phase 0 Research: Reporting & Dashboards (Phase 9)

## 1. Database-First Analytical Aggregation Architecture

### Decision
Use Laravel Eloquent query builder / database views (`DB::table(...)` / SQL aggregate functions `COUNT`, `AVG`, `SUM`, `CASE WHEN`) to calculate live KPI metrics directly at the database layer.

### Rationale
- Complies strictly with Constitution Principle II & VI (Database-First Architecture).
- High performance (executes set-based calculations in MySQL/Oracle engine rather than loading large arrays into PHP memory).
- Clean abstraction that ports easily to Oracle stored procedures/views in Phase 10.

### Alternatives Considered
- *In-memory PHP collection processing*: Rejected because loading all historical clinical reviews into PHP memory degrades as dataset grows and violates Database-First principles.
- *Heavy JS Chart Libraries (Chart.js / Highcharts)*: Rejected for initial release to keep layout lightweight and fast while leveraging Bootstrap 5 progress bars, badges, and metric cards.

---

## 2. Patient Clinical History Search & CSV Export

### Decision
Implement a patient history search endpoint at `/reports/history` with a streamed CSV export endpoint (`/reports/history/{patient}/export`).

### Rationale
- Standard Laravel `Symfony\Component\HttpFoundation\StreamedResponse` with `fputcsv()` provides zero-dependency, memory-efficient CSV generation.
- Supports Arabic UTF-8 encoding by outputting UTF-8 BOM (`"\xEF\xBB\xBF"`) so Excel opens Arabic text correctly without encoding distortion.

### Alternatives Considered
- *DOMPDF / TCPDF*: Heavy memory overhead and complex Arabic font embedding issues.
- *JavaScript client-side table export*: Incomplete for multi-page/multi-registration timelines.

---

## 3. Date Range Filtering Strategy

### Decision
Default report queries to "All Time" (`all`), while providing quick-filter query parameters (`?period=30days`, `?period=ytd`, `?period=all`).

### Rationale
- User choice from specification clarification.
- Provides immediate executive snapshot on load without requiring user interaction, while enabling quick period filtering.

### Alternatives Considered
- *Mandatory date range pickers*: Rejected per user clarification choice.
