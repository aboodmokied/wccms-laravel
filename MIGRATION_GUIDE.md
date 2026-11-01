# دليل تشغيل WCCMS على قاعدة بيانات المستشفى (Oracle 11g)

يعمل WCCMS على قاعدة Oracle نفسها التي فيها نظام المستشفى (المخطط `HOS`، Oracle 11g، عدة مستشفيات في القاعدة نفسها). يقرأ جداول المستشفى الحقيقية بأعمدتها الحقيقية مباشرة: بلا views، وبلا نسخ لبيانات المستشفى، وبلا أي كتابة فيها. جداول العيادة (`WC_*` و `WC_USERS_TB`) في مخطط `WCCMS` المستقل.

نفّذ الخطوات بالترتيب. كل خطوة تنتهي بـ **تحقق**؛ لا تنتقل للتي بعدها قبل أن ينجح.

> **لماذا تبدأ بقائمة الفحص:** النظام كُتب دون وصول إلى قاعدة المستشفى. ما لم يُعرف أثناء التطوير (رقم المستشفى الحالي، رموز الجنس، أكواد وظائف الأطباء والتمريض، عدد الموظفين النشطين) يكتشفه `wccms:check-hospital` من القاعدة نفسها أثناء التسليم، ويقول لكل تحذير ماذا تغيّر في `.env`.

| الملف | ما فيه |
| --- | --- |
| `wccms_oracle_schema.sql` | المستخدم WCCMS، صلاحيات SELECT على جداول HOS الثمانية، جداول العيادة الـ 20، الفهارس، CHECK، البيانات الأساسية، والقسم 8 (المفاتيح المركّبة إلى HOS، مُعطَّل) |
| `wccms_oracle_procedures.sql` | الدوال المساعدة لقراءة XML، `FN_WC_EMPLOYEE_EXISTS`، والـ procedures الأربعة بـ PL/SQL لـ 11g |
| `config/hospital.php` | رقم المستشفى، مخطط وجداول المستشفى، المفاتيح الاختيارية، الموظف النشط، أكواد الوظائف، وأسماء الجنس |
| `oracle-test/` | تجربة السكربتين: على Oracle XE 11g في Docker (`run.ps1`)، أو على القاعدة الحقيقية (`live_check.sql`) |
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

## الخطوة 1: قائمة فحص التسليم (`wccms:check-hospital`)

**ما يلزم:** نسخة النظام على الخادم بعد `composer install`، مع oci8 وحزمة Oracle (الخطوة 5؛ إن لم يكن الخادم جاهزاً فجهّزه أولاً ثم عُد هنا)، و `.env` يتصل بقاعدة المستشفى بحساب يقرأ جداول `HOS`. قبل إنشاء المستخدم `WCCMS` (الخطوة 6) استعمل أي حساب قراءة لديك، كحساب الـ DBA؛ وبعدها أعد الفحص بالمستخدم `WCCMS` نفسه (الخطوة 7).

```ini
DB_CONNECTION=oracle
DB_HOST=<خادم Oracle>
DB_PORT=1521
DB_SERVICE_NAME=<service name>
DB_USERNAME=<حساب يقرأ HOS>
DB_PASSWORD=<كلمة مروره>
DB_CHARSET=AL32UTF8
DB_SERVER_VERSION=11g
ORA_MAX_NAME_LEN=30

HOSPITAL_NO=1
HOSPITAL_SCHEMA=HOS
CACHE_STORE=file
```

```bash
php artisan config:clear
php artisan wccms:check-hospital
```

الفحص للقراءة فقط، لا يغيّر شيئاً في القاعدة، ولا يوقف النظام. يطبع بالعربية، ويكتب كل نتيجة أيضاً في `storage/logs/laravel.log`:

1. **المستشفى:** `HOSPITAL_NO` المستخدم، والمستشفى الحالي في `C_HOSPITAL_TB WHERE H_CURR = 1` برقمه واسمه، وهل يتطابقان.
2. **رموز الجنس:** كل رمز في `C_SEX_TB` لهذا المستشفى باسميه، وتصنيفه (ذكر / أنثى / غير معروف). التصنيف بمطابقة `SEX_NAME_EN` حرفياً، بلا تخمين من الرمز (كود المستشفى نفسه متناقض: 1 = أنثى في 12 موضعاً و 1 = ذكر في موضعين).
3. **وظائف الأطباء:** كل كود في `HOSPITAL_DOCTOR_JOB_CODES` (الافتراضي 2) باسمه في `C_JOB_TB`، فيظهر الكود الخطأ فوراً.
4. **وظائف التمريض:** الأكواد من `HOSPITAL_NURSE_JOB_CODES`؛ أو، إن كان فارغاً، الأكواد التي اكتُشفت تلقائياً من أسماء الوظائف في `C_JOB_TB` (تمريض، ممرض، ممرضة، Nurse، Nursing)، بأسمائها، مع السطر الجاهز لتثبيتها في `.env`. ثم **مقارنة مع `USER_TYPE = 3`** (نوع «ممرض» في `HOS_USERS_VW`): عدد الممرضين النشطين بالوظيفة، وعدد الموظفين النشطين الذين `USER_TYPE = 3`، وعدد المشتركين بينهما، ووظائف موظفي `USER_TYPE = 3` بأسمائها. إن كان المشترك أقل من 80% من المجموعة الأكبر يظهر تحذير.
5. **الموظفون النشطون:** قاعدة "الموظف النشط" المطبّقة (افتراضياً `EMP_REC_CD = 1` في كل القوائم، كما في إجراءات المستشفى؛ و `EMP_ACTIVE` غير مستعمل)، وعدد الأطباء النشطين والممرضين النشطين في القوائم.
6. **كل الوظائف:** قائمة `C_JOB_TB` كاملة لهذا المستشفى، تظهر عند أي تحذير في الوظائف لتختار منها الأكواد.
7. **التحذيرات:** عددها، وتحت كل تحذير سطر «الحل».

**ماذا تغيّر في `.env` لكل تحذير:**

| التحذير | التغيير |
| --- | --- |
| عدم تطابق: `HOSPITAL_NO` المضبوط هو X، لكن `C_HOSPITAL_TB` يقول إن القاعدة للمستشفى Y | `HOSPITAL_NO=Y` إن كانت هذه قاعدة المستشفى Y؛ وإن كان المقصود X فالاتصال (`DB_*`) على القاعدة الخطأ. |
| لا يوجد صف فيه `H_CURR = 1`، أو يوجد أكثر من صف | لا يُستنتج آلياً: حدّد رقم المستشفى من `C_HOSPITAL_TB` مع مسؤول القاعدة واضبط `HOSPITAL_NO`. |
| تعذرت قراءة `HOS.<جدول>` | ليس في `.env`: نفّذ `GRANT SELECT` على الجدول للحساب المستخدم (القسم 2 من `wccms_oracle_schema.sql`)، وتأكد من `HOSPITAL_SCHEMA=HOS`. |
| رموز جنس لا يطابق `SEX_NAME_EN` فيها `Male` أو `Female` تماماً | إن كان الرمز ذكراً أو أنثى فعلاً بكتابة أخرى (مثل `MALE`): `HOSPITAL_SEX_MALE_NAME_EN=MALE` (أو `HOSPITAL_SEX_FEMALE_NAME_EN=...`) بالكتابة نفسها حرفياً. وإلا فهو ليس ذكراً ولا أنثى ويبقى «Unknown» عن قصد. |
| `HOSPITAL_DOCTOR_JOB_CODES` فارغ، أو أكواد وظائف الأطباء غير موجودة | `HOSPITAL_DOCTOR_JOB_CODES=<أكواد الأطباء من قسم «كل الوظائف»>`، مفصولة بفواصل. |
| لم توجد في `C_JOB_TB` وظيفة تمريض بالأسماء، أو تعذرت قراءته | `HOSPITAL_NURSE_JOB_CODES=<أكواد التمريض من قسم «كل الوظائف»>`. إلى أن يُضبط، تعرض حقول الممرض وفريق الجروح كل الموظفين النشطين، مع تنبيه ظاهر في الشاشات وتحذير في السجل. |
| أكواد وظائف الممرضين غير موجودة | صحّح `HOSPITAL_NURSE_JOB_CODES`. |
| يختلف الممرضون بالوظيفة عن موظفي `USER_TYPE = 3` كثيراً | اقرأ سطر «وظائف موظفي USER_TYPE = 3». إن كانت وظائفهم هي التمريض الصحيح: `HOSPITAL_NURSE_JOB_CODES=<الأكواد المقترحة في سطر الحل>` (راجع أسماءها أولاً). وإن كانت أكواد التمريض الحالية صحيحة و `USER_TYPE` لا يدل على التمريض في هذا المستشفى: `HOSPITAL_NURSE_USER_TYPE=` (فارغ) يوقف المقارنة. المقارنة لا تغيّر ما تعرضه القوائم. |
| تعذرت مقارنة وظائف التمريض مع `USER_TYPE` | لا يؤثر على التشغيل: العمود `USER_TYPE` غير موجود أو غير مقروء. `HOSPITAL_NURSE_USER_TYPE=` (فارغ) يوقف المقارنة. |
| لا يوجد أي موظف نشط في قوائم الأطباء أو الممرضين | راجع أكواد الوظائف أعلاه أولاً، ثم `HOSPITAL_EMP_REC_CD` (شرط `EMP_REC_CD`؛ فارغ = بلا شرط) و `HOSPITAL_EMP_REC_CD_LISTS` (`all` أو `doctors`)، و `HOSPITAL_EMP_ACTIVE` إن ضبطته (فارغ افتراضياً = بلا شرط). |
| تعذر عدّ الموظفين النشطين | `GRANT SELECT` على `HOS.EMPLOYEE_TB`، ووجود الأعمدة `EMP_ACTIVE` و `EMP_REC_CD` و `EMP_JOB_CD`. |

ليست تحذيراً لكن راجعها: إن ظهر «اكتُشفت وظائف التمريض تلقائياً»، اقرأ الأسماء المكتشفة. إن كانت كلها وظائف تمريض فانسخ السطر المقترح `HOSPITAL_NURSE_JOB_CODES=...` إلى `.env` (يثبّتها ولا يعود الاكتشاف يعمل)؛ وإن كان فيها ما ليس تمريضاً فاكتب الأكواد الصحيحة فقط. راجع كذلك عدد الأطباء والممرضين: رقم بعيد عن الواقع يعني أكواداً أو شرط نشاط خاطئاً.

بعد كل تعديل في `.env`: `php artisan config:clear` ثم أعد الفحص.

الفحص يعمل أيضاً تلقائياً مرة يومياً لكل رقم مستشفى بعد أول استجابة ويب، فيبقى السجل يذكر أي تغيّر لاحق.

**تحقق:** آخر قسم يقول `التحذيرات: 0`، والأسماء والأعداد المطبوعة منطقية.

---

## الخطوة 2: الإعدادات (`.env` الإنتاج)

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
HOSPITAL_NURSE_JOB_CODES=<من الخطوة 1>
HOSPITAL_EMP_REC_CD=1
HOSPITAL_EMP_REC_CD_LISTS=all
HOSPITAL_EMP_ACTIVE=
HOSPITAL_NURSE_USER_TYPE=3
HOSPITAL_SEX_MALE_NAME_EN=Male
HOSPITAL_SEX_FEMALE_NAME_EN=Female

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

| المتغير | المعنى |
| --- | --- |
| `HOSPITAL_NO` | رقم المستشفى الذي يعمل له WCCMS. الافتراضي 1 (مجمع الشفاء الطبي). مصدره في كود المستشفى نفسه: الـ trigger `HOS.LAB_ORDER_TB_MREF` والـ procedure `HOS.GET_PATIENT_LOCATION`. تتحقق منه الخطوة 1 مقابل `C_HOSPITAL_TB`. |
| `HOSPITAL_SCHEMA` | مخطط جداول المستشفى. بوجوده تُقرأ الجداول كـ `HOS.MR_PATIENT_TB`... على جهاز التطوير يبقى فارغاً فتُستخدم النسخ المحلية التي ينشئها الـ migration. |
| `HOSPITAL_FOREIGN_KEYS` | `true` فقط إذا نُفّذ القسم 8 من السكربت (الخطوة 6.4). لا يغيّر سلوك التطبيق على Oracle، فالتطبيق يفحص كل رابط قبل الحفظ في الحالتين. |
| `HOSPITAL_DOCTOR_JOB_CODES` | أكواد `C_JOB_TB.JOB_CODE` (مفصولة بفواصل) لحقول الطبيب. الافتراضي 2: هو ما تصفّي به procedures المستشفى نفسه قوائم الأطباء (`GET_DOCTORS_PROC` و `GET_DOCTORS_NAMES`: `EMP_JOB_CD = 2`). الخطوة 1 تطبع اسم كل كود. |
| `HOSPITAL_NURSE_JOB_CODES` | أكواد وظائف التمريض. **فارغ:** تُكتشف أثناء التشغيل من أسماء الوظائف في `C_JOB_TB` (تمريض، ممرض، ممرضة، Nurse، Nursing)، ويُكتب في السجل ما اختير بأكواده وأسمائه. **مضبوط:** يُستعمل كما هو ويلغي الاكتشاف. الأفضل تثبيته بعد مراجعة الخطوة 1. حقل فريق الجروح يعرض الأطباء والممرضين معاً. |
| `HOSPITAL_EMP_REC_CD` و `HOSPITAL_EMP_REC_CD_LISTS` | **الموظف النشط:** شرط `EMP_REC_CD = 1`، وهو ما تستعمله قوائم الموظفين في نظام المستشفى نفسه (68 موضعاً، منها `GET_DOCTORS_NAMES`). `all` (الافتراضي): على كل القوائم؛ `doctors`: على قوائم الأطباء فقط. فارغ = بلا هذا الشرط. |
| `HOSPITAL_EMP_ACTIVE` | شرط إضافي `EMP_ACTIVE = <القيمة>` على كل القوائم. **فارغ افتراضياً (غير مستعمل):** في كود المستشفى لا يصفّي عليه إلا `HOS_USERS_VW` (مستخدمو الدخول)، فقد يعني «له حساب دخول فعّال» ويخفي موظفين بلا حساب. |
| `HOSPITAL_NURSE_USER_TYPE` | قيمة `EMPLOYEE_TB.USER_TYPE` للممرض (3 في `HOS_USERS_VW`). ليست شرطاً على القوائم: الخطوة 1 تقارن بها أكواد التمريض فقط. فارغ = بلا مقارنة. |
| `HOSPITAL_SEX_MALE_NAME_EN` و `HOSPITAL_SEX_FEMALE_NAME_EN` | قيمة `SEX_NAME_EN` الحرفية للذكر وللأنثى في `C_SEX_TB`. |
| `DB_SERVER_VERSION=11g` و `ORA_MAX_NAME_LEN=30` | يمنعان yajra من استعمال `FETCH FIRST` ومن أسماء أطول من 30 حرفاً، وهما غير موجودين في 11g. |

`SESSION_DRIVER` و `CACHE_STORE` و `QUEUE_CONNECTION` بهذه القيم حتى لا يحتاج Laravel جداوله (`sessions`، `cache`، `jobs`) في Oracle.

بقية القيم في `config/hospital.php` (لا تحتاج تعديلاً عادة): عمود الطبيب `ADMISSION_ENTER_DOC_CD`، عمود القسم `INPATIENT_SUP_LOC_CD`، الإقامة غير الملغاة `INPATIENT_CANCELED = 0`، وكلمات اكتشاف التمريض.

---

## الخطوة 3: كيف يُعزَل مستشفى عن آخر

**كل قراءة من جداول المستشفى مقيّدة بـ `HOS_NO = HOSPITAL_NO`، بلا استثناء:** المرضى، الموظفون، الدخولات، الإقامات، الأقسام، الجنس، المسميات الوظيفية. القيد global scope على كل Model (`App\Models\Concerns\HospitalTable`)، فيشمل البحث والعلاقات والقوائم والتقارير والتصدير واكتشاف وظائف التمريض. الاستثناء الوحيد المقصود: قائمة الفحص تقرأ `C_HOSPITAL_TB WHERE H_CURR = 1` لتعرف لأي مستشفى هذه القاعدة.

**جداول المستشفى للقراءة فقط، في كل بيئة:** أي حفظ أو تعديل أو حذف عبر Models المستشفى يُرفض باستثناء `HospitalTableReadOnly` قبل أن يصل إلى القاعدة، بكل الطرق (`save` و `update` و `delete` و `insert` و `upsert` و `increment` و `truncate`، ومع تعطيل الأحداث أيضاً). النسخ المحلية في التطوير والاختبار تُملأ عبر `App\Support\LocalHospitalCopy` فقط، وهو يرفض العمل عند ضبط `HOSPITAL_SCHEMA`. يثبت ذلك `tests/Feature/HospitalTablesReadOnlyTest.php`.

**جداول العيادة** مقيّدة بعمود `HOS_NO` الخاص بها (`App\Models\Concerns\BelongsToHospital`)؛ الجداول التي بلا عمود (مثل المواعيد) تُقرأ عبر التسجيل التابعة له.

**قبل كل حفظ** يتحقق التطبيق أن كل رمز مريض أو موظف موجود في المستشفى المضبوط، وإلا رفض الحفظ برسالة واضحة. والـ procedures تفعل الشيء نفسه (`INVALID_EMPLOYEE`). قواعد التحقق في النماذج (`App\Rules\HospitalPatientExists` و `HospitalEmployeeExists`) تمر عبر الـ Models، لأن قاعدة `exists:` في Laravel تتجاوز الـ scope. وحقول الطبيب والممرض وفريق الجروح تقبل فقط موظفاً من وظائف الحقل.

**المفاتيح الأجنبية المركّبة إلى HOS** حسب المحرك:

| المحرك | الوضع |
| --- | --- |
| Oracle (الإنتاج) | في القسم 8 من السكربت، مُعطَّلة. تُفعَّل فقط إذا كان مخطط WCCMS في القاعدة نفسها، ومُنح `REFERENCES` على جداول HOS. لا تعمل عبر database link. |
| MySQL و SQLite (التطوير والاختبار) | ينشئها الـ migration على النسخ المحلية فقط عند `HOSPITAL_FOREIGN_KEYS=true`، بنفس الأسماء. كلاهما يتجاهل الفحص حين يكون الرمز فارغاً، فالروابط الاختيارية تبقى اختيارية. |

في كل الحالات يبقى فحص التطبيق هو الأساس؛ المفاتيح طبقة حماية إضافية. اختبارات `tests/Feature/HospitalIsolationTest.php` تثبت العزل بمستشفى ثانٍ يحمل الرموز نفسها ببيانات مختلفة.

> تنبيه لفريق المستشفى إن فُعِّلت المفاتيح: لن يستطيع نظام المستشفى **حذف** مريض أو موظف له سجلات في العيادة (ORA-02292)، ولا تغيير قيمة مفتاحه.

---

## الخطوة 4: تجربة السكربتين على Oracle 11g (إلزامية قبل التشغيل الفعلي)

السكربتان لم يُشغَّلا على Oracle أثناء التطوير. **لا يُشغَّل النظام فعلياً قبل أن ينجح أحد الاختبارين التاليين، والأفضل الاثنان:**

**4.1 على Oracle XE 11g في Docker** (أي جهاز فيه Docker؛ لا يمس قاعدة المستشفى):

```powershell
cd oracle-test
powershell -ExecutionPolicy Bypass -File .\run.ps1                    # السكربتان + اختبار الدخان
powershell -ExecutionPolicy Bypass -File .\run.ps1 -WithForeignKeys   # ومعها القسم 8
```

(على Linux/macOS: `./run.sh` و `./run.sh --with-fk`.)

العُدّة تشغّل `gvenzl/oracle-xe:11`، وتنشئ مخطط `HOS` تجريبياً فيه مستشفيان يشتركان في الرموز (ولا شيء من بيانات المستشفى الحقيقية)، ثم تنفّذ السكربتين، ثم اختبار الدخان الذي يجرّب كل procedure ويطبع PASS/FAIL لكل فحص. التفاصيل في `oracle-test/README.md`.

**4.2 الفحوص نفسها على القاعدة الحقيقية:** `oracle-test/live_check.sql`، بعد تنفيذ السكربتين في الخطوة 6 (انظر 6.5). يغني عن 4.1 إن لم يتوفر Docker.

**تحقق:** آخر سطر `SMOKE TEST: PASS`، ولا كائنات INVALID. أي `FAIL` يوقف التسليم.

حدود تجربة Docker: XE 11g يعمل بـ AL32UTF8، فسلوك قاعدة المستشفى بـ AR8MSWIN1256 (حروف خارج العربية/اللاتينية، مثل الرموز التعبيرية، تصبح `?`) لا يُختبر فيها؛ `live_check.sql` على القاعدة الحقيقية يختبره.

---

## الخطوة 5: تجهيز الخادم والاتصال

**5.1 Oracle Instant Client و oci8:** ثبّت Instant Client وأضف مجلده إلى `PATH` (Windows) أو `LD_LIBRARY_PATH` (Linux). على Windows: `extension=oci8_19` في `php.ini`؛ على Linux: `pecl install oci8`. أعد تشغيل الخادم.

**تحقق:** `php -m` يُظهر `oci8`.

**5.2 الحزمة:**

```bash
composer require yajra/laravel-oci8:^13
php artisan vendor:publish --tag=oracle
```

**5.3 `config/oracle.php`:**

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

**5.4 الأحرف الكبيرة في الـ aliases:** Oracle يُرجع كل اسم غير مقتبس بأحرف كبيرة. القاعدة في الكود: داخل `DB::raw` / `selectRaw` ضع الـ alias بين علامتي تنصيص مزدوجتين (`DB::raw('COUNT(*) as "total"')`)، وفي `select('col as ALIAS')` و `withCount('rel as REL_COUNT')` اكتبه بأحرف كبيرة واقرأه كذلك.

**تحقق:**

```bash
php artisan config:clear
php artisan tinker
>>> DB::select('select 1 as x, sessiontimezone as tz from dual');
```

المفاتيح `X` و `TZ` بأحرف كبيرة، و `TZ` = `+00:00`.

---

## الخطوة 6: تنفيذ سكربتات SQL

> **لا تشغّل `php artisan migrate` ولا `php artisan db:seed` على الإنتاج أبداً.** الـ migration للتطوير والاختبارات فقط، وينشئ نسخاً محلية من جداول المستشفى.

**6.1 الـ placeholders:** استبدل `{{WCCMS_PASSWORD}}` و `{{WCCMS_TABLESPACE}}` في `wccms_oracle_schema.sql`.

**تحقق:** هذا الأمر لا يُرجع شيئاً:

```bash
grep -nE "\{\{[A-Z_]+\}\}" wccms_oracle_schema.sql wccms_oracle_procedures.sql
```

**6.2 الجداول** (DBA، بـ SQL*Plus مع `NLS_LANG=AMERICAN_AMERICA.AL32UTF8` حتى يُحفظ النص العربي سليماً):

```sql
@wccms_oracle_schema.sql
```

الملف يتوقف عند أول خطأ. إذا رفض Oracle أسطر `GRANT SELECT ON HOS.*` (ORA-01031) فيجب أن ينفّذها مالك المخطط `HOS`، ثم يُكمل الباقي.

**6.3 الـ procedures:**

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

**6.4 (اختياري) المفاتيح المركّبة إلى HOS:** فقط إذا تحقق الشرطان في الخطوة 3. أزل التعليق عن القسم 8 في آخر `wccms_oracle_schema.sql` ونفّذه (يحتاج `GRANT REFERENCES` من مالك `HOS`)، ثم اضبط `HOSPITAL_FOREIGN_KEYS=true`. إن فشل أي مفتاح بـ ORA-02298 فهناك صفوف عيادة ترمز لمريض/موظف غير موجود: صحّحها أولاً.

**6.5 (إلزامي) فحص الإجراءات على القاعدة الحقيقية، قبل التشغيل الفعلي:** بالمستخدم `WCCMS`، من مجلد `oracle-test`:

```powershell
cd oracle-test
$env:NLS_LANG = 'AMERICAN_AMERICA.AL32UTF8'
sqlplus WCCMS/<كلمة المرور>@//<الخادم>:1521/<service> "@live_check.sql" <HOSPITAL_NO> <EMP_SERIAL لممرض> <MRP_PATREC_CODE لمريض>
```

- الموظف: أي موظف نشط في هذا المستشفى **ليس له حساب WCCMS بعد** (يمكن اختياره من قائمة الممرضين التي تعدّها الخطوة 1).
- المريض: أي مريض من هذا المستشفى.
- الفحوص نفسها التي في Docker: قراءة جداول HOS الثمانية، قبول تحويل بجرح وصورة، إضافة جرح، مراجعتان (والثانية بالمفتاح نفسه لا تتكرر)، تخريج، ورفض الموظف غير الموجود والطلب المقبول سابقاً.
- **لا يبقى شيء في القاعدة:** الإجراءات لا تنفّذ COMMIT أبداً، والسكربت ينتهي بـ ROLLBACK، حتى عند الخطأ.

**تحقق:** `SMOKE TEST: PASS`. أي `FAIL` يوقف التسليم.

---

## الخطوة 7: قائمة الفحص بالمستخدم WCCMS

اضبط `.env` كما في الخطوة 2 (`DB_USERNAME=WCCMS`)، ثم:

```bash
php artisan config:clear
php artisan wccms:check-hospital
```

هذه المرة يثبت الفحص أيضاً أن صلاحيات `WCCMS` على جداول `HOS` كاملة.

**تحقق:** `التحذيرات: 0`، والنتائج نفسها التي ظهرت في الخطوة 1.

---

## الخطوة 8: الدخول والصلاحيات

- **من يدخل يقرّره نظام المستشفى.** الدخول بـ JWT منه، و `PER_USER_USERNAME` يساوي `sub` في التوكن.
- **الحساب يُنشأ عند أول دخول** في `WC_USERS_TB`، مفعّلاً.
- **شاشة المستخدمين (`/admin`)** تربط أي مستخدم بموظف فعّال من مستشفاه، أو توقف الحساب. الربط يُكتب في `PER_USER_EMP_CD` و `PER_USER_HOS_NO` فقط.
- حساب مربوط بموظف من مستشفى آخر لا يظهر له موظف هنا (لا يُربط بموظف هذا المستشفى الذي يحمل الرقم نفسه).

**تحقق:** أول دخول يفتح لوحة التحكم، ويظهر الصف في `WC_USERS_TB` بقيمة `PER_USER_IS_ACTIVE = 1`.

---

## الخطوة 9: اختبار المسارات وتشغيل النظام

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

نفّذ بالترتيب، وراقب `storage/logs/laravel.log`:

- [ ] `oracle-test/run.ps1` أو `live_check.sql`: `SMOKE TEST: PASS` (الخطوتان 4 و 6.5).
- [ ] `wccms:check-hospital`: `التحذيرات: 0` (الخطوة 7).
- [ ] ربط مستخدم بموظف من شاشة المستخدمين؛ القائمة فيها موظفو هذا المستشفى الفعّالون فقط (كل الوظائف، فالحساب قد يكون لأي موظف).
- [ ] حقول الطبيب تعرض الأطباء فقط، وحقول الممرض الممرضين فقط، وحقل فريق الجروح الاثنين معاً، ولا يظهر تنبيه «تعرض كل الموظفين النشطين».
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
| كل الحقول فارغة في الشاشات بلا خطأ | `ATTR_CASE` ما زال `CASE_LOWER` | الخطوة 5.3 ثم `php artisan config:clear` |
| `ORA-00942: table or view does not exist` | `HOSPITAL_SCHEMA` خطأ، أو لا يوجد `GRANT SELECT` على جدول HOS | الخطوة 2 والخطوة 6.2، ثم `wccms:check-hospital` |
| `ORA-00933` أو `ORA-00923` في استعلام فيه حدّ للصفوف | `DB_SERVER_VERSION` ليس `11g` (يُولَّد `FETCH FIRST`) | الخطوة 2 |
| `ORA-00972: identifier is too long` | `ORA_MAX_NAME_LEN` ليس 30 | الخطوة 2 |
| تنبيه في الشاشة: «قوائم الممرضين... تعرض كل الموظفين النشطين» | لم تُعرف أكواد وظائف التمريض (لا `HOSPITAL_NURSE_JOB_CODES` ولا وظيفة باسم تمريض في `C_JOB_TB`) | الخطوة 1: اضبط `HOSPITAL_NURSE_JOB_CODES` |
| قائمة الأطباء أو الممرضين فارغة | أكواد خطأ، أو شرط `EMP_REC_CD` (أو `EMP_ACTIVE` إن ضُبط) لا يطابق بيانات المستشفى | الخطوة 1: قسم «الموظفون النشطون» وتحذيره |
| «الرمز ... غير موجود في المستشفى رقم ...» | رمز مريض أو موظف من مستشفى آخر أو غير موجود | مقصود: العزل بين المستشفيات |
| «الموظف المحدد ... ليس من الأطباء» (أو الممرضين) | الموظف ليس من وظائف الحقل | اختر من القائمة؛ أو صحّح أكواد الوظائف (الخطوة 1) |
| بيانات مستشفى آخر لا تظهر إطلاقاً | `HOSPITAL_NO` يشير لمستشفى آخر | راجع تحذير `wccms:check-hospital` |
| `ORA-20001: INVALID_EMPLOYEE` | موظف غير موجود في مستشفى السجل | اختر موظفاً من القائمة |
| `ORA-20001: <رمز آخر>` | تحقق داخل procedure رفض البيانات (مثل `REQUEST_ALREADY_DECIDED`) | الرمز يقول السبب؛ العملية كاملة أُلغيت |
| `ORA-02291: parent key not found` | المفاتيح المركّبة مفعّلة والرمز غير موجود في HOS | تحقق من الرمز |
| `ORA-02292` عند الحذف في نظام المستشفى | المفاتيح المركّبة مفعّلة وسجل العيادة يشير لهذا المريض أو الموظف | مقصود؛ انظر الخطوة 3 |
| نص عربي يظهر `???` | `NLS_LANG` ليس AL32UTF8 عند تشغيل السكربت | أعد التشغيل بالإعداد الصحيح |
| «بيانات التقييم أطول من المسموح» | XML المراجعة أكبر من 32 KB (حد معامل PL/SQL) | ملاحظات أقصر |
