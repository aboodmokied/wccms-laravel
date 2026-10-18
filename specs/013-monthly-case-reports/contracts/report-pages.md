# Contract: Report Pages and Export

All routes sit inside the existing `hospital.jwt` middleware group (every authenticated user; clarification Q8).
Responses are Arabic, right-to-left, in the `layouts.app` shell.

| Method | Path | Name | Purpose |
|---|---|---|---|
| GET | `/reports/monthly` | `reports.monthly.cases` | Case list for one month |
| GET | `/reports/monthly/summary` | `reports.monthly.summary` | Summary over a month range |
| GET | `/reports/monthly/readmissions` | `reports.monthly.readmissions` | Readmissions over a month range |
| GET | `/reports/monthly/export` | `reports.monthly.export` | `.xlsx` download |

## Query parameters

| Parameter | Pages | Validation | Arabic error |
|---|---|---|---|
| `month` | cases | `date_format:Y-m` | صيغة الشهر غير صحيحة. |
| `from`, `to` | summary, readmissions, export | `date_format:Y-m`; `to` `after_or_equal:from` | شهر النهاية يجب أن يكون بعد شهر البداية أو مساوياً له. |
| `departments[]` | all | array of `exists:wc_department_tb,WC_DEPT_CODE` | القسم المحدد غير موجود. |

Invalid input redirects back with the message; the page never shows a server error.

## Pages

- A tab bar links the three pages and keeps the department filter.
- **Cases**: month picker, department multi-select, "تصدير Excel" button (exports `from = to = month`), table with
  the columns of FR-004 in workbook order, empty state `لا توجد حالات في هذا الشهر.`
- **Summary**: from/to pickers, department multi-select, export button, per-month bars, gender pie, per-diagnosis bars,
  totals.
- **Readmissions**: from/to pickers, department multi-select, table with case columns plus "التسجيل السابق" and
  "نتيجة التسجيل السابق", empty state `لا توجد حالات إعادة دخول في هذه الفترة.`
- All text in cells is escaped; narrow screens scroll the table horizontally inside its card.

## Export

- File name: `monthly-cases-YYYY-MM.xlsx`, or `monthly-cases-YYYY-MM-to-YYYY-MM.xlsx` for a range.
- Sheets, all right-to-left: `الحالات` (one row per case in the range, same columns as the page), `الملخص` (the
  three count tables with totals), `إعادة الدخول`.
- Date columns are date cells; counts are numeric cells; the header row is bold.
- After the file is written, one `wc_report_export_tb` row is inserted (FR-013).

## Navigation

The sidebar's "السجلات والتقارير" section gains `التقارير الشهرية` → `reports.monthly.cases`, active on
`reports.monthly.*`. The existing `التقارير` entry (patient history) stays.
