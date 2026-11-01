# دليل نقل WCCMS إلى Oracle

النظام يتصل بقاعدة Oracle نفسها التي فيها نظام المستشفى، ويستخدم جدولي المرضى والموظفين الموجودين مباشرة. لا migrations على الإنتاج، ولا views، ولا نسخ من بيانات المستشفى. المفاتيح الأجنبية حقيقية وتشير إلى جداول المستشفى.

نفّذ الخطوات بالترتيب. كل خطوة تنتهي بـ **تحقق**؛ لا تنتقل للتي بعدها قبل أن ينجح.

| الملف | ما فيه |
| --- | --- |
| `wccms_oracle_schema.sql` | المستخدم WCCMS، الجداول الـ 20، المفاتيح الأجنبية، الفهارس، CHECK، البيانات الأساسية |
| `wccms_oracle_procedures.sql` | النوع `WC_CODE_LIST`، الدوال، والـ procedures الأربعة بـ PL/SQL |
| `config/hospital.php` | أسماء جدولي المستشفى وخريطة أسماء أعمدتهما |
| `app/Console/Commands/MapHospitalColumns.php` | الأمر الذي يغيّر أسماء أعمدة المستشفى في كل الكود |
| `app/Services/ClinicalWorkflows/OracleClinicalWorkflowGateway.php` | استدعاء الـ procedures من Laravel |

---

## الصورة الكاملة

```
مخطط المستشفى (HIS مثلاً) — موجود، لا نغيّره          مخطط WCCMS — جديد، ينشئه wccms_oracle_schema.sql
┌───────────────────────────┐                      ┌──────────────────────────────────────────────┐
│ جدول المرضى               │◄── FK (2) ───────────│ WC_REQUEST_REG_TB, WC_REGISTRATION_TB         │
│ جدول الموظفين             │◄── FK (12) ──────────│ PER_USERS_TB.PER_USER_EMP_CD + 11 عموداً آخر  │
└───────────────────────────┘                      │ + بقية جداول WC_* (20 جدولاً بالمجموع)        │
   WCCMS يقرأ فقط (SELECT, REFERENCES)              │ + 4 procedures + 4 دوال + نوع واحد            │
                                                   └──────────────────────────────────────────────┘
```

- **جداول المستشفى (2):** المرضى والموظفون. الـ Models `MrPatient` و `PerEmployee` و `Employee` تقرأ منها مباشرة. WCCMS لا يكتب فيها.
- **جداول WCCMS (20):** `PER_USERS_TB` وجداول `WC_*` الـ 19. تُنشأ بالسكربت.
- **ربط المستخدم بالموظف:** صار عموداً في جدولنا `PER_USERS_TB.PER_USER_EMP_CD` (UNIQUE + FK إلى جدول موظفي المستشفى)، بدل عمود `PER_EMP_USER_CD` الذي كان على جدول الموظفين. لذلك لا يحتاج النظام أي تعديل على جدول المستشفى.

> تنبيه لفريق المستشفى: المفاتيح الأجنبية الحقيقية تعني أن نظام المستشفى **لن يستطيع حذف** مريض أو موظف له سجلات في العيادة (ORA-02292)، ولن يستطيع تغيير قيمة مفتاحه. يجب أن يعرفوا ذلك قبل التشغيل.

---

## الخطوة 1: أخذ أسماء جداول وأعمدة المستشفى

نفّذ هذه الاستعلامات على قاعدة المستشفى بمستخدم يرى جداولها.

**1.1 اسم المخطط والجدولين:**

```sql
SELECT owner, table_name FROM all_tables
WHERE table_name LIKE '%PAT%' OR table_name LIKE '%EMP%'
ORDER BY owner, table_name;
```

**1.2 المفتاح الأساسي لكل جدول ونوعه** (غيّر `HIS` و `PATIENTS` للقيم الحقيقية، وكرّر لجدول الموظفين):

```sql
SELECT cc.column_name, c.constraint_type, tc.data_type, tc.data_length, tc.data_precision, tc.data_scale, tc.char_used
FROM all_constraints c
JOIN all_cons_columns cc ON cc.owner = c.owner AND cc.constraint_name = c.constraint_name
JOIN all_tab_columns tc ON tc.owner = cc.owner AND tc.table_name = cc.table_name AND tc.column_name = cc.column_name
WHERE c.owner = 'HIS' AND c.table_name = 'PATIENTS' AND c.constraint_type IN ('P', 'U');
```

إذا لم يُرجع شيئاً فالجدول بلا PRIMARY KEY ولا UNIQUE، ولا يمكن إنشاء مفتاح أجنبي إليه. اطلب من DBA المستشفى إضافة واحد قبل المتابعة.

**1.3 كل الأعمدة:**

```sql
SELECT column_name, data_type, data_length, nullable
FROM all_tab_columns
WHERE owner = 'HIS' AND table_name = 'PATIENTS'
ORDER BY column_id;
```

**1.4 املأ هذا الجدول.** هو المرجع لكل ما بعده.

| المطلوب | القيمة عندكم |
| --- | --- |
| `{{HOSPITAL_SCHEMA}}` | |
| `{{PATIENTS_TABLE}}` / `{{PATIENTS_PK}}` / `{{PATIENTS_PK_TYPE}}` | |
| `{{EMPLOYEES_TABLE}}` / `{{EMPLOYEES_PK}}` / `{{EMPLOYEES_PK_TYPE}}` | |

| عمود WCCMS | الاستخدام | العمود في المستشفى |
| --- | --- | --- |
| `MR_PAT_CODE` | مطلوب (المفتاح) | |
| `MR_PAT_ID_NO` | مطلوب (البحث برقم الهوية) | |
| `MR_PAT_FULL_NAME` | مطلوب | |
| `MR_PAT_NATIONAL_ID` | مطلوب في البحث، انظر 2.4 | |
| `MR_PAT_HOSPITAL_PATIENT_ID` | مطلوب في البحث، انظر 2.4 | |
| `MR_PAT_PHONE`, `MR_PAT_GENDER`, `MR_PAT_DOB` | عرض فقط | |
| `MR_PAT_SUPERVISING_DOCTOR_CD` | عرض فقط: رمز الطبيب في جدول الموظفين، ويُعرض اسمه منه في شاشة الطلب | يجب أن يكون من نفس نوع مفتاح جدول الموظفين |
| `MR_PAT_HOSPITAL_DEPARTMENT`, `MR_PAT_HOSPITAL_ADMISSION_DATE` | عرض فقط (بيانات الدخول في شاشة الطلب) | |
| `PER_EMP_CODE` | مطلوب (المفتاح) | |
| `PER_EMP_FULL_NAME` | مطلوب | |
| `PER_EMP_JOB_TITLE`, `PER_EMP_IS_ACTIVE` | عرض فقط | |

**تحقق:** كل عمود "مطلوب" له مقابل حقيقي، والجدولان لهما PRIMARY KEY أو UNIQUE على المفتاح.

---

## الخطوة 2: ربط Models المستشفى بالجداول الحقيقية

اعمل هذه الخطوة على نسخة التطوير وارفعها إلى git، لا على خادم الإنتاج مباشرة.

**لماذا لا يكفي تعديل الـ Model:** أسماء الأعمدة مثل `MR_PAT_FULL_NAME` و `PER_EMP_CODE` مكتوبة في أكثر من 50 ملفاً (controllers، services، Blade، validation، relations). تغيير `$fillable` في الـ Model وحده يترك هذه الملفات تطلب أعمدة غير موجودة (ORA-00904). لذلك:

- **اسم الجدول** يُضبط من `.env` (يختلف بين جهاز التطوير والإنتاج).
- **أسماء الأعمدة** تُغيَّر مرة واحدة في كل الكود بأمر artisan.

### 2.1 اسم الجدول (في `.env` الإنتاج)

```ini
HOSPITAL_PATIENTS_TABLE=HIS.PATIENTS
HOSPITAL_EMPLOYEES_TABLE=HIS.EMPLOYEES
```

الـ Models (`MrPatient`، `PerEmployee`، `Employee`) تقرأ الجدول من `config('hospital.tables.*')` في `getTable()`. لا تكتب `$table` فيها. على جهاز التطوير يبقى المتغيران فارغين فتُستخدم `mr_patient_tb` و `per_employee_tb` التي ينشئها الـ migration.

### 2.2 أسماء الأعمدة (في `config/hospital.php`)

اكتب اسم العمود الحقيقي مقابل كل عمود مختلف، واترك `null` لما اسمه مطابق:

```php
'columns' => [
    'MR_PAT_CODE' => 'PATIENT_ID',
    'MR_PAT_ID_NO' => 'NATIONAL_NO',
    'MR_PAT_FULL_NAME' => 'PATIENT_NAME',
    // ...
    'PER_EMP_CODE' => 'EMP_ID',
    'PER_EMP_FULL_NAME' => 'EMP_NAME',
],
```

اكتب الأسماء بأحرف كبيرة كما تظهر في `all_tab_columns`.

### 2.3 تطبيق الأسماء على الكود

```bash
php artisan wccms:map-hospital-columns --dry-run   # يعرض الملفات وعدد الاستبدالات بدون كتابة
php artisan wccms:map-hospital-columns             # يكتب التغيير
php artisan test --compact
```

الأمر يستبدل الكلمة كاملة فقط (`MR_PAT_ID_NO` لا تمس `MR_PAT_ID_NO_X`) في `app/` و `resources/views/` و `routes/` و `database/` و `tests/`. يشمل ذلك الـ migration المحلي، فتبقى قاعدة التطوير مطابقة. لا يلمس `config/hospital.php`، وتشغيله مرة ثانية لا يغيّر شيئاً.

الأمر يرفض:
- اسماً غير صالح في Oracle.
- عمودين من WCCMS إلى عمود واحد في المستشفى.
- اسماً هو نفسه اسم عمود آخر من أعمدة WCCMS.

**تحقق:** `php artisan test` كله ناجح، ثم `git diff --stat` يعرض الملفات المتغيرة. راجعها وارفعها إلى git.

### 2.4 عمود غير موجود في المستشفى

- **عمود "عرض فقط"** (الهاتف، الجنس، الطبيب...): اتركه `null`. قراءته تعطي `null` والشاشة تعرضه فارغاً.
- **`MR_PAT_NATIONAL_ID` أو `MR_PAT_HOSPITAL_PATIENT_ID`:** يُستخدمان فقط كشرط بحث إضافي. إذا لم يوجدا، احذف سطور `orWhere` الخاصة بهما بعد تشغيل الأمر:
  - `app/Services/ReportingService.php` (قرب السطر 181): `->orWhere('MR_PAT_NATIONAL_ID', $search)`
  - `app/Services/ScheduledReviewService.php` (السطران 57 و 59): `orWhere` على `MR_PAT_NATIONAL_ID` و `MR_PAT_HOSPITAL_PATIENT_ID`

  للتأكد أنه لم يبقَ شرط على عمود غير موجود:

  ```bash
  grep -rn "MR_PAT_NATIONAL_ID\|MR_PAT_HOSPITAL_PATIENT_ID" app | grep -i "where\|orderBy"
  ```

### 2.5 المفتاح ليس UUID

إذا كان مفتاح المستشفى رقماً، لا تعديل في الكود: قواعد التحقق صارت `string` + `exists` على الـ Model، بدل `uuid`.

**تحقق:** في tinker على الإنتاج بعد الخطوة 6:

```php
App\Models\MrPatient::first()?->MR_PAT_FULL_NAME;   // باسمك الجديد إن غيّرته
App\Models\PerEmployee::count();
```

---

## الخطوة 3: Models جداول WCCMS

لا تحتاج تعديلاً. الـ Models مكتوب فيها `$table = 'wc_registration_tb'` بأحرف صغيرة، والـ driver يحوّل كل اسم إلى أحرف كبيرة (`"WC_REGISTRATION_TB"`)، وهو الاسم الذي ينشئه السكربت.

شرطان فقط:

1. التطبيق يتصل بالمستخدم **WCCMS** نفسه (`DB_USERNAME=WCCMS`)، فتُوجد الجداول في مخططه الافتراضي.
2. لا تُنشأ الجداول بأسماء بين علامتي تنصيص أو بأحرف صغيرة (`CREATE TABLE "wc_x"`)؛ السكربت ينشئها صحيحة.

**تحقق:** في tinker:

```php
App\Models\WcAssessmentChoice::count();   // 46
App\Models\WcDepartment::count();         // 13
```

---

## الخطوة 4: الـ aliases ومشكلة الأحرف الكبيرة

**المشكلة:** Oracle يُرجع كل اسم غير مقتبس بأحرف كبيرة. الـ driver يحوّل أيضاً كل اسم يكتبه Laravel إلى أحرف كبيرة، والـ aliases منها. لذلك `select('X as nurse_name')` يرجع المفتاح `NURSE_NAME`، والكود يقرأ `$row->nurse_name` فيجده `null` دون أي خطأ.

**ما تم تعديله في الكود (جاهز):**

| الملف | التعديل |
| --- | --- |
| `app/Services/ReportingService.php` | `DB::raw('COUNT(*) as "total"')` بين علامتي تنصيص؛ أسماء `select([... as X])` صارت بأحرف كبيرة (`NURSE_NAME`، `PATIENT_NAME`، ...) وقراءتها كذلك؛ جدولا المستشفى من الـ Model |
| `app/Http/Controllers/PatientMedicalRecordController.php` | `withCount(['diagnoses as DIAGNOSES_COUNT', ...])` |
| `resources/views/patients/partials/registration_record.blade.php` | `$registration->DIAGNOSES_COUNT` و `DAILY_REVIEWS_COUNT` و `SCHEDULED_REVIEWS_COUNT` |
| `app/Services/Reports/MonthlyCaseReportService.php` | `withCount('woundPhotos as WOUND_PHOTOS_COUNT')` و `sum('WOUND_PHOTOS_COUNT')` |

**القاعدة لأي كود جديد:**

- داخل `DB::raw` / `selectRaw`: ضع الـ alias بين علامتي تنصيص مزدوجتين: `DB::raw('COUNT(*) as "total"')`.
- في `select('col as ALIAS')` و `withCount('rel as REL_COUNT')`: اكتب الـ alias بأحرف كبيرة واقرأه كذلك. لا تقتبسه هنا، لأن Laravel يقتبسه بنفسه.
- `count()` و `exists()` و `paginate()` تعمل كما هي.

**للبحث عن حالات جديدة:**

```bash
grep -rnE "DB::raw\(|selectRaw\(" app | grep -E " as [a-z]"
grep -rnE "' as [a-z_]+'" app
grep -rn "withCount(" app
grep -rn "_count\b" resources/views app
```

**الإعداد الذي يجعل كل هذا يعمل** (`config/oracle.php`، الخطوة 5.3): `PDO::ATTR_CASE => PDO::CASE_NATURAL`. القيمة الافتراضية للـ driver هي `CASE_LOWER`، وهي تجعل `$patient->MR_PAT_FULL_NAME` فارغاً في كل النظام.

**تنبيه آخر:** `LIKE` في Oracle حساس لحالة الأحرف اللاتينية (`'%ahmed%'` لا يطابق `Ahmed`). العربية لا تتأثر.

---

## الخطوة 5: تجهيز الخادم والاتصال

**5.1 Oracle Instant Client و oci8:**
- ثبّت Instant Client (19 أو أحدث) وأضف مجلده إلى `PATH` (Windows) أو `LD_LIBRARY_PATH` (Linux).
- على Windows: `extension=oci8_19` في `php.ini`. على Linux: `pecl install oci8`.
- أعد تشغيل الخادم.

**تحقق:** `php -m` يُظهر `oci8`.

**5.2 الحزمة:**

```bash
composer require yajra/laravel-oci8:^13
php artisan vendor:publish --tag=oracle
```

**5.3 `config/oracle.php`:** أضف `options` و `TIME_ZONE`:

```php
'options' => [
    PDO::ATTR_CASE => PDO::CASE_NATURAL,
],
'sessionVars' => [
    // ... الموجود كما هو (NLS_DATE_FORMAT, NLS_NUMERIC_CHARACTERS ...)
    'TIME_ZONE' => '+00:00',
],
```

**5.4 `.env` الإنتاج:**

```ini
DB_CONNECTION=oracle
DB_HOST=<خادم Oracle>
DB_PORT=1521
DB_SERVICE_NAME=<service name>
DB_DATABASE=
DB_USERNAME=WCCMS
DB_PASSWORD=<{{WCCMS_PASSWORD}}>
DB_CHARSET=AL32UTF8
DB_SERVER_VERSION=12c
ORA_MAX_NAME_LEN=128

HOSPITAL_PATIENTS_TABLE=HIS.PATIENTS
HOSPITAL_EMPLOYEES_TABLE=HIS.EMPLOYEES

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

`SESSION_DRIVER` و `CACHE_STORE` و `QUEUE_CONNECTION` بهذه القيم حتى لا يحتاج Laravel جداوله (`sessions`، `cache`، `jobs`) في Oracle.

**تحقق (بعد الخطوة 6):**

```bash
php artisan config:clear
php artisan tinker
>>> DB::select('select 1 as x, sessiontimezone as tz from dual');
```

المفاتيح `X` و `TZ` بأحرف كبيرة، و `TZ` = `+00:00`. إذا ظهر `x` صغيراً فـ 5.3 لم تُطبَّق.

---

## الخطوة 6: تنفيذ سكربتات SQL

> **لا تشغّل `php artisan migrate` على الإنتاج أبداً**، ولا `php artisan db:seed`. الجداول والبيانات الأساسية كلها في السكربت. الـ migration للتطوير والاختبارات فقط.

**6.1 استبدل الـ placeholders** في الملفين بقيم الخطوة 1.4 (Find & Replace على الملف كاملاً). شرح كل placeholder في رأس `wccms_oracle_schema.sql`.

**تحقق:** هذا الأمر لا يُرجع شيئاً:

```bash
grep -nE "\{\{[A-Z_]+\}\}" wccms_oracle_schema.sql wccms_oracle_procedures.sql
```

**6.2 الجداول** (DBA، بـ SQL*Plus أو SQLcl، مع `NLS_LANG=AMERICAN_AMERICA.AL32UTF8` حتى يُحفظ النص العربي سليماً):

```sql
@wccms_oracle_schema.sql
```

الملف يتوقف عند أول خطأ. إذا رفض Oracle سطري `GRANT ... ON {{HOSPITAL_SCHEMA}}` (ORA-01031)، فيجب أن ينفّذهما مالك مخطط المستشفى، ثم يُكمل الباقي.

**6.3 الـ procedures:**

```sql
@wccms_oracle_procedures.sql
```

**تحقق** (آخر الملفين يطبع هذه النتائج):

```sql
SELECT COUNT(*) FROM all_tables WHERE owner = 'WCCMS';                                  -- 20
SELECT object_name, object_type FROM all_objects WHERE owner = 'WCCMS' AND status = 'INVALID';  -- لا صفوف
SELECT COUNT(*) FROM WCCMS.WC_ASSESSMENT_CHOICE_TB;                                     -- 46
```

إذا ظهر كائن INVALID: `SHOW ERRORS PROCEDURE WCCMS.<الاسم>` يعرض السطر والسبب.

---

## الخطوة 7: الدخول والصلاحيات (لا يوجد مدير أول)

لا تحتاج إدخال أي مستخدم يدوياً.
- **من يدخل يقرّره نظام المستشفى.** الدخول يتم بـ JWT منه، و`PER_USER_USERNAME` يساوي قيمة `sub` في التوكن.
- **الحساب يُنشأ عند أول دخول.** يُنشأ في `PER_USERS_TB` مفعّلاً مباشرة، بلا خطوة موافقة.
- **كل من يدخل يفتح صفحة الإعدادات (`/admin`)، ومنها شاشة المستخدمين.** فيها يمكن:
  - ربط أي مستخدم بموظفه في جدول المستشفى. الربط يُكتب في `PER_USERS_TB.PER_USER_EMP_CD` فقط.
  - إيقاف أي حساب.

إيقاف حساب من شاشة المستخدمين يمنعه من دخول WCCMS حتى لو سمح له نظام المستشفى. يستطيع أي مستخدم آخر إعادة تفعيله من الشاشة نفسها.

**تحقق:** أول دخول لأي ممرض يفتح لوحة التحكم مباشرة، ويظهر له رابط «الإدارة» في القائمة، ويظهر صفّه في `PER_USERS_TB` بقيمة `PER_USER_IS_ACTIVE = 1`.

---

## الخطوة 8: اختبار المسارات وتشغيل النظام

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

نفّذ بالترتيب، وراقب `storage/logs/laravel.log`:

- [ ] دخول مستخدم جديد يُنشئ حساباً مفعّلاً في `PER_USERS_TB` ويفتح لوحة التحكم مباشرة.
- [ ] ربطه بموظف من شاشة المستخدمين، فتظهر القيمة في `PER_USER_EMP_CD`.
- [ ] البحث عن مريض برقم هويته يعرض بياناته من جدول المستشفى.
- [ ] البحث برقم غير موجود يعرض «لم يتم العثور على المريض»، ولا يُضاف أي صف (النظام لا ينشئ مرضى).
- [ ] طلب تحويل، ثم قبوله بجرحين وصورة.
- [ ] مراجعة يومية، ثم إرسال النموذج نفسه مرة ثانية: لا يتكرر الصف.
- [ ] مراجعة مجدولة، وإضافة جرح لتسجيل نشط.
- [ ] ملاحظات طويلة (أكثر من 2100 حرف عربي) في الطلب والمراجعة تُحفظ كاملة.
- [ ] لوحة التحكم تعرض أرقاماً وأسماء (لا أصفار ولا «غير محدد» في كل مكان).
- [ ] صفحة المريض تعرض عدد الجروح والمراجعات والمواعيد.
- [ ] تصدير التقرير الشهري يعمل.
- [ ] قبول طلب مقبول سابقاً يعرض رسالة خطأ ولا يترك صفوفاً ناقصة.

**تحقق:** كل البنود ناجحة، والسجل خالٍ من `ORA-`.

خارج القاعدة:
- صور الجروح في `storage/app/public/wound_photos`؛ أضفها إلى النسخ الاحتياطي مع مخطط WCCMS.
- لا تشغّل `php artisan storage:link`، واحذف `public/storage` إن وُجد (الصور تُعرض لمن سجّل الدخول فقط).

---

## أخطاء شائعة

| الخطأ | السبب | الحل |
| --- | --- | --- |
| كل الحقول فارغة في الشاشات بلا خطأ | `ATTR_CASE` ما زال `CASE_LOWER` | الخطوة 5.3 ثم `php artisan config:clear` |
| رقم أو اسم فارغ في جزء واحد (لوحة، عدّاد) | alias بأحرف صغيرة | الخطوة 4 |
| `ORA-00904: invalid identifier` | عمود باسم WCCMS لا يوجد في جدول المستشفى | الخطوة 2.2 و 2.3، أو 2.4 |
| `ORA-00942: table or view does not exist` | اسم جدول المستشفى خطأ في `.env`، أو لا يوجد GRANT SELECT | `HOSPITAL_*_TABLE` بالمخطط، والخطوة 6.2 |
| `ORA-02270` أثناء السكربت | مفتاح المستشفى بلا PRIMARY KEY/UNIQUE | الخطوة 1.2 |
| `ORA-01031` على GRANT أو FK | لا صلاحية REFERENCES على جدول المستشفى | ينفّذ مالك مخطط المستشفى سطري GRANT |
| `ORA-02291: parent key not found` | رمز موظف أو مريض غير موجود في المستشفى | تحقق من الرمز المختار في النموذج |
| `ORA-02292` عند الحذف في نظام المستشفى | سجل العيادة يشير إلى هذا المريض أو الموظف | مقصود؛ انظر التنبيه أعلى الدليل |
| `ORA-20001: <رمز>` في السجل | تحقق داخل procedure رفض البيانات (مثل `REQUEST_ALREADY_DECIDED`، `INVALID_EMPLOYEE`) | الرمز يقول السبب؛ العملية كاملة أُلغيت |
| `PLS-00201: identifier 'JSON_OBJECT_T'` | Oracle أقدم من 12.2 | يلزم 12.2 أو أحدث |
| نص عربي يظهر `???` | `NLS_LANG` ليس AL32UTF8 عند تشغيل السكربت، أو الملف لم يُحفظ UTF-8 | أعد التشغيل بالإعداد الصحيح |
| «بيانات التقييم أطول من المسموح» | JSON المراجعة أكبر من 32 KB (حد معامل PL/SQL) | ملاحظات أقصر؛ لا يحدث في الاستخدام العادي |
