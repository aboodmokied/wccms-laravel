# Feature Specification: Reporting & Dashboards (Phase 9)

**Feature Branch**: `009-reports-dashboards`

**Created**: 2026-10-04

**Status**: Approved

**Input**: User description: "/speckit-specify phase 9"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Real-Time Clinic Executive Dashboard (Priority: P1)

As a clinic manager or physician, I want to view a real-time executive dashboard containing live aggregated metrics (active patient count, average treatment duration, outcome distribution, missed appointment rates, and recent activities) so that I can immediately gauge clinic performance and workload without reading raw database records.

**Why this priority**: Essential operational overview for clinicians and administration to monitor active care metrics and clinic efficiency.

**Independent Test**: Log into `/dashboard`, observe live card numbers computed dynamically from registered patients and reviews, and verify metrics match database state.

**Acceptance Scenarios**:

1. **Given** active patient registrations and completed/scheduled reviews in the system, **When** a user navigates to `/dashboard`, **Then** the system displays 4 primary KPI cards: Active Patients, Average Treatment Duration (days), Outcome Distribution Summary, and Missed Appointment Rate (%), along with a list of recent clinical activities.
2. **Given** dynamic changes in patient status (e.g., patient discharged or new appointment missed), **When** refreshing `/dashboard`, **Then** all metrics update accurately reflecting current database records.

---

### User Story 2 - Comprehensive Patient Clinical History Report (Priority: P2)

As a nurse or physician, I want to search for a specific patient by clinical ID or MRN in the clinical reports section, viewing their complete historical timeline of registrations, daily wound assessments, dressings, medications, and scheduled reviews in a clean, unified view, with the ability to export the summary to CSV.

**Why this priority**: Clinicians require a single consolidated historical timeline per patient to evaluate long-term wound healing progress and export clinical data.

**Independent Test**: Navigate to `/reports/history`, search for a patient by clinical ID, verify full clinical history, wound progress timeline, and click "Export CSV" to download the patient's record summary.

**Acceptance Scenarios**:

1. **Given** a patient with multiple past registrations and review assessments, **When** a user searches by patient clinical number, **Then** the system renders a comprehensive clinical timeline with registration dates, wound progression status (improved, worsened, deceased), applied dressings, and scheduled appointment compliance.
2. **Given** a patient report on screen, **When** clicking the export action button, **Then** the system downloads a structured CSV file containing the patient's demographics, registration history, and clinical review metrics.
3. **Given** a search for a non-existent patient ID, **When** submitting the search query, **Then** the system displays a clear "No patient records found" notification with search tips.

---

### User Story 3 - Interactive Date Range & Analytical Filtering (Priority: P3)

As an administrator or clinic lead, I want to filter aggregate clinic metrics and reports by preset date ranges (e.g., Last 30 Days, Year to Date, All Time) so that I can compare treatment outcomes and missed appointment trends over specific operational periods.

**Why this priority**: Analytical insights allow management to track historical trends, seasonal workload shifts, and long-term wound healing success rates.

**Independent Test**: Select a date range filter on the reports screen (e.g., Last 30 Days) and verify all metrics and distribution charts recalculate exclusively for that period.

**Acceptance Scenarios**:

1. **Given** clinical data spanning multiple months, **When** a user selects a preset date filter (e.g., "Last 30 Days"), **Then** all summary metrics and outcome percentage charts recalculate based on clinical reviews within that range.

---

### Edge Cases

- What happens when there are zero registrations or reviews in the database? The system MUST display `0` for counts, `0 days` for average duration, `0%` for missed appointment rate, and empty state placeholders without division-by-zero runtime exceptions.
- How does the system handle patients who have never been discharged? Active treatment duration MUST be computed dynamically using the number of days elapsed between registration date and current date (`CURRENT_DATE`).
- What happens if a patient has multiple registrations across different time periods? The history report MUST group clinical events chronologically by registration session while displaying cumulative clinic history.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST calculate and display the total number of currently active patients (`REGISTERED` status).
- **FR-002**: System MUST calculate the average treatment duration in days across discharged and active patient registrations.
- **FR-003**: System MUST calculate and visualize the distribution of clinical review outcomes (Improved + Discharged, Improved + Follow-up, Worsened, Deceased) with counts and percentages.
- **FR-004**: System MUST compute the missed-appointment rate as `(MISSED appointments / total non-pending appointments) * 100`.
- **FR-005**: System MUST provide database views or stored procedures (or repository queries adhering to DB-First architecture) to encapsulate all analytical aggregation logic cleanly.
- **FR-006**: System MUST update `/dashboard` to replace all static placeholder values with live database-driven statistics and recent activity logs.
- **FR-007**: System MUST provide a comprehensive patient history search view at `/reports/history` with a detailed chronological clinical timeline.
- **FR-008**: System MUST support date range filtering defaulting to "All Time" with quick preset filter buttons (Last 30 Days, Year to Date, All Time).
- **FR-009**: System MUST support downloading a structured CSV data export of the patient's clinical history and evaluation summary from `/reports/history`.

### Key Entities *(include if feature involves data)*

- **Clinic KPI Summary**: Aggregated analytical metric structure containing active count, avg duration days, missed appointment percentage, and total evaluations.
- **Outcome Distribution Metric**: Grouping of daily review status assessments (improved/discharged, improved/follow-up, worsened, deceased) with counts and percentage breakdown.
- **Patient Clinical Timeline**: Chronological collection of registration events, daily wound assessments, dressings, medications, and appointment status logs per patient.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Dashboard pages load and render all aggregated KPI metrics in under 1 second.
- **SC-002**: 100% accuracy in aggregated calculations (active count, average duration, missed appointment rate) verified against underlying DB tables.
- **SC-003**: Zero division-by-zero or SQL aggregation runtime exceptions when reporting on empty or sparse datasets.
- **SC-004**: Patient clinical history search retrieves and renders full historical timeline in under 1.5 seconds.
- **SC-005**: CSV export downloads cleanly with correct column headers and formatted patient data.

## Assumptions

- Analytical reporting queries rely on existing tables (`WC_REGISTRATION`, `WC_DAILY_REVIEW`, `WC_SCHEDULED_REVIEW`, `WC_PATIENT_REQUEST`).
- Aggregation queries are structured via database views/procedures or Eloquent DB-First query builders so they can be seamlessly ported to Oracle PL/SQL in Phase 10.
- Modern Bootstrap 5 badges and progress bars will be used for visualization, maintaining light-weight performance without requiring heavy external chart libraries.
