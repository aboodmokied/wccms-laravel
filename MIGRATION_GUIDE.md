# دليل تشغيل WCCMS على قاعدة بيانات المستشفى (Oracle 11g)

يعمل WCCMS على قاعدة Oracle نفسها التي فيها نظام المستشفى (المخطط `HOS`، Oracle 11g، عدة مستشفيات في القاعدة نفسها). يقرأ جداول المستشفى الحقيقية بأعمدتها الحقيقية مباشرة: بلا views، وبلا نسخ لبيانات المستشفى، وبلا أي كتابة فيها. جداول العيادة (`WC_*` و `WC_USERS_TB`) في مخطط `WCCMS` المستقل.

نفّذ الخطوات بالترتيب. كل خطوة تنتهي بـ **تحقق**؛ لا تنتقل للتي بعدها قبل أن ينجح.

| الملف | ما فيه |
| --- | --- |
| `wccms_oracle_schema.sql` | المستخدم WCCMS، صلاحيات SELECT على جداول HOS الثمانية، جداول العيادة الـ 20، الفهارس، CHECK، البيانات الأساسية، والقسم 8 (المفاتيح المركّبة إلى HOS، مُعطَّل) |
| `wccms_oracle_procedures.sql` | الدوال المساعدة لقراءة XML، `FN_WC_EMPLOYEE_EXISTS`، والـ procedures الأربعة بـ PL/SQL لـ 11g |
| `config/hospital.php` | رقم المستشفى، مخطط وجداول المستشفى، المفاتيح الاختيارية، وقيم الجنس والدخول |
| `oracle-test/` | عُدّة تشغيل السكربتين على Oracle XE 11g في Docker قبل التسليم |
| `app/Services/ClinicalWorkflows/OracleClinicalWorkflowGateway.php` | استدعاء الـ procedures من Laravel (البيانات بصيغة XML) |

---

## الصورة الكاملة

```
HOS (نظام المستشفى — WCCMS يقرأ فقط)            WCCMS (ينشئه wccms_oracle_schema.sql)
-----------------------------------------      ---------------------------------------------------
MR_PATIENT_TB        (MRP_PATREC_CODE, HOS_NO) <-- WC_REQUEST_REG_TB, WC_REGISTRATION_TB
EMPLOYEE_TB          (EMP_SERIAL, HOS_NO)      <-- WC_USERS_TB وجداول الطلب والتسجيل والمراجعة والتقييم والصور
ADMISSION_PATIENT_TB (ADMISSION_CODE, HOS_NO)      (كل رابط = رمز + عمود HOS_NO في جدول العيادة)
INPATIENT_ADM_TB     (INPATIENT_CODE, HOS_NO)
C_LOCATION_TB, C_SEX_TB, C_JOB_TB (code, HOS_NO)   + بقية جداول WC_* + 4 procedures
C_HOSPITAL_TB        (H_CURR = 1)
```

- **مفاتيح المستشفى مركّبة:** الرمز وحده (رقم المريض، رقم الموظف، رقم القسم...) يتكرر بين المستشفيات. المفتاح دائماً (الرمز، `HOS_NO`).
- **كل جدول عيادة يشير إلى مريض أو موظف فيه عمود `HOS_NO` واحد** (مثل `WC_REG_HOS_NO`) يملؤه التطبيق من `HOSPITAL_NO`، وكل فهرس على رمز مريض أو موظف يضم هذا العمود.
- **ربط المستخدم بالموظف** في جدولنا: `WC_USERS_TB (PER_USER_EMP_CD, PER_USER_HOS_NO)` → `EMPLOYEE_TB (EMP_SERIAL, HOS_NO)`. اسم الجدول تغيّر من `PER_USERS_TB` (الاسم مستخدم في HOS) وبقيت أعمدة `PER_USER_*` كما هي.

---

## الخطوة 1: الإعدادات (`.env` الإنتاج)

```ini
DB_CONNECTION=oracle
DB_HOST=<خادم Oracle>
DB_PORT=1521
DB_SERVICE_NAME=<service name>
DB_DATABASE=
DB_USERNAME=WCCMS
DB_PASSWORD=<كلمة مرور WCCMS>
DB_CHARSET=AL32UTF8
DB_SERVER_VERSION=11g
ORA_MAX_NAME_LEN=30

HOSPITAL_NO=1
HOSPITAL_SCHEMA=HOS
HOSPITAL_FOREIGN_KEYS=false
HOSPITAL_DOCTOR_JOB_CODES=2
HOSPITAL_NURSE_JOB_CODES=<أكواد التمريض من C_JOB_TB>

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

| المتغير | المعنى |
| --- | --- |
| `HOSPITAL_NO` | رقم المستشفى الذي يعمل له WCCMS. الافتراضي 1 (مجمع الشفاء الطبي). مصدره في كود المستشفى نفسه: الـ trigger `HOS.LAB_ORDER_TB_MREF` والـ procedure `HOS.GET_PATIENT_LOCATION`. يُكتب في السجل عند كل فحص بدء التشغيل (الخطوة 6). |
| `HOSPITAL_SCHEMA` | مخطط جداول المستشفى. بوجوده تُقرأ الجداول كـ `HOS.MR_PATIENT_TB`... على جهاز التطوير يبقى فارغاً فتُستخدم النسخ المحلية التي ينشئها الـ migration. |
| `HOSPITAL_FOREIGN_KEYS` | `true` فقط إذا نُفّذ القسم 8 من السكربت (الخطوة 5.4). لا يغيّر سلوك التطبيق على Oracle، فالتطبيق يفحص كل رابط قبل الحفظ في الحالتين. |
| `HOSPITAL_DOCTOR_JOB_CODES` | أكواد `C_JOB_TB.JOB_CODE` (مفصولة بفواصل) لحقول الطبيب. الافتراضي 2: هو ما تصفّي به procedures المستشفى نفسه قوائم الأطباء (`GET_DOCTORS_PROC` و `GET_DOCTORS_NAMES`: `EMP_JOB_CD = 2`). |
| `HOSPITAL_NURSE_JOB_CODES` | أكواد وظائف التمريض لحقول الممرض. **غير معروفة بعد**: لا يظهر أي كود تمريض في كود المستشفى. خذها من `SELECT JOB_CODE, JOB_NAME_AR FROM HOS.C_JOB_TB WHERE HOS_NO = 1`. ما دامت فارغة، تعرض حقول الممرض وفريق الجروح كل الموظفين النشطين ويحذّر الفحص (الخطوة 6). حقل فريق الجروح يعرض الأطباء والممرضين معاً. |
| `DB_SERVER_VERSION=11g` و `ORA_MAX_NAME_LEN=30` | يمنعان yajra من استعمال `FETCH FIRST` ومن أسماء أطول من 30 حرفاً، وهما غير موجودين في 11g. |

`SESSION_DRIVER` و `CACHE_STORE` و `QUEUE_CONNECTION` بهذه القيم حتى لا يحتاج Laravel جداوله (`sessions`، `cache`، `jobs`) في Oracle.

بقية القيم في `config/hospital.php` (لا تحتاج تعديلاً عادة): عمود الطبيب `ADMISSION_ENTER_DOC_CD`، عمود القسم `INPATIENT_SUP_LOC_CD`، الإقامة غير الملغاة `INPATIENT_CANCELED = 0`، الموظف الفعّال `EMP_ACTIVE = 1`، وأسماء الجنس `Male` / `Female`.

---

## الخطوة 2: كيف يُعزَل مستشفى عن آخر

**كل قراءة من جداول المستشفى مقيّدة بـ `HOS_NO = HOSPITAL_NO`، بلا استثناء:** المرضى، الموظفون، الدخولات، الإقامات، الأقسام، الجنس، المسميات الوظيفية. القيد global scope على كل Model (`App\Models\Concerns\HospitalTable`)، فيشمل البحث والعلاقات والقوائم والتقارير والتصدير. الاستثناء الوحيد المقصود: فحص بدء التشغيل يقرأ `C_HOSPITAL_TB WHERE H_CURR = 1` ليعرف لأي مستشفى هذه القاعدة.

**جداول المستشفى للقراءة فقط، في كل بيئة:** أي حفظ أو تعديل أو حذف عبر Models المستشفى يُرفض باستثناء `HospitalTableReadOnly` قبل أن يصل إلى القاعدة، بكل الطرق (`save` و `update` و `delete` و `insert` و `upsert` و `increment` و `truncate`، ومع تعطيل الأحداث أيضاً). النسخ المحلية في التطوير والاختبار تُملأ عبر `App\Support\LocalHospitalCopy` فقط، وهو يرفض العمل عند ضبط `HOSPITAL_SCHEMA`. يثبت ذلك `tests/Feature/HospitalTablesReadOnlyTest.php`.

**جداول العيادة** مقيّدة بعمود `HOS_NO` الخاص بها (`App\Models\Concerns\BelongsToHospital`)؛ الجداول التي بلا عمود (مثل المواعيد) تُقرأ عبر التسجيل التابعة له.

**قبل كل حفظ** يتحقق التطبيق أن كل رمز مريض أو موظف موجود في المستشفى المضبوط، وإلا رفض الحفظ برسالة واضحة. والـ procedures تفعل الشيء نفسه (`INVALID_EMPLOYEE`). قواعد التحقق في النماذج (`App\Rules\HospitalPatientExists` و `HospitalEmployeeExists`) تمر عبر الـ Models، لأن قاعدة `exists:` في Laravel تتجاوز الـ scope.

**المفاتيح الأجنبية المركّبة إلى HOS** حسب المحرك:

| المحرك | الوضع |
| --- | --- |
| Oracle (الإنتاج) | في القسم 8 من السكربت، مُعطَّلة. تُفعَّل فقط إذا كان مخطط WCCMS في القاعدة نفسها، ومُنح `REFERENCES` على جداول HOS. لا تعمل عبر database link. |
| MySQL و SQLite (التطوير والاختبار) | ينشئها الـ migration على النسخ المحلية فقط عند `HOSPITAL_FOREIGN_KEYS=true`، بنفس الأسماء. كلاهما يتجاهل الفحص حين يكون الرمز فارغاً، فالروابط الاختيارية تبقى اختيارية. |

في كل الحالات يبقى فحص التطبيق هو الأساس؛ المفاتيح طبقة حماية إضافية. اختبارات `tests/Feature/HospitalIsolationTest.php` تثبت العزل بمستشفى ثانٍ يحمل الرموز نفسها ببيانات مختلفة.

> تنبيه لفريق المستشفى إن فُعِّلت المفاتيح: لن يستطيع نظام المستشفى **حذف** مريض أو موظف له سجلات في العيادة (ORA-02292)، ولا تغيير قيمة مفتاحه.

---

## الخطوة 3: تجربة السكربتين على Oracle XE 11g قبل التسليم

السكربتان لم يُشغَّلا على Oracle بعد. لا تسلّمهما قبل أن تنجح هذه الخطوة. العُدّة في `oracle-test/` وتحتاج Docker فقط:

```powershell
cd oracle-test
powershell -ExecutionPolicy Bypass -File .\run.ps1                    # السكربتان + اختبار الدخان
powershell -ExecutionPolicy Bypass -File .\run.ps1 -WithForeignKeys   # ومعها القسم 8
```

(على Linux/macOS: `./run.sh` و `./run.sh --with-fk`.)

العُدّة تشغّل `gvenzl/oracle-xe:11`، وتنشئ مخطط `HOS` تجريبياً فيه مستشفيان يشتركان في الرموز (ولا شيء من بيانات المستشفى الحقيقية)، ثم تنفّذ السكربتين، ثم `03_smoke_test.sql` الذي يجرّب كل procedure ويطبع PASS/FAIL لكل فحص. التفاصيل في `oracle-test/README.md`.

**تحقق:** آخر سطر `SMOKE TEST: PASS`، ولا كائنات INVALID.

حدود التجربة: XE 11g يعمل بـ AL32UTF8، فسلوك قاعدة المستشفى بـ AR8MSWIN1256 (حروف خارج العربية/اللاتينية، مثل الرموز التعبيرية، تصبح `?`) لا يُختبر هنا.

---

## الخطوة 4: تجهيز الخادم والاتصال

**4.1 Oracle Instant Client و oci8:** ثبّت Instant Client وأضف مجلده إلى `PATH` (Windows) أو `LD_LIBRARY_PATH` (Linux). على Windows: `extension=oci8_19` في `php.ini`؛ على Linux: `pecl install oci8`. أعد تشغيل الخادم.

**تحقق:** `php -m` يُظهر `oci8`.

**4.2 الحزمة:**

```bash
composer require yajra/laravel-oci8:^13
php artisan vendor:publish --tag=oracle
```

**4.3 `config/oracle.php`:**

```php
'options' => [
    PDO::ATTR_CASE => PDO::CASE_NATURAL,
],
'sessionVars' => [
    // ... الموجود كما هو (NLS_DATE_FORMAT, NLS_NUMERIC_CHARACTERS ...)
    'TIME_ZONE' => '+00:00',
],
```

`CASE_NATURAL` ضروري: القيمة الافتراضية `CASE_LOWER` تجعل كل الأعمدة (`MRP_FIRST_NAME_AR`...) فارغة في الشاشات دون أي خطأ.

**4.4 الأحرف الكبيرة في الـ aliases:** Oracle يُرجع كل اسم غير مقتبس بأحرف كبيرة. القاعدة في الكود: داخل `DB::raw` / `selectRaw` ضع الـ alias بين علامتي تنصيص مزدوجتين (`DB::raw('COUNT(*) as "total"')`)، وفي `select('col as ALIAS')` و `withCount('rel as REL_COUNT')` اكتبه بأحرف كبيرة واقرأه كذلك.

**تحقق (بعد الخطوة 5):**

```bash
php artisan config:clear
php artisan tinker
>>> DB::select('select 1 as x, sessiontimezone as tz from dual');
```

المفاتيح `X` و `TZ` بأحرف كبيرة، و `TZ` = `+00:00`.

---

## الخطوة 5: تنفيذ سكربتات SQL

> **لا تشغّل `php artisan migrate` ولا `php artisan db:seed` على الإنتاج أبداً.** الـ migration للتطوير والاختبارات فقط، وينشئ نسخاً محلية من جداول المستشفى.

**5.1 الـ placeholders:** استبدل `{{WCCMS_PASSWORD}}` و `{{WCCMS_TABLESPACE}}` في `wccms_oracle_schema.sql`.

**تحقق:** هذا الأمر لا يُرجع شيئاً:

```bash
grep -nE "\{\{[A-Z_]+\}\}" wccms_oracle_schema.sql wccms_oracle_procedures.sql
```

**5.2 الجداول** (DBA، بـ SQL*Plus مع `NLS_LANG=AMERICAN_AMERICA.AL32UTF8` حتى يُحفظ النص العربي سليماً):

```sql
@wccms_oracle_schema.sql
```

الملف يتوقف عند أول خطأ. إذا رفض Oracle أسطر `GRANT SELECT ON HOS.*` (ORA-01031) فيجب أن ينفّذها مالك المخطط `HOS`، ثم يُكمل الباقي.

**5.3 الـ procedures:**

```sql
@wccms_oracle_procedures.sql
```

**تحقق:**

```sql
SELECT COUNT(*) FROM all_tables WHERE owner = 'WCCMS';                                         -- 20
SELECT object_name, object_type FROM all_objects WHERE owner = 'WCCMS' AND status = 'INVALID';  -- لا صفوف
SELECT COUNT(*) FROM WCCMS.WC_ASSESSMENT_CHOICE_TB;                                            -- 46
```

إذا ظهر كائن INVALID: `SHOW ERRORS PROCEDURE WCCMS.<الاسم>` يعرض السطر والسبب.

**5.4 (اختياري) المفاتيح المركّبة إلى HOS:** فقط إذا تحقق الشرطان في الخطوة 2. أزل التعليق عن القسم 8 في آخر `wccms_oracle_schema.sql` ونفّذه (يحتاج `GRANT REFERENCES` من مالك `HOS`)، ثم اضبط `HOSPITAL_FOREIGN_KEYS=true`. إن فشل أي مفتاح بـ ORA-02298 فهناك صفوف عيادة ترمز لمريض/موظف غير موجود: صحّحها أولاً.

---

## الخطوة 6: فحص بدء التشغيل

```bash
php artisan wccms:check-hospital
```

فحص للقراءة فقط، لا يوقف النظام أبداً، ويكتب كل نتيجة في `storage/logs/laravel.log`:

1. رقم المستشفى المستخدم (`HOSPITAL_NO`).
2. يقرأ `HOS.C_HOSPITAL_TB WHERE H_CURR = 1`. إن اختلف `H_CODE` عن `HOSPITAL_NO` يكتب تحذيراً بالرقمين. إن تعذرت القراءة يكتب ذلك ويستمر.
3. يقرأ `C_SEX_TB` للمستشفى المضبوط. أي رمز ليس `SEX_NAME_EN` فيه `Male` أو `Female` تماماً (أو فارغ) يُذكر في تحذير برمزه؛ مرضاه يظهرون «Unknown» في التقارير. لا تخمين للجنس من الرمز (كود المستشفى نفسه متناقض: 1 = أنثى في 12 موضعاً و 1 = ذكر في موضعين).

4. يذكر وظائف `C_JOB_TB` التي تملأ قوائم الأطباء والممرضين بأسمائها، ويحذّر من كود غير موجود في `C_JOB_TB` لهذا المستشفى، أو من دور بلا أكواد (`HOSPITAL_NURSE_JOB_CODES` فارغ).

الفحص يعمل أيضاً تلقائياً مرة يومياً لكل رقم مستشفى، بعد إرسال أول استجابة ويب، فلا يبطئ أي طلب.

**تحقق:** لا تحذيرات، أو تحذيرات مفهومة وموثّقة.

---

## الخطوة 7: الدخول والصلاحيات

- **من يدخل يقرّره نظام المستشفى.** الدخول بـ JWT منه، و `PER_USER_USERNAME` يساوي `sub` في التوكن.
- **الحساب يُنشأ عند أول دخول** في `WC_USERS_TB`، مفعّلاً.
- **شاشة المستخدمين (`/admin`)** تربط أي مستخدم بموظف فعّال من مستشفاه، أو توقف الحساب. الربط يُكتب في `PER_USER_EMP_CD` و `PER_USER_HOS_NO` فقط.
- حساب مربوط بموظف من مستشفى آخر لا يظهر له موظف هنا (لا يُربط بموظف هذا المستشفى الذي يحمل الرقم نفسه).

**تحقق:** أول دخول يفتح لوحة التحكم، ويظهر الصف في `WC_USERS_TB` بقيمة `PER_USER_IS_ACTIVE = 1`.

---

## الخطوة 8: اختبار المسارات وتشغيل النظام

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

نفّذ بالترتيب، وراقب `storage/logs/laravel.log`:

- [ ] `wccms:check-hospital` بلا تحذير غير مفهوم.
- [ ] ربط مستخدم بموظف من شاشة المستخدمين؛ القائمة فيها موظفو هذا المستشفى الفعّالون فقط (كل الوظائف، فالحساب قد يكون لأي موظف).
- [ ] حقول الطبيب تعرض الأطباء فقط، وحقول الممرض الممرضين فقط، وحقل فريق الجروح الاثنين معاً.
- [ ] البحث عن مريض برقم الوثيقة (`MRP_ID`)، وبرقم الملف (`MRP_PATREC_CODE`)، وبجزء من الاسم: النتائج من هذا المستشفى فقط.
- [ ] البحث بنص فيه حروف في خانة رقمية لا يُظهر خطأ `ORA-01722`.
- [ ] شاشة الطلب تعرض قسم المريض الحالي وطبيبه وتاريخ دخوله (من `ADMISSION_PATIENT_TB` و `INPATIENT_ADM_TB`).
- [ ] طلب تحويل، ثم قبوله بجرحين وصورة.
- [ ] مراجعة يومية، ثم إرسال النموذج نفسه مرة ثانية: لا يتكرر الصف.
- [ ] مراجعة مجدولة، وإضافة جرح لتسجيل نشط.
- [ ] ملاحظات طويلة تُحفظ كاملة.
- [ ] لوحة التحكم وتقرير المريض والتقرير الشهري وتصديره: بيانات هذا المستشفى فقط، والجنس «Male» / «Female» / «Unknown».
- [ ] قبول طلب مقبول سابقاً يعرض رسالة خطأ ولا يترك صفوفاً ناقصة.

**تحقق:** كل البنود ناجحة، والسجل خالٍ من `ORA-`.

خارج القاعدة: صور الجروح في `storage/app/public/wound_photos`؛ أضفها إلى النسخ الاحتياطي مع مخطط WCCMS. لا تشغّل `php artisan storage:link`.

---

## أخطاء شائعة

| الخطأ | السبب | الحل |
| --- | --- | --- |
| كل الحقول فارغة في الشاشات بلا خطأ | `ATTR_CASE` ما زال `CASE_LOWER` | الخطوة 4.3 ثم `php artisan config:clear` |
| `ORA-00942: table or view does not exist` | `HOSPITAL_SCHEMA` خطأ، أو لا يوجد `GRANT SELECT` على جدول HOS | الخطوة 1 والخطوة 5.2 |
| `ORA-00933` أو `ORA-00923` في استعلام فيه حدّ للصفوف | `DB_SERVER_VERSION` ليس `11g` (يُولَّد `FETCH FIRST`) | الخطوة 1 |
| `ORA-00972: identifier is too long` | `ORA_MAX_NAME_LEN` ليس 30 | الخطوة 1 |
| «الرمز ... غير موجود في المستشفى رقم ...» | رمز مريض أو موظف من مستشفى آخر أو غير موجود | مقصود: العزل بين المستشفيات |
| بيانات مستشفى آخر لا تظهر إطلاقاً | `HOSPITAL_NO` يشير لمستشفى آخر | راجع تحذير `wccms:check-hospital` |
| `ORA-20001: INVALID_EMPLOYEE` | موظف غير موجود في مستشفى السجل | اختر موظفاً من القائمة |
| `ORA-20001: <رمز آخر>` | تحقق داخل procedure رفض البيانات (مثل `REQUEST_ALREADY_DECIDED`) | الرمز يقول السبب؛ العملية كاملة أُلغيت |
| `ORA-02291: parent key not found` | المفاتيح المركّبة مفعّلة والرمز غير موجود في HOS | تحقق من الرمز |
| `ORA-02292` عند الحذف في نظام المستشفى | المفاتيح المركّبة مفعّلة وسجل العيادة يشير لهذا المريض أو الموظف | مقصود؛ انظر الخطوة 2 |
| نص عربي يظهر `???` | `NLS_LANG` ليس AL32UTF8 عند تشغيل السكربت | أعد التشغيل بالإعداد الصحيح |
| «بيانات التقييم أطول من المسموح» | XML المراجعة أكبر من 32 KB (حد معامل PL/SQL) | ملاحظات أقصر |
