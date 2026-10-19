# Quickstart: Clinical Terms in English

## Automated checks

```bash
php artisan test --compact tests/Feature/ClinicalTermsDisplayTest.php
php artisan test --compact tests/Feature/AssessmentChoiceAdminTest.php
php artisan test --compact tests/Feature/MonthlyCaseReportTest.php tests/Feature/MonthlyReportExportTest.php
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run build
```

## Manual check

1. `php artisan migrate` on the WAMP database: every assessment choice has an English name.
2. Sign in as `nurse_sarah`; open a pending request and accept it: medical labels and choices in English,
   department / nurse / follow-up labels in Arabic; leave Exudate Amount empty → `حقل Exudate Amount مطلوب.`
3. Daily review form: outcome options in English.
4. Registration page, review timeline, patient record, patient history report and its CSV: medical terms in English.
5. التقارير الشهرية: headers as the workbook, `Male`/`Female`, `Nov -2025`, English Comment; export sheets
   `CASES`, `CASES-A`, `ReAdmission`.
6. Sign in as `admin_user`: edit a choice's English name; it appears on the forms.
7. Check that English terms read left-to-right inside the Arabic pages (desktop and phone width).
