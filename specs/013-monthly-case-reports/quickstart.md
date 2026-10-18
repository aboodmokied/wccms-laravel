# Quickstart: Monthly Case Reports

## Prerequisites

- WAMP running (MySQL 8.4 on `127.0.0.1:3306`, default engine InnoDB); `.env` points at `wccms`.
- `composer install` after `openspout/openspout` is added; `npm run build`.

## Automated checks

```bash
php artisan test --compact tests/Feature/MonthlyCaseReportTest.php
php artisan test --compact tests/Feature/MonthlyCaseSummaryTest.php
php artisan test --compact tests/Feature/ReadmissionReportTest.php
php artisan test --compact tests/Feature/MonthlyReportExportTest.php
php artisan test --compact tests/Feature/ClinicWorkbookListsSeederTest.php
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

The same report tests on MySQL, against a scratch database:

```bash
DB_CONNECTION=mysql DB_DATABASE=wccms_test php artisan migrate:fresh --env=testing
DB_CONNECTION=mysql DB_DATABASE=wccms_test php artisan test --compact tests/Feature/MonthlyCaseSummaryTest.php
```

Expected (SC-001), synthetic dataset, November–December 2025: 125 / 79 cases; 82 female / 122 male; 51 bedsore
PEU, 11 Burn, 69 DF, 55 Inf Wound, 7 Row Area, 11 SSI (total 204); 6 readmissions.

## Manual check

1. `php artisan migrate --seed` then `php artisan db:seed --class=LongitudinalPatientSeeder`.
2. `php artisan serve`, sign in at `http://127.0.0.1:8000/mock-hospital-auth` as `nurse_sarah` (NURSE).
3. Sidebar → التقارير الشهرية: choose the current month; every seeded registration of that month is listed once.
4. Select two departments: only their cases remain.
5. Summary tab: last 12 months by default; month bars include zero months; counts match the case list.
6. Readmissions tab: the longitudinal demo patient's later registrations appear with their previous date and outcome.
7. Export: open the file in Excel — three right-to-left sheets, Arabic readable, dates as dates. One new row in
   `wc_report_export_tb`.
8. Narrow the browser to phone width: tables scroll inside their cards; filters stack.
