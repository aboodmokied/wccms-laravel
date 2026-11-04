<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * The complete WCCMS clinic schema for development and tests (MySQL / SQLite). Production is Oracle and is built by
 * wccms_oracle_install.sql, never by this migration.
 *
 * Section 1 builds local copies of the hospital system tables (schema HOS in production) with their real names, the
 * columns WCCMS reads and their composite keys (code, HOS_NO), so the system runs on a developer machine. WCCMS
 * reads them through App\Models\MrPatient, Employee, AdmissionPatient, InpatientStay, HospitalLocation, SexLookup,
 * JobLookup and HospitalInfo, and never writes to them in production.
 *
 * Every WCCMS table that points at a patient or an employee carries one HOS_NO column (e.g. WC_REG_HOS_NO), and every
 * index on such a code includes it. The composite foreign keys to the hospital tables are created only when
 * config('hospital.foreign_keys') is on; the application checks each link before saving either way
 * (App\Models\Concerns\BelongsToHospital).
 *
 * Every identifier stays within Oracle 11g's 30 characters and matches wccms_oracle_install.sql.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Hospital system tables (local copies; in production these are HOS.* in the hospital's Oracle database)
        Schema::create($this->hospitalTable('patients'), function (Blueprint $table) {
            $table->integer('MRP_PATREC_CODE')->comment('رقم السجل الطبي');
            $table->integer('HOS_NO')->comment('رقم المستشفى');
            $table->integer('MRP_SEX_CD')->nullable()->comment('الجنس (C_SEX_TB)');
            $table->date('MRP_DOB')->nullable()->comment('تاريخ الميلاد');
            $table->integer('MRP_ID_TYPE_CD')->default(1)->comment('نوع الوثيقة');
            // HOS: MODIFY ("MRP_ID" NOT NULL DISABLE), so the column can hold NULL there.
            $table->integer('MRP_ID')->nullable()->comment('رقم الوثيقة');
            $table->string('MRP_FIRST_NAME_AR', 50)->nullable()->comment('الاسم الأول بالعربي');
            $table->string('MRP_FATHER_NAME_AR', 50)->nullable()->comment('اسم الأب بالعربي');
            $table->string('MRP_GRANDFATHER_NAME_AR', 50)->nullable()->comment('اسم الجد بالعربي');
            $table->string('MRP_LAST_NAME_AR', 50)->nullable()->comment('اسم العائلة بالعربي');
            $table->string('MRP_MOBILE_NO', 10)->nullable()->comment('رقم الهاتف الخلوي');
            $table->primary(['MRP_PATREC_CODE', 'HOS_NO'], 'MRP_SERIAL_PK');
            $table->unique(['MRP_ID_TYPE_CD', 'MRP_ID', 'HOS_NO'], 'AK_UNIQUE_KEY_MR_PATIE');
            $table->comment('نسخة محلية من HOS.MR_PATIENT_TB (مرضى المستشفى) للتطوير والاختبار');
        });

        Schema::create($this->hospitalTable('jobs'), function (Blueprint $table) {
            $table->integer('JOB_CODE');
            $table->integer('HOS_NO');
            $table->string('JOB_NAME_AR', 100)->nullable();
            $table->string('JOB_NAME_EN', 100)->nullable();
            $table->primary(['JOB_CODE', 'HOS_NO'], 'C_JOB_PK');
            $table->comment('نسخة محلية من HOS.C_JOB_TB (المسميات الوظيفية)');
        });

        Schema::create($this->hospitalTable('employees'), function (Blueprint $table) {
            $table->integer('EMP_SERIAL')->comment('رقم الموظف');
            $table->integer('HOS_NO')->comment('رقم المستشفى');
            $table->string('EMP_FIRST_NAME_AR', 50)->nullable();
            $table->string('EMP_FATHER_NAME_AR', 50)->nullable();
            $table->string('EMP_GRANDFATHER_NAME_AR', 50)->nullable();
            $table->string('EMP_LAST_NAME_AR', 50)->nullable();
            $table->integer('EMP_JOB_CD')->nullable()->comment('المسمى الوظيفي (C_JOB_TB)');
            $table->integer('EMP_ACTIVE')->nullable()->comment('1 = على رأس عمله');
            $table->integer('EMP_REC_CD')->nullable()->comment('حالة سجل الموظف (1 في قوائم المستشفى)');
            $table->integer('USER_TYPE')->nullable()->comment('نوع المستخدم (3 = ممرض في HOS_USERS_VW)');
            $table->primary(['EMP_SERIAL', 'HOS_NO'], 'EMP_SERIAL_PK');
            $table->comment('نسخة محلية من HOS.EMPLOYEE_TB (موظفو المستشفى)');
        });

        Schema::create($this->hospitalTable('locations'), function (Blueprint $table) {
            $table->integer('LOC_CODE');
            $table->integer('HOS_NO');
            $table->string('LOC_NAME_AR', 200)->nullable();
            $table->string('LOC_NAME_EN', 200)->nullable();
            $table->primary(['LOC_CODE', 'HOS_NO'], 'PK_C_LOCATION_TB');
            $table->comment('نسخة محلية من HOS.C_LOCATION_TB (الأقسام والعيادات)');
        });

        Schema::create($this->hospitalTable('admissions'), function (Blueprint $table) {
            $table->integer('ADMISSION_CODE')->comment('رقم الدخول للمستشفى');
            $table->integer('HOS_NO');
            $table->integer('ADMISSION_PATREC_CD')->comment('رقم المريض');
            $table->integer('ADMISSION_LOC_CD')->nullable()->comment('القسم عند الدخول');
            $table->dateTime('ADMISSION_ENTER_DATE')->comment('تاريخ ووقت دخول المستشفى');
            $table->integer('ADMISSION_ENTER_DOC_CD')->nullable()->comment('الطبيب المعالج (EMP_SERIAL)');
            $table->dateTime('ADMISSION_OUT_DATE')->nullable()->comment('تاريخ ووقت الخروج من المستشفى');
            $table->primary(['ADMISSION_CODE', 'HOS_NO'], 'PK_ADMISSION_PATIENT_TB');
            $table->index(['ADMISSION_PATREC_CD', 'HOS_NO'], 'IX_ADM_PATREC');
            $table->comment('نسخة محلية من HOS.ADMISSION_PATIENT_TB (دخولات المستشفى)');
        });

        Schema::create($this->hospitalTable('inpatient_stays'), function (Blueprint $table) {
            $table->integer('INPATIENT_CODE')->comment('رقم الدخول للقسم');
            $table->integer('HOS_NO');
            $table->dateTime('INPATIENT_DATE')->nullable()->comment('تاريخ ووقت الدخول للقسم');
            $table->integer('INPATIENT_SUP_LOC_CD')->nullable()->comment('قسم الإشراف');
            $table->integer('INPATIENT_RESD_LOC_CD')->nullable()->comment('قسم الإقامة');
            $table->integer('INPATIENT_ADMISSION_CD')->nullable()->comment('رقم دخول المستشفى');
            $table->dateTime('INPATIENT_OUT_DATE')->nullable()->comment('تاريخ الخروج من القسم');
            $table->integer('INPATIENT_CANCELED')->default(0)->comment('0 = غير ملغى');
            $table->primary(['INPATIENT_CODE', 'HOS_NO'], 'SEC_INPATIENT_TB_PK');
            $table->index(['INPATIENT_ADMISSION_CD', 'HOS_NO'], 'IX_INP_ADMISSION');
            $table->comment('نسخة محلية من HOS.INPATIENT_ADM_TB (الإقامة في الأقسام)');
        });

        Schema::create($this->hospitalTable('sexes'), function (Blueprint $table) {
            $table->integer('SEX_CODE');
            $table->integer('HOS_NO');
            $table->string('SEX_NAME_AR', 20)->nullable();
            $table->string('SEX_NAME_EN', 20)->nullable();
            $table->primary(['SEX_CODE', 'HOS_NO'], 'C_SEX_PK');
            $table->comment('نسخة محلية من HOS.C_SEX_TB (الجنس)');
        });

        Schema::create($this->hospitalTable('hospitals'), function (Blueprint $table) {
            $table->integer('H_CODE');
            $table->string('H_NAME_AR', 100)->nullable();
            $table->string('H_NAME_EN', 100)->nullable();
            $table->integer('H_CURR')->nullable()->comment('1 = المستشفى الذي تتبع له القاعدة');
            $table->integer('HOS_NO')->nullable();
            $table->primary('H_CODE', 'C_HOSPITAL_PK');
            $table->comment('نسخة محلية من HOS.C_HOSPITAL_TB (المستشفيات)');
        });

        // 2. User Accounts (owned by WCCMS; the user-to-employee link lives here, not on the hospital table)
        Schema::create('wc_users_tb', function (Blueprint $table) {
            $table->char('PER_USER_CODE', 36)->primary()->comment('المعرّف الفريد للمستخدم (UUID) - المفتاح الأساسي');
            $table->string('PER_USER_FULL_NAME', 255)->nullable()->comment('الاسم الكامل للمستخدم');
            $table->string('PER_USER_USERNAME', 100)->unique('UQ_WC_USERS_USERNAME')->comment('اسم المستخدم لتسجيل الدخول (فريد)');
            $table->string('PER_USER_EMAIL', 255)->nullable()->comment('البريد الإلكتروني للمستخدم');
            $table->string('PER_USER_PASSWORD', 255)->nullable()->comment('كلمة المرور المشفرة (Hash)');
            $table->string('PER_USER_ROLE', 20)->nullable()->comment('دور وصلاحيات المستخدم (ADMIN / NURSE)');
            $table->smallInteger('PER_USER_IS_ACTIVE')->default(1)->comment('حالة حساب المستخدم: 1 = فعّال، 0 = معطّل');
            $table->integer('PER_USER_EMP_CD')->nullable()->comment('الموظف المرتبط بالحساب (EMPLOYEE_TB.EMP_SERIAL، اختياري، موظف واحد لكل حساب)');
            $table->integer('PER_USER_HOS_NO')->comment('رقم المستشفى للموظف المرتبط (HOS_NO)، من HOSPITAL_NO');

            // NULLs are distinct in MySQL and SQLite, so accounts without an employee never collide.
            $table->unique(['PER_USER_EMP_CD', 'PER_USER_HOS_NO'], 'UX_WC_USERS_EMP');
            $this->hospitalForeign($table, 'PER_USER_EMP_CD', 'PER_USER_HOS_NO', 'employees', 'FK_WC_USERS_EMPLOYEE');
            $table->comment('حسابات المستخدمين الذين يسجلون الدخول إلى النظام');
        });

        // 4. Master Reference Tables
        Schema::create('wc_department_tb', function (Blueprint $table) {
            $table->char('WC_DEPT_CODE', 36)->primary()->comment('المعرّف الفريد للقسم (UUID) - المفتاح الأساسي');
            $table->string('WC_DEPT_NAME', 255)->comment('اسم القسم الطّبي المحوِّل');
            $table->smallInteger('WC_DEPT_IS_ACTIVE')->default(1)->comment('حالة القسم: 1 = فعّال، 0 = غير فعّال');
            $table->comment('مرجع: الأقسام الطبية المحوِّلة للمرضى');
        });

        Schema::create('wc_pmh_tb', function (Blueprint $table) {
            $table->char('WC_PMH_CODE', 36)->primary()->comment('المعرّف الفريد للحالة المرضية السابقة (UUID) - المفتاح الأساسي');
            $table->string('WC_PMH_NAME', 255)->comment('اسم الحالة المرضية السابقة (مثل السكري، الضغط)');
            $table->smallInteger('WC_PMH_IS_ACTIVE')->default(1)->comment('حالة العنصر المرجعي: 1 = فعّال، 0 = غير فعّال');
            $table->comment('مرجع: التاريخ المرضي السابق للمريض (Past Medical History)');
        });

        Schema::create('wc_diagnosis_tb', function (Blueprint $table) {
            $table->char('WC_DIAG_CODE', 36)->primary()->comment('المعرّف الفريد للتشخيص (UUID) - المفتاح الأساسي');
            $table->string('WC_DIAG_NAME', 255)->comment('اسم التشخيص الطبي للجرح');
            $table->smallInteger('WC_DIAG_IS_ACTIVE')->default(1)->comment('حالة التشخيص: 1 = فعّال، 0 = غير فعّال');
            $table->comment('مرجع: قائمة التشخيصات الطبية للجروح');
        });

        Schema::create('wc_wound_type_tb', function (Blueprint $table) {
            $table->char('WC_WTYPE_CODE', 36)->primary()->comment('المعرّف الفريد لنوع الجرح (UUID) - المفتاح الأساسي');
            $table->string('WC_WTYPE_NAME', 255)->comment('اسم نوع الجرح (مثل قرحة وريدية، حرق، جرح جراحي)');
            $table->smallInteger('WC_WTYPE_IS_ACTIVE')->default(1)->comment('حالة نوع الجرح: 1 = فعّال، 0 = غير فعّال');
            $table->comment('مرجع: أنواع الجروح');
        });

        Schema::create('wc_dressing_tb', function (Blueprint $table) {
            $table->char('WC_DRESS_CODE', 36)->primary()->comment('المعرّف الفريد للضماد (UUID) - المفتاح الأساسي');
            $table->string('WC_DRESS_NAME', 255)->comment('اسم الضماد المستعمل في التغطية والتغيير');
            $table->smallInteger('WC_DRESS_IS_ACTIVE')->default(1)->comment('حالة الضماد: 1 = فعّال، 0 = غير فعّال');
            $table->comment('مرجع: أنواع الضمادات الطبية');
        });

        Schema::create('wc_medication_tb', function (Blueprint $table) {
            $table->char('WC_MED_CODE', 36)->primary()->comment('المعرّف الفريد للدواء (UUID) - المفتاح الأساسي');
            $table->string('WC_MED_NAME', 255)->comment('اسم الدواء أو المرهم الموضعي');
            $table->smallInteger('WC_MED_IS_ACTIVE')->default(1)->comment('حالة الدواء: 1 = فعّال، 0 = غير فعّال');
            $table->comment('مرجع: الأدوية والمراهم الموضعية المستخدمة');
        });

        Schema::create('wc_procedure_tb', function (Blueprint $table) {
            $table->char('WC_PROC_CODE', 36)->primary()->comment('المعرّف الفريد للإجراء (UUID) - المفتاح الأساسي');
            $table->string('WC_PROC_NAME', 255)->comment('اسم الإجراء الطبي المُجرى على الجرح');
            $table->smallInteger('WC_PROC_IS_ACTIVE')->default(1)->comment('حالة الإجراء: 1 = فعّال، 0 = غير فعّال');
            $table->comment('مرجع: الإجراءات الطبية التي تُجرى للمريض');
        });

        // 5. Registration Requests
        Schema::create('wc_request_reg_tb', function (Blueprint $table) {
            $table->char('WC_REQ_CODE', 36)->primary()->comment('المعرّف الفريد لطلب التسجيل (UUID) - المفتاح الأساسي');
            $table->integer('WC_REQ_HOS_NO')->comment('رقم المستشفى لرموز المريض والموظفين في هذا الصف (HOS_NO)، من HOSPITAL_NO');
            $table->integer('WC_REQ_PATIENT_CD')->comment('رمز المريض المرجعي (FK -> MR_PATIENT_TB)');
            $table->string('WC_REQ_PAT_NAME_SNAPSHOT', 150)->nullable()->comment('نسخة ثابتة من اسم المريض وقت تقديم الطلب');
            $table->string('WC_REQ_PAT_ID_SNAPSHOT', 50)->nullable()->comment('نسخة ثابتة من رقم هوية المريض وقت تقديم الطلب');
            $table->string('WC_REQ_PAT_PHONE_SNAPSHOT', 30)->nullable()->comment('نسخة ثابتة من هاتف المريض وقت تقديم الطلب');
            $table->integer('WC_REQ_NURSE_CD')->comment('الممرض/ة مقدّم الطلب (FK -> EMPLOYEE_TB)');
            $table->char('WC_REQ_ENTRY_USER_CD', 36)->index('IX_WC_REQ_ENTRY_USER')->comment('المستخدم مدخل الطلب في النظام (FK -> WC_USERS_TB)');
            $table->integer('WC_REQ_REVIEWED_BY_CD')->nullable()->comment('الموظف مراجع الطلب (FK -> EMPLOYEE_TB)');
            $table->string('WC_REQ_STATUS', 20)->default('PENDING')->index('IX_WC_REQ_STATUS')->comment('حالة الطلب (PENDING/ACCEPTED/REJECTED/VISIT_IN_PROGRESS)');
            $table->string('WC_REQ_REJECTION_REASON', 500)->nullable()->comment('سبب رفض الطلب في حال الرفض');
            $table->text('WC_REQ_NOTES')->nullable()->comment('ملاحظات إضافية مرفقة بطلب التحويل');
            $table->dateTime('WC_REQ_CREATED_AT')->comment('تاريخ ووقت تقديم طلب التسجيل');
            $table->dateTime('WC_REQ_REVIEWED_ON')->nullable()->comment('تاريخ ووقت مراجعة واتخاذ القرار بشأن الطلب');

            $table->foreign('WC_REQ_ENTRY_USER_CD', 'FK_WC_REQ_ENTRY_USER')->references('PER_USER_CODE')->on('wc_users_tb');

            $table->index(['WC_REQ_PATIENT_CD', 'WC_REQ_HOS_NO'], 'IX_WC_REQ_PATIENT');
            $table->index(['WC_REQ_NURSE_CD', 'WC_REQ_HOS_NO'], 'IX_WC_REQ_NURSE');
            $table->index(['WC_REQ_REVIEWED_BY_CD', 'WC_REQ_HOS_NO'], 'IX_WC_REQ_REVIEWED_BY');
            $this->hospitalForeign($table, 'WC_REQ_PATIENT_CD', 'WC_REQ_HOS_NO', 'patients', 'FK_WC_REQ_PATIENT');
            $this->hospitalForeign($table, 'WC_REQ_NURSE_CD', 'WC_REQ_HOS_NO', 'employees', 'FK_WC_REQ_NURSE');
            $this->hospitalForeign($table, 'WC_REQ_REVIEWED_BY_CD', 'WC_REQ_HOS_NO', 'employees', 'FK_WC_REQ_REVIEWED_BY');
            $table->comment('طلبات تسجيل المرضى في عيادة العناية بالجروح');
        });

        // 6. Clinical Registration
        Schema::create('wc_registration_tb', function (Blueprint $table) {
            $table->char('WC_REG_CODE', 36)->primary()->comment('المعرّف الفريد للتسجيل السريري (UUID) - المفتاح الأساسي');
            $table->integer('WC_REG_HOS_NO')->comment('رقم المستشفى لرموز المريض والموظفين في هذا الصف (HOS_NO)، من HOSPITAL_NO');
            $table->char('WC_REG_REQUEST_CD', 36)->index('IX_WC_REG_REQUEST')->comment('رمز طلب التسجيل المرتبط (FK -> WC_REQUEST_REG_TB)');
            $table->integer('WC_REG_PATIENT_CD')->comment('رمز المريض (FK -> MR_PATIENT_TB)');
            $table->char('WC_REG_DEPARTMENT_CD', 36)->nullable()->index('IX_WC_REG_DEPARTMENT')->comment('رمز القسم المحوِّل (FK -> WC_DEPARTMENT_TB)');
            $table->char('WC_REG_PROCEDURE_CD', 36)->nullable()->index('IX_WC_REG_PROCEDURE')->comment('رمز الإجراء الطبي عند التسجيل (FK -> WC_PROCEDURE_TB)');
            $table->integer('WC_REG_PROC_PERFORMED_BY_CD')->nullable()->comment('الموظف المنفذ للإجراء الأول (FK -> EMPLOYEE_TB)');
            $table->dateTime('WC_REG_PROCEDURE_PERFORMED_AT')->nullable()->comment('تاريخ ووقت تنفيذ الإجراء الأول');
            $table->text('WC_REG_PMH_NOTES')->nullable()->comment('ملاحظات التاريخ المرضي السريرية المخصصة');
            $table->dateTime('WC_REG_DATE')->comment('تاريخ ووقت تسجيل فتح ملف العناية بالعيادة');
            $table->integer('WC_REG_NURSE_CD')->comment('الممرض/ة المسؤول عن ملف التسجيل (FK -> EMPLOYEE_TB)');
            $table->char('WC_REG_ENTRY_USER_CD', 36)->index('IX_WC_REG_ENTRY_USER')->comment('المستخدم الذي أدخل التسجيل (FK -> WC_USERS_TB)');
            $table->string('WC_REG_STATUS', 20)->default('ACTIVE')->index('IX_WC_REG_STATUS')->comment('حالة التسجيل السريري (ACTIVE / DISCHARGED)');
            $table->date('WC_REG_DISCHARGE_DATE')->nullable()->comment('تاريخ خروج المريض المعتمد سريرياً');
            $table->string('WC_REG_DISCHARGE_TYPE', 30)->nullable()->comment('سبب ونوع الخروج (شفاء، تحويل، وفاة)');
            $table->dateTime('WC_REG_DISCHARGED_AT')->nullable()->comment('تاريخ ووقت تنفيذ إغلاق الملف بالنظام');
            $table->integer('WC_REG_DISCHARGED_BY_EMP_CD')->nullable()->comment('الموظف منفذ قرار التخريج (FK -> EMPLOYEE_TB)');
            $table->boolean('WC_REG_HAS_ALLERGIES')->nullable()->comment('هل لدى المريض حساسية: 1 = نعم، 0 = لا، فارغ = سجل قديم قبل التقييم المنظم');
            $table->text('WC_REG_ALLERGIES_DESCRIPTION')->nullable()->comment('وصف الحساسية (مطلوب عند وجود حساسية وفارغ عند عدمها)');
            $table->boolean('WC_REG_NO_COMORBIDITIES')->nullable()->comment('لا توجد أمراض مصاحبة: 1 = لا توجد (لا تُربط أمراض في WC_REGISTRATION_PMH_TB)');
            $table->dateTime('WC_REG_NEXT_VISIT_DATE')->nullable()->comment('تاريخ الزيارة القادمة في خطة المتابعة');
            $table->string('WC_REG_FOLLOW_UP_FREQUENCY', 100)->nullable()->comment('تكرار المتابعة (مثال: كل أسبوع)');
            $table->text('WC_REG_FOLLOW_UP_OBJECTIVE')->nullable()->comment('هدف خطة المتابعة');

            $table->foreign('WC_REG_REQUEST_CD', 'FK_WC_REG_REQUEST')->references('WC_REQ_CODE')->on('wc_request_reg_tb');
            $table->foreign('WC_REG_DEPARTMENT_CD', 'FK_WC_REG_DEPARTMENT')->references('WC_DEPT_CODE')->on('wc_department_tb');
            $table->foreign('WC_REG_PROCEDURE_CD', 'FK_WC_REG_PROCEDURE')->references('WC_PROC_CODE')->on('wc_procedure_tb');
            $table->foreign('WC_REG_ENTRY_USER_CD', 'FK_WC_REG_ENTRY_USER')->references('PER_USER_CODE')->on('wc_users_tb');

            $table->index(['WC_REG_PATIENT_CD', 'WC_REG_HOS_NO'], 'IX_WC_REG_PATIENT');
            $table->index(['WC_REG_PROC_PERFORMED_BY_CD', 'WC_REG_HOS_NO'], 'IX_WC_REG_PROC_PERFORMED_BY');
            $table->index(['WC_REG_NURSE_CD', 'WC_REG_HOS_NO'], 'IX_WC_REG_NURSE');
            $table->index(['WC_REG_DISCHARGED_BY_EMP_CD', 'WC_REG_HOS_NO'], 'IX_WC_REG_DISCHARGED_BY');
            $this->hospitalForeign($table, 'WC_REG_PATIENT_CD', 'WC_REG_HOS_NO', 'patients', 'FK_WC_REG_PATIENT');
            $this->hospitalForeign($table, 'WC_REG_PROC_PERFORMED_BY_CD', 'WC_REG_HOS_NO', 'employees', 'FK_WC_REG_PROC_PERFORMED_BY');
            $this->hospitalForeign($table, 'WC_REG_NURSE_CD', 'WC_REG_HOS_NO', 'employees', 'FK_WC_REG_NURSE');
            $this->hospitalForeign($table, 'WC_REG_DISCHARGED_BY_EMP_CD', 'WC_REG_HOS_NO', 'employees', 'FK_WC_REG_DISCHARGED_BY');
            $table->comment('التسجيل السريري للمريض في العيادة بعد قبول الطلب');
        });

        // 7. Registration Diagnoses
        Schema::create('wc_reg_diagnosis_tb', function (Blueprint $table) {
            $table->char('WC_RDIAG_CODE', 36)->primary()->comment('المعرّف الفريد لتشخيص الجرح (UUID) - المفتاح الأساسي');
            $table->integer('WC_RDIAG_HOS_NO')->comment('رقم المستشفى لرموز الموظفين في هذا الصف (HOS_NO)، من HOSPITAL_NO');
            $table->char('WC_RDIAG_REGISTRATION_CD', 36)->index('IX_WC_RDIAG_REGISTRATION')->comment('رمز التسجيل السريري التابع له (FK -> WC_REGISTRATION_TB)');
            $table->char('WC_RDIAG_DIAGNOSIS_CD', 36)->index('IX_WC_RDIAG_DIAGNOSIS')->comment('رمز التشخيص المرجعي (FK -> WC_DIAGNOSIS_TB)');
            $table->char('WC_RDIAG_WOUND_TYPE_CD', 36)->index('IX_WC_RDIAG_WOUND_TYPE')->comment('رمز نوع الجرح المرجعي (FK -> WC_WOUND_TYPE_TB)');
            $table->text('WC_RDIAG_DESCRIPTION')->nullable()->comment('الوصف السريري المخصص للجرح وموقعه وتفاصيله');
            $table->integer('WC_RDIAG_CLINICAL_EMP_CD')->nullable()->comment('الموظف السريري المشخص للجرح (FK -> EMPLOYEE_TB)');
            $table->char('WC_RDIAG_ENTRY_USER_CD', 36)->nullable()->index('IX_WC_RDIAG_ENTRY_USER')->comment('المستخدم مدخل التشخيص (FK -> WC_USERS_TB)');

            $table->foreign('WC_RDIAG_REGISTRATION_CD', 'FK_WC_RDIAG_REGISTRATION')->references('WC_REG_CODE')->on('wc_registration_tb');
            $table->foreign('WC_RDIAG_DIAGNOSIS_CD', 'FK_WC_RDIAG_DIAGNOSIS')->references('WC_DIAG_CODE')->on('wc_diagnosis_tb');
            $table->foreign('WC_RDIAG_WOUND_TYPE_CD', 'FK_WC_RDIAG_WOUND_TYPE')->references('WC_WTYPE_CODE')->on('wc_wound_type_tb');
            $table->foreign('WC_RDIAG_ENTRY_USER_CD', 'FK_WC_RDIAG_ENTRY_USER')->references('PER_USER_CODE')->on('wc_users_tb');

            $table->index(['WC_RDIAG_CLINICAL_EMP_CD', 'WC_RDIAG_HOS_NO'], 'IX_WC_RDIAG_CLINICAL_EMP');
            $this->hospitalForeign($table, 'WC_RDIAG_CLINICAL_EMP_CD', 'WC_RDIAG_HOS_NO', 'employees', 'FK_WC_RDIAG_CLINICAL_EMP');
            $table->comment('التشخيصات والجروح المسجَّلة ضمن تسجيل مريض واحد');
        });

        // 8. Scheduled Reviews
        Schema::create('wc_scheduled_review_tb', function (Blueprint $table) {
            $table->char('WC_SREV_CODE', 36)->primary()->comment('المعرّف الفريد للموعد المجدول (UUID) - المفتاح الأساسي');
            $table->char('WC_SREV_REGISTRATION_CD', 36)->index('IX_WC_SREV_REGISTRATION')->comment('رمز التسجيل السريري المجدول له الموعد (FK -> WC_REGISTRATION_TB)');
            $table->char('WC_SREV_CREATED_BY_CD', 36)->nullable()->index('IX_WC_SREV_CREATED_BY')->comment('المستخدم منشئ الموعد (FK -> WC_USERS_TB)');
            $table->dateTime('WC_SREV_SCHEDULED_DATE')->comment('تاريخ ووقت الموعد المستقبلي المجدول');
            $table->integer('WC_SREV_VISIT_SERIAL')->default(1)->comment('الرقم التسلسلي للمراجعة ضمن خطة العلاج');
            $table->string('WC_SREV_STATUS', 20)->default('PENDING')->index('IX_WC_SREV_STATUS')->comment('حالة الموعد (PENDING/COMPLETED/MISSED/CANCELLED)');
            $table->text('WC_SREV_NOTES')->nullable()->comment('ملاحظات وإرشادات خاصة بالموعد');
            $table->dateTime('WC_SREV_CREATED_ON')->nullable()->comment('تاريخ إنشاء وتسجيل الموعد');
            $table->dateTime('WC_SREV_COMPLETED_ON')->nullable()->comment('تاريخ ووقت إتمام الزيارة فعلياً');

            $table->foreign('WC_SREV_REGISTRATION_CD', 'FK_WC_SREV_REGISTRATION')->references('WC_REG_CODE')->on('wc_registration_tb');
            $table->foreign('WC_SREV_CREATED_BY_CD', 'FK_WC_SREV_CREATED_BY')->references('PER_USER_CODE')->on('wc_users_tb');
            $table->comment('مواعيد المراجعات المجدولة مستقبلاً للمريض المسجَّل');
        });

        // 9. Daily Review Visit Header
        Schema::create('wc_daily_review_tb', function (Blueprint $table) {
            $table->char('WC_DREV_CODE', 36)->primary()->comment('المعرّف الفريد للزيارة السريرية (UUID) - المفتاح الأساسي');
            $table->integer('WC_DREV_HOS_NO')->comment('رقم المستشفى لرموز الموظفين في هذا الصف (HOS_NO)، من HOSPITAL_NO');
            $table->char('WC_DREV_REGISTRATION_CD', 36)->index('IX_WC_DREV_REGISTRATION')->comment('رمز التسجيل السريري التابع له الزيارة (FK -> WC_REGISTRATION_TB)');
            $table->char('WC_DREV_SCHEDULED_REVIEW_CD', 36)->nullable()->index('IX_WC_DREV_SCHEDULED')->comment('رمز الموعد المجدول المرتبط إن وجد (FK -> WC_SCHEDULED_REVIEW_TB)');
            $table->dateTime('WC_DREV_VISIT_DATE')->comment('تاريخ ووقت الزيارة والمراجعة الفعلية');
            $table->integer('WC_DREV_NURSE_CD')->comment('الممرض/ة منفذ الزيارة والتقييم (FK -> EMPLOYEE_TB)');
            $table->char('WC_DREV_ENTRY_USER_CD', 36)->index('IX_WC_DREV_ENTRY_USER')->comment('المستخدم مدخل التقييم اليومي (FK -> WC_USERS_TB)');
            $table->string('WC_DREV_OVERALL_STATUS', 30)->index('IX_WC_DREV_OVERALL_STATUS')->comment('التقييم العام الشامل لحالة المريض بالزيارة');
            $table->text('WC_DREV_GENERAL_NOTES')->nullable()->comment('ملاحظات الممرض العامة أثناء الزيارة');
            $table->char('WC_DREV_SUBMISSION_KEY', 36)->nullable()->unique('UQ_WC_DREV_SUBMISSION_KEY')->comment('مفتاح الإرسال الفريد لمنع تكرار حفظ الزيارة نفسها (فارغ للسجلات القديمة)');
            $table->unique('WC_DREV_SCHEDULED_REVIEW_CD', 'UQ_WC_DREV_SCHEDULED');

            $table->foreign('WC_DREV_REGISTRATION_CD', 'FK_WC_DREV_REGISTRATION')->references('WC_REG_CODE')->on('wc_registration_tb');
            $table->foreign('WC_DREV_SCHEDULED_REVIEW_CD', 'FK_WC_DREV_SCHEDULED')->references('WC_SREV_CODE')->on('wc_scheduled_review_tb');
            $table->foreign('WC_DREV_ENTRY_USER_CD', 'FK_WC_DREV_ENTRY_USER')->references('PER_USER_CODE')->on('wc_users_tb');

            $table->index(['WC_DREV_NURSE_CD', 'WC_DREV_HOS_NO'], 'IX_WC_DREV_NURSE');
            $this->hospitalForeign($table, 'WC_DREV_NURSE_CD', 'WC_DREV_HOS_NO', 'employees', 'FK_WC_DREV_NURSE');
            $table->comment('رأس الزيارة/المراجعة الفعلية للمريض');
        });

        // 10. Unified Review Detail Table
        Schema::create('wc_review_detail_tb', function (Blueprint $table) {
            $table->char('WC_REVD_CODE', 36)->primary()->comment('المعرّف الفريد لسطر تقييم الجرح (UUID) - المفتاح الأساسي');
            $table->char('WC_REVD_REVIEW_CD', 36)->index('IX_WC_REVD_REVIEW')->comment('رمز الزيارة المرجعية الأم (FK -> WC_DAILY_REVIEW_TB)');
            $table->string('WC_REVD_REVIEW_TYPE', 20)->default('DAILY')->index('IX_WC_REVD_REVIEW_TYPE')->comment('نوع المراجعة السريرية (DAILY)');
            $table->char('WC_DREVD_REG_DIAGNOSIS_CD', 36)->index('IX_WC_REVD_REG_DIAGNOSIS')->comment('رمز الجرح المشخص الجاري تقييمه (FK -> WC_REG_DIAGNOSIS_TB)');
            $table->text('WC_DREVD_WOUND_DESCRIPTION')->nullable()->comment('وصف حالة الجرح والحجم والإفرازات والأنسجة');
            $table->text('WC_DREVD_WOUND_CHANGES')->nullable()->comment('ملاحظات التغير في حالة الجرح مقارنة بالزيارة السابقة');
            $table->char('WC_DREVD_DRESSING_CD', 36)->nullable()->index('IX_WC_REVD_DRESSING')->comment('رمز الضماد المستعمل في المراجعة (FK -> WC_DRESSING_TB)');
            $table->char('WC_DREVD_MEDICATION_CD', 36)->nullable()->index('IX_WC_REVD_MEDICATION')->comment('رمز العلاج/المرهم المستعمل بالمراجعة (FK -> WC_MEDICATION_TB)');
            $table->string('WC_DREVD_STATUS', 30)->index('IX_WC_REVD_STATUS')->comment('حالة الجرح الخاصة بهذه الزيارة (تحسن/ثبات/تدهور/شفاء)');
            $table->boolean('WC_DREVD_FOLLOW_UP_REQUIRED')->default(false)->comment('هل يتطلب الجرح متابعة خاصة إضافية (1=نعم, 0=لا)');

            $table->foreign('WC_REVD_REVIEW_CD', 'FK_WC_REVD_REVIEW')->references('WC_DREV_CODE')->on('wc_daily_review_tb');
            $table->foreign('WC_DREVD_REG_DIAGNOSIS_CD', 'FK_WC_REVD_REG_DIAGNOSIS')->references('WC_RDIAG_CODE')->on('wc_reg_diagnosis_tb');
            $table->foreign('WC_DREVD_DRESSING_CD', 'FK_WC_REVD_DRESSING')->references('WC_DRESS_CODE')->on('wc_dressing_tb');
            $table->foreign('WC_DREVD_MEDICATION_CD', 'FK_WC_REVD_MEDICATION')->references('WC_MED_CODE')->on('wc_medication_tb');
            $table->comment('تفاصيل تقييم كل جرح داخل الزيارة (جدول موحَّد)');
        });

        // 11. Structured wound assessment (spec 012)
        Schema::create('wc_assessment_choice_tb', function (Blueprint $table) {
            $table->char('WC_ACH_CODE', 36)->primary()->comment('المعرّف الفريد لخيار التقييم (UUID) - المفتاح الأساسي');
            $table->string('WC_ACH_CATEGORY', 40)->comment('فئة الخيار الثابتة (WOUND_STATUS / WOUND_BED / WOUND_MARGIN / EXUDATE_AMOUNT / ...)');
            $table->string('WC_ACH_KEY', 50)->comment('المفتاح الثابت للخيار داخل فئته؛ لا يتغير حفاظاً على السجل التاريخي');
            $table->string('WC_ACH_LABEL_AR', 150)->comment('التسمية العربية للخيار (محفوظة، والمعروض هو الاسم الإنجليزي)');
            $table->string('WC_ACH_LABEL_EN', 150)->nullable()->comment('الاسم الإنجليزي المعروض للخيار (المصطلحات الطبية تُعرض بالإنجليزية)');
            $table->smallInteger('WC_ACH_IS_ACTIVE')->default(1)->comment('حالة الخيار: 1 = فعّال، 0 = معطّل (يبقى مقروءاً في السجلات القديمة)');
            $table->integer('WC_ACH_DISPLAY_ORDER')->default(0)->comment('ترتيب عرض الخيار داخل فئته');
            $table->unique(['WC_ACH_CATEGORY', 'WC_ACH_KEY'], 'UQ_WC_ACH_CATEGORY_KEY');
            $table->index(['WC_ACH_CATEGORY', 'WC_ACH_IS_ACTIVE', 'WC_ACH_DISPLAY_ORDER'], 'IX_WC_ACH_CATEGORY_ACTIVE');
            $table->comment('خيارات التقييم المنظم للجروح (قاع الجرح، الحواف، الإفرازات، الألم، الموقع...)');
        });

        Schema::create('wc_registration_pmh_tb', function (Blueprint $table) {
            $table->char('WC_RPMH_CODE', 36)->primary()->comment('المعرّف الفريد لربط التسجيل بالمرض المزمن (UUID) - المفتاح الأساسي');
            $table->char('WC_RPMH_REGISTRATION_CD', 36)->comment('رمز التسجيل السريري (FK -> WC_REGISTRATION_TB)');
            $table->char('WC_RPMH_PMH_CD', 36)->comment('رمز المرض المزمن من القائمة الأساسية (FK -> WC_PMH_TB)');
            $table->unique(['WC_RPMH_REGISTRATION_CD', 'WC_RPMH_PMH_CD'], 'UQ_WC_RPMH_REG_PMH');
            $table->foreign('WC_RPMH_REGISTRATION_CD', 'FK_WC_RPMH_REGISTRATION')->references('WC_REG_CODE')->on('wc_registration_tb')->cascadeOnDelete();
            $table->foreign('WC_RPMH_PMH_CD', 'FK_WC_RPMH_PMH')->references('WC_PMH_CODE')->on('wc_pmh_tb');
            $table->comment('الأمراض المزمنة (PMH) المسجلة لكل تسجيل سريري');
        });

        Schema::create('wc_wound_assessment_tb', function (Blueprint $table) {
            $table->char('WC_WA_CODE', 36)->primary()->comment('المعرّف الفريد للتقييم المنظم (UUID) - المفتاح الأساسي');
            $table->integer('WC_WA_HOS_NO')->comment('رقم المستشفى لرموز الموظفين في هذا الصف (HOS_NO)، من HOSPITAL_NO');
            $table->char('WC_WA_REG_DIAGNOSIS_CD', 36)->comment('الجرح المقيَّم (FK -> WC_REG_DIAGNOSIS_TB)');
            $table->char('WC_WA_REVIEW_DETAIL_CD', 36)->nullable()->comment('سطر المراجعة لتقييم المتابعة؛ فارغ للتقييم الأولي (FK -> WC_REVIEW_DETAIL_TB)');
            $table->string('WC_WA_TYPE', 20)->comment('نوع التقييم (INITIAL = أولي عند التسجيل / FOLLOW_UP = متابعة في مراجعة)');
            $table->integer('WC_WA_CLINICAL_EMP_CD')->comment('الموظف السريري منفذ التقييم (FK -> EMPLOYEE_TB)');
            $table->integer('WC_WA_WCT_EMP_CD')->nullable()->comment('عضو فريق الجروح المشارك (FK -> EMPLOYEE_TB)');
            $table->integer('WC_WA_DOCTOR_EMP_CD')->nullable()->comment('الطبيب المشارك (FK -> EMPLOYEE_TB)');
            $table->char('WC_WA_ENTRY_USER_CD', 36)->comment('المستخدم مدخل التقييم (FK -> WC_USERS_TB)');
            $table->dateTime('WC_WA_ASSESSED_AT')->comment('تاريخ ووقت التقييم');
            $table->char('WC_WA_WOUND_STATUS_CD', 36)->comment('Wound Status (FK -> WC_ASSESSMENT_CHOICE_TB، فئة WOUND_STATUS)');
            $table->char('WC_WA_EXUDATE_AMOUNT_CD', 36)->comment('Exudate Amount (FK -> WC_ASSESSMENT_CHOICE_TB، فئة EXUDATE_AMOUNT)');
            $table->char('WC_WA_EXUDATE_TYPE_CD', 36)->nullable()->comment('Exudate Type؛ فارغ عندما تكون الإفرازات Dry (FK -> WC_ASSESSMENT_CHOICE_TB)');
            $table->char('WC_WA_ODOR_CD', 36)->comment('Odor (FK -> WC_ASSESSMENT_CHOICE_TB، فئة ODOR)');
            $table->char('WC_WA_PAIN_SEVERITY_CD', 36)->comment('Pain Severity (FK -> WC_ASSESSMENT_CHOICE_TB، فئة PAIN_SEVERITY)');
            $table->boolean('WC_WA_PAIN_AT_DRESSING')->comment('Pain at Dressing Change: 1 = نعم، 0 = لا');
            $table->char('WC_WA_FEVER_CD', 36)->nullable()->comment('Fever (FK -> WC_ASSESSMENT_CHOICE_TB، فئة FEVER)');
            $table->decimal('WC_WA_LENGTH_MM', 8, 2)->nullable()->comment('Length بالمليمتر (أكبر من 0 وحتى 1000)');
            $table->decimal('WC_WA_WIDTH_MM', 8, 2)->nullable()->comment('Width بالمليمتر (أكبر من 0 وحتى 1000)');
            $table->decimal('WC_WA_DEPTH_MM', 8, 2)->nullable()->comment('Depth بالمليمتر (من 0 حتى 1000)');
            $table->char('WC_WA_LOCATION_CD', 36)->nullable()->comment('Location (FK -> WC_ASSESSMENT_CHOICE_TB، فئة LOCATION)');
            $table->char('WC_WA_LATERALITY_CD', 36)->nullable()->comment('Laterality (FK -> WC_ASSESSMENT_CHOICE_TB، فئة LATERALITY)');
            $table->string('WC_WA_LOCATION_NOTES', 500)->nullable()->comment('Location Details نص حر');
            $table->boolean('WC_WA_SWAB_TAKEN')->nullable()->comment('Swab Taken: 1 = نعم، 0 = لا');
            $table->char('WC_WA_NUTRITION_CD', 36)->nullable()->comment('Nutrition (FK -> WC_ASSESSMENT_CHOICE_TB، فئة NUTRITION)');
            $table->char('WC_WA_DRESSING_CD', 36)->nullable()->comment('Type of DSG (FK -> WC_DRESSING_TB)');
            $table->char('WC_WA_MEDICATION_CD', 36)->nullable()->comment('Medication (FK -> WC_MEDICATION_TB)');
            $table->char('WC_WA_PROCEDURE_CD', 36)->nullable()->comment('Procedure (FK -> WC_PROCEDURE_TB)');
            $table->text('WC_WA_TREATMENT_NOTES')->nullable()->comment('Treatment Plan / Notes نص حر');
            $table->text('WC_WA_CLINICAL_OBSERVATION')->nullable()->comment('Clinical Observation نص حر');
            $table->text('WC_WA_WOUND_CHANGES')->nullable()->comment('Wound Changes منذ الزيارة السابقة، نص حر');
            $table->boolean('WC_WA_FOLLOW_UP_REQUIRED')->comment('هل يحتاج الجرح متابعة: 1 = نعم، 0 = لا');

            $table->unique('WC_WA_REVIEW_DETAIL_CD', 'UQ_WC_WA_REVIEW_DETAIL');
            $table->index(['WC_WA_REG_DIAGNOSIS_CD', 'WC_WA_ASSESSED_AT'], 'IX_WC_WA_DIAGNOSIS_ASSESSED');
            $table->foreign('WC_WA_REG_DIAGNOSIS_CD', 'FK_WC_WA_REG_DIAGNOSIS')->references('WC_RDIAG_CODE')->on('wc_reg_diagnosis_tb')->cascadeOnDelete();
            $table->foreign('WC_WA_REVIEW_DETAIL_CD', 'FK_WC_WA_REVIEW_DETAIL')->references('WC_REVD_CODE')->on('wc_review_detail_tb')->cascadeOnDelete();
            $table->foreign('WC_WA_ENTRY_USER_CD', 'FK_WC_WA_ENTRY_USER')->references('PER_USER_CODE')->on('wc_users_tb');
            $table->foreign('WC_WA_WOUND_STATUS_CD', 'FK_WC_WA_WOUND_STATUS')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_EXUDATE_AMOUNT_CD', 'FK_WC_WA_EXUDATE_AMOUNT')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_EXUDATE_TYPE_CD', 'FK_WC_WA_EXUDATE_TYPE')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_ODOR_CD', 'FK_WC_WA_ODOR')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_PAIN_SEVERITY_CD', 'FK_WC_WA_PAIN_SEVERITY')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_FEVER_CD', 'FK_WC_WA_FEVER')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_LOCATION_CD', 'FK_WC_WA_LOCATION')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_LATERALITY_CD', 'FK_WC_WA_LATERALITY')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_NUTRITION_CD', 'FK_WC_WA_NUTRITION')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_DRESSING_CD', 'FK_WC_WA_DRESSING')->references('WC_DRESS_CODE')->on('wc_dressing_tb');
            $table->foreign('WC_WA_MEDICATION_CD', 'FK_WC_WA_MEDICATION')->references('WC_MED_CODE')->on('wc_medication_tb');
            $table->foreign('WC_WA_PROCEDURE_CD', 'FK_WC_WA_PROCEDURE')->references('WC_PROC_CODE')->on('wc_procedure_tb');

            $table->index(['WC_WA_CLINICAL_EMP_CD', 'WC_WA_HOS_NO'], 'IX_WC_WA_CLINICAL_EMP');
            $table->index(['WC_WA_WCT_EMP_CD', 'WC_WA_HOS_NO'], 'IX_WC_WA_WCT_EMP');
            $table->index(['WC_WA_DOCTOR_EMP_CD', 'WC_WA_HOS_NO'], 'IX_WC_WA_DOCTOR_EMP');
            $this->hospitalForeign($table, 'WC_WA_CLINICAL_EMP_CD', 'WC_WA_HOS_NO', 'employees', 'FK_WC_WA_CLINICAL_EMP');
            $this->hospitalForeign($table, 'WC_WA_WCT_EMP_CD', 'WC_WA_HOS_NO', 'employees', 'FK_WC_WA_WCT_EMP');
            $this->hospitalForeign($table, 'WC_WA_DOCTOR_EMP_CD', 'WC_WA_HOS_NO', 'employees', 'FK_WC_WA_DOCTOR_EMP');
            $table->comment('التقييم المنظم لكل جرح: أولي عند التسجيل ومتابعة في كل مراجعة');
        });

        Schema::create('wc_assessment_finding_tb', function (Blueprint $table) {
            $table->char('WC_AF_CODE', 36)->primary()->comment('المعرّف الفريد للنتيجة (UUID) - المفتاح الأساسي');
            $table->char('WC_AF_ASSESSMENT_CD', 36)->comment('التقييم المنظم (FK -> WC_WOUND_ASSESSMENT_TB)');
            $table->char('WC_AF_CHOICE_CD', 36)->comment('الخيار المختار من Wound Bed أو Wound Margins (FK -> WC_ASSESSMENT_CHOICE_TB)');
            $table->unique(['WC_AF_ASSESSMENT_CD', 'WC_AF_CHOICE_CD'], 'UQ_WC_AF_ASSESSMENT_CHOICE');
            $table->foreign('WC_AF_ASSESSMENT_CD', 'FK_WC_AF_ASSESSMENT')->references('WC_WA_CODE')->on('wc_wound_assessment_tb')->cascadeOnDelete();
            $table->foreign('WC_AF_CHOICE_CD', 'FK_WC_AF_CHOICE')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->comment('نتائج قاع الجرح وحوافه المختارة في كل تقييم منظم (اختيار متعدد)');
        });

        // 12. Wound photos (created after the assessments it links to)
        Schema::create('wc_wound_photo_tb', function (Blueprint $table) {
            $table->char('WC_PHOTO_CODE', 36)->primary()->comment('المعرّف الفريد للصورة السريرية (UUID) - المفتاح الأساسي');
            $table->integer('WC_PHOTO_HOS_NO')->comment('رقم المستشفى لرموز الموظفين في هذا الصف (HOS_NO)، من HOSPITAL_NO');
            $table->char('WC_PHOTO_REG_DIAGNOSIS_CD', 36)->index('IX_WC_PHOTO_REG_DIAGNOSIS')->comment('الجرح/التشخيص الذي تتبع له الصورة (FK -> WC_REG_DIAGNOSIS_TB)');
            $table->char('WC_PHOTO_REVIEW_DETAIL_CD', 36)->nullable()->index('IX_WC_PHOTO_REVIEW_DETAIL')->comment('سطر تفاصيل المراجعة اليومية إذا كانت الصورة مأخوذة في مراجعة (FK -> WC_REVIEW_DETAIL_TB)');
            $table->string('WC_PHOTO_STAGE', 20)->index('IX_WC_PHOTO_STAGE')->comment('مرحلة الصورة السريرية (INITIAL = خط الأساس الأولي / REVIEW = أثناء المراجعات)');
            $table->string('WC_PHOTO_PATH', 255)->comment('مسار التخزين الفرعي للصورة في القرص السريري');
            $table->text('WC_PHOTO_CAPTION')->nullable()->comment('الوصف/التعليق السريري على الصورة');
            $table->dateTime('WC_PHOTO_TAKEN_AT')->comment('تاريخ ووقت التقاط الصورة السريرية');
            $table->integer('WC_PHOTO_NURSE_CD')->comment('الممرض/ة المسؤول/ة عن التقاط الصورة (FK -> EMPLOYEE_TB)');
            $table->char('WC_PHOTO_ENTRY_USER_CD', 36)->index('IX_WC_PHOTO_ENTRY_USER')->comment('المستخدم الذي أدخل سجل الصورة في النظام (FK -> WC_USERS_TB)');
            $table->char('WC_PHOTO_ASSESSMENT_CD', 36)->nullable()->index('IX_WC_PHOTO_ASSESSMENT')->comment('التقييم المنظم الذي التُقطت فيه الصورة (FK -> WC_WOUND_ASSESSMENT_TB، فارغ للسجلات القديمة)');

            $table->foreign('WC_PHOTO_REG_DIAGNOSIS_CD', 'FK_WC_PHOTO_REG_DIAGNOSIS')->references('WC_RDIAG_CODE')->on('wc_reg_diagnosis_tb')->onDelete('cascade');
            $table->foreign('WC_PHOTO_REVIEW_DETAIL_CD', 'FK_WC_PHOTO_REVIEW_DETAIL')->references('WC_REVD_CODE')->on('wc_review_detail_tb')->onDelete('set null');
            $table->foreign('WC_PHOTO_ENTRY_USER_CD', 'FK_WC_PHOTO_ENTRY_USER')->references('PER_USER_CODE')->on('wc_users_tb');
            $table->foreign('WC_PHOTO_ASSESSMENT_CD', 'FK_WC_PHOTO_ASSESSMENT')->references('WC_WA_CODE')->on('wc_wound_assessment_tb')->nullOnDelete();

            $table->index(['WC_PHOTO_NURSE_CD', 'WC_PHOTO_HOS_NO'], 'IX_WC_PHOTO_NURSE');
            $this->hospitalForeign($table, 'WC_PHOTO_NURSE_CD', 'WC_PHOTO_HOS_NO', 'employees', 'FK_WC_PHOTO_NURSE');
            $table->comment('جدول التوثيق البصري وصور الجروح السريرية عند القبول وأثناء المراجعات الدوريّة');
        });

        // 13. Monthly report exports (spec 013)
        Schema::create('wc_report_export_tb', function (Blueprint $table) {
            $table->char('WC_RPEX_CODE', 36)->primary()->comment('المعرّف الفريد لعملية تصدير التقرير (UUID) - المفتاح الأساسي');
            $table->char('WC_RPEX_USER_CD', 36)->index('IX_WC_RPEX_USER')->comment('المستخدم الذي صدّر التقرير (FK -> WC_USERS_TB)');
            $table->dateTime('WC_RPEX_EXPORTED_AT')->index('IX_WC_RPEX_EXPORTED_AT')->comment('تاريخ ووقت التصدير');
            $table->date('WC_RPEX_PERIOD_FROM')->comment('أول يوم من الشهر الأول في فترة التقرير');
            $table->date('WC_RPEX_PERIOD_TO')->comment('أول يوم من الشهر الأخير في فترة التقرير');
            $table->text('WC_RPEX_DEPARTMENT_CODES')->nullable()->comment('رموز الأقسام المحددة (JSON)، فارغ = كل الأقسام');
            $table->integer('WC_RPEX_CASE_COUNT')->comment('عدد الحالات في ورقة الحالات');

            $table->foreign('WC_RPEX_USER_CD', 'FK_WC_RPEX_USER')->references('PER_USER_CODE')->on('wc_users_tb');
            $table->comment('سجل عمليات تصدير التقارير الشهرية (من صدّر، ومتى، وأي فترة وأقسام)');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE wc_registration_tb ADD CONSTRAINT wc_reg_allergy_context_check CHECK ((WC_REG_HAS_ALLERGIES IS NULL) OR (WC_REG_HAS_ALLERGIES = 1 AND NULLIF(TRIM(WC_REG_ALLERGIES_DESCRIPTION), '') IS NOT NULL) OR (WC_REG_HAS_ALLERGIES = 0 AND WC_REG_ALLERGIES_DESCRIPTION IS NULL))");
            DB::statement('ALTER TABLE wc_assessment_choice_tb ADD CONSTRAINT wc_ach_active_check CHECK (WC_ACH_IS_ACTIVE IN (0, 1))');
            DB::statement("ALTER TABLE wc_wound_assessment_tb ADD CONSTRAINT wc_wa_type_link_check CHECK ((WC_WA_TYPE = 'INITIAL' AND WC_WA_REVIEW_DETAIL_CD IS NULL) OR (WC_WA_TYPE = 'FOLLOW_UP' AND WC_WA_REVIEW_DETAIL_CD IS NOT NULL))");
            DB::statement('ALTER TABLE wc_wound_assessment_tb ADD CONSTRAINT wc_wa_measurement_check CHECK ((WC_WA_LENGTH_MM IS NULL OR (WC_WA_LENGTH_MM > 0 AND WC_WA_LENGTH_MM <= 1000)) AND (WC_WA_WIDTH_MM IS NULL OR (WC_WA_WIDTH_MM > 0 AND WC_WA_WIDTH_MM <= 1000)) AND (WC_WA_DEPTH_MM IS NULL OR (WC_WA_DEPTH_MM >= 0 AND WC_WA_DEPTH_MM <= 1000)))');
        }

        // 14. Assessment choices required by the forms (same rows as wccms_oracle_install.sql)
        $this->insertAssessmentChoices();

        // 15. Clinical stored procedures (MySQL only; tests on SQLite use App\Services\ClinicalWorkflows\SqliteClinicalWorkflowGateway)
        if (DB::getDriverName() === 'mysql') {
            $this->dropProcedures();
            DB::unprepared($this->insertAssessmentProcedure());
            DB::unprepared($this->acceptRegistrationProcedure());
            DB::unprepared($this->addWoundProcedure());
            DB::unprepared($this->completeReviewProcedure());
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $this->dropProcedures();
        }

        Schema::disableForeignKeyConstraints();

        foreach ([
            'wc_report_export_tb', 'wc_wound_photo_tb', 'wc_assessment_finding_tb', 'wc_wound_assessment_tb',
            'wc_registration_pmh_tb', 'wc_assessment_choice_tb', 'wc_review_detail_tb', 'wc_daily_review_detail_tb',
            'wc_daily_review_tb', 'wc_scheduled_review_tb', 'wc_reg_diagnosis_tb', 'wc_registration_tb',
            'wc_request_reg_tb', 'wc_procedure_tb', 'wc_medication_tb', 'wc_dressing_tb', 'wc_wound_type_tb',
            'wc_diagnosis_tb', 'wc_pmh_tb', 'wc_department_tb', 'wc_users_tb',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        foreach (['hospitals', 'sexes', 'inpatient_stays', 'admissions', 'locations', 'employees', 'jobs', 'patients'] as $key) {
            Schema::dropIfExists($this->hospitalTable($key));
        }

        Schema::enableForeignKeyConstraints();
    }

    private function insertAssessmentChoices(): void
    {
        $rows = [];
        foreach ($this->assessmentChoices() as $category => $choices) {
            foreach ($choices as $order => [$key, $label, $englishLabel]) {
                $rows[] = [
                    'WC_ACH_CODE' => Uuid::uuid5(Uuid::NAMESPACE_URL, "wccms:assessment-choice:{$category}:{$key}")->toString(),
                    'WC_ACH_CATEGORY' => $category,
                    'WC_ACH_KEY' => $key,
                    'WC_ACH_LABEL_AR' => $label,
                    'WC_ACH_LABEL_EN' => $englishLabel,
                    'WC_ACH_IS_ACTIVE' => 1,
                    'WC_ACH_DISPLAY_ORDER' => $order + 1,
                ];
            }
        }

        DB::table('wc_assessment_choice_tb')->insert($rows);
    }

    /**
     * Stable key, Arabic label, English display label. Same values as Database\Seeders\WcAssessmentChoiceSeeder.
     *
     * @return array<string, list<array{string, string, string}>>
     */
    private function assessmentChoices(): array
    {
        return [
            'WOUND_STATUS' => [['FIRST_VISIT', 'الزيارة الأولى', 'First Visit'], ['DETERIORATING', 'متدهور', 'Deteriorating'], ['STATIC', 'ثابت', 'Static'], ['IMPROVING', 'يتحسن', 'Improving']],
            'WOUND_BED' => [['GRANULATION', 'نسيج حبيبي', 'Granulation'], ['NECROTIC', 'نسيج نخري', 'Necrotic'], ['SLOUGHING', 'نسيج رخو متموت', 'Sloughing'], ['EPITHELIALIZING', 'تكوّن ظهارة', 'Epithelializing']],
            'WOUND_MARGIN' => [['NONE_OBSERVED', 'لا توجد علامة مسجلة', 'None Observed'], ['ERYTHEMA', 'احمرار', 'Erythema'], ['EDEMA', 'وذمة', 'Edema'], ['DELAYED_HEALING', 'تأخر الالتئام', 'Delayed Healing'], ['POCKETING', 'جيوب', 'Pocketing']],
            'EXUDATE_AMOUNT' => [['DRY', 'جاف', 'Dry'], ['LOW', 'قليل', 'Low'], ['MEDIUM', 'متوسط', 'Moderate'], ['HIGH', 'غزير', 'High']],
            'EXUDATE_TYPE' => [['WATERY', 'مائي', 'Watery'], ['CLOUDY', 'عكر', 'Cloudy'], ['PURULENT', 'صديدي', 'Purulent'], ['CLEAR', 'صافي', 'Clear']],
            'ODOR' => [['NONE', 'لا توجد رائحة', 'None'], ['SLIGHT', 'خفيفة', 'Slight'], ['MODERATE', 'متوسطة', 'Moderate'], ['OFFENSIVE', 'كريهة', 'Offensive']],
            'PAIN_SEVERITY' => [['NONE', 'لا يوجد ألم', 'None'], ['MODERATE', 'متوسط', 'Moderate'], ['SEVERE', 'شديد', 'Severe']],
            'FEVER' => [['NONE', 'لا توجد حرارة', 'None'], ['LOW', 'حرارة بسيطة', 'Low-grade'], ['HIGH', 'حرارة مرتفعة', 'High']],
            'NUTRITION' => [['WELL_NOURISHED', 'جيد التغذية', 'Well Nourished'], ['MALNOURISHED', 'سوء تغذية', 'Malnourished']],
            'LOCATION' => [['HEAD', 'الرأس', 'Head'], ['ABDOMEN', 'البطن', 'Abdomen'], ['BACK', 'الظهر', 'Back'], ['LEG', 'الساق', 'Leg'], ['BUTTOCKS', 'الأرداف', 'Buttocks'], ['FOOT', 'القدم', 'Foot'], ['HAND', 'اليد', 'Hand'], ['FOREARM', 'الساعد', 'Forearm'], ['OTHER', 'أخرى', 'Other']],
            'LATERALITY' => [['NOT_APPLICABLE', 'لا ينطبق', 'Not Applicable'], ['LEFT', 'يسار', 'Left'], ['RIGHT', 'يمين', 'Right'], ['BILATERAL', 'ثنائي الجانب', 'Bilateral']],
        ];
    }

    /**
     * The local name of a hospital table (config/hospital.php "tables").
     */
    private function hospitalTable(string $key): string
    {
        return config("hospital.tables.{$key}");
    }

    /**
     * The composite foreign key (code, HOS_NO) to a hospital table, created only when config('hospital.foreign_keys')
     * is on. MySQL and SQLite skip the check while the code is NULL (MATCH SIMPLE), so optional links stay optional.
     * With the setting off, App\Models\Concerns\BelongsToHospital and the procedures check each link instead.
     */
    private function hospitalForeign(Blueprint $table, string $codeColumn, string $hospitalColumn, string $key, string $name): void
    {
        if (! config('hospital.foreign_keys')) {
            return;
        }

        $keys = ['patients' => 'MRP_PATREC_CODE', 'employees' => 'EMP_SERIAL'];

        $table->foreign([$codeColumn, $hospitalColumn], $name)
            ->references([$keys[$key], 'HOS_NO'])
            ->on($this->hospitalTable($key));
    }

    private function dropProcedures(): void
    {
        foreach ([
            // Current names (within Oracle's 30 characters) and the names used before the hospital integration.
            'SP_WC_ACCEPT_REGISTRATION', 'SP_WC_ADD_WOUND', 'SP_WC_COMPLETE_REVIEW', 'SP_WC_INSERT_ASSESSMENT',
            'SP_WC_ACCEPT_REGISTRATION_WITH_ASSESSMENTS', 'SP_WC_ADD_WOUND_WITH_ASSESSMENT',
            'SP_WC_COMPLETE_REVIEW_WITH_ASSESSMENTS', 'SP_WC_INSERT_STRUCTURED_ASSESSMENT',
        ] as $procedure) {
            DB::unprepared("DROP PROCEDURE IF EXISTS {$procedure}");
        }
    }

    /**
     * Puts the local employee table's name into a procedure body.
     */
    private function withHospitalTables(string $sql): string
    {
        return str_replace('{EMPLOYEE_TB}', $this->hospitalTable('employees'), $sql);
    }

    private function insertAssessmentProcedure(): string
    {
        return $this->withHospitalTables(<<<'SQL'
CREATE PROCEDURE SP_WC_INSERT_ASSESSMENT(
    IN p_diagnosis CHAR(36), IN p_detail CHAR(36), IN p_type VARCHAR(20), IN p_hos_no INT,
    IN p_shared JSON, IN p_assessment JSON
)
BEGIN
    DECLARE v_assessment CHAR(36) DEFAULT UUID();
    DECLARE v_status CHAR(36); DECLARE v_amount CHAR(36); DECLARE v_type CHAR(36);
    DECLARE v_odor CHAR(36); DECLARE v_pain CHAR(36);
    DECLARE v_clinician INT; DECLARE v_wct INT; DECLARE v_doctor INT;
    DECLARE v_bed_count INT DEFAULT 0; DECLARE v_margin_count INT DEFAULT 0;
    SET v_status = JSON_UNQUOTE(JSON_EXTRACT(p_assessment, '$.wound_status_choice_code'));
    SET v_amount = JSON_UNQUOTE(JSON_EXTRACT(p_assessment, '$.exudate_amount_choice_code'));
    SET v_type = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment, '$.exudate_type_choice_code')), 'null');
    SET v_odor = JSON_UNQUOTE(JSON_EXTRACT(p_assessment, '$.odor_choice_code'));
    SET v_pain = JSON_UNQUOTE(JSON_EXTRACT(p_assessment, '$.pain_severity_choice_code'));
    SET v_clinician = CAST(COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_shared, '$.clinic_nurse_code')), 'null'), NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_shared, '$.nurse_code')), 'null')) AS SIGNED);
    SET v_wct = CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_shared, '$.wct_employee_code')), 'null') AS SIGNED);
    SET v_doctor = CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_shared, '$.doctor_employee_code')), 'null') AS SIGNED);

    -- Every employee must exist in the row's hospital (the composite foreign keys are optional).
    IF v_clinician IS NULL OR NOT EXISTS (SELECT 1 FROM {EMPLOYEE_TB} WHERE EMP_SERIAL=v_clinician AND HOS_NO=p_hos_no)
       OR (v_wct IS NOT NULL AND NOT EXISTS (SELECT 1 FROM {EMPLOYEE_TB} WHERE EMP_SERIAL=v_wct AND HOS_NO=p_hos_no))
       OR (v_doctor IS NOT NULL AND NOT EXISTS (SELECT 1 FROM {EMPLOYEE_TB} WHERE EMP_SERIAL=v_doctor AND HOS_NO=p_hos_no)) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_EMPLOYEE';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM wc_assessment_choice_tb WHERE WC_ACH_CODE=v_status AND WC_ACH_CATEGORY='WOUND_STATUS' AND WC_ACH_IS_ACTIVE=1)
       OR NOT EXISTS (SELECT 1 FROM wc_assessment_choice_tb WHERE WC_ACH_CODE=v_amount AND WC_ACH_CATEGORY='EXUDATE_AMOUNT' AND WC_ACH_IS_ACTIVE=1)
       OR NOT EXISTS (SELECT 1 FROM wc_assessment_choice_tb WHERE WC_ACH_CODE=v_odor AND WC_ACH_CATEGORY='ODOR' AND WC_ACH_IS_ACTIVE=1)
       OR NOT EXISTS (SELECT 1 FROM wc_assessment_choice_tb WHERE WC_ACH_CODE=v_pain AND WC_ACH_CATEGORY='PAIN_SEVERITY' AND WC_ACH_IS_ACTIVE=1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_REQUIRED_ASSESSMENT_CHOICE';
    END IF;
    IF p_type='INITIAL' AND NOT EXISTS (SELECT 1 FROM wc_assessment_choice_tb WHERE WC_ACH_CODE=v_status AND WC_ACH_CATEGORY='WOUND_STATUS' AND WC_ACH_KEY='FIRST_VISIT' AND WC_ACH_IS_ACTIVE=1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INITIAL_STATUS_MUST_BE_FIRST_VISIT';
    END IF;
    IF p_type='FOLLOW_UP' AND EXISTS (SELECT 1 FROM wc_assessment_choice_tb WHERE WC_ACH_CODE=v_status AND WC_ACH_KEY='FIRST_VISIT') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='FOLLOW_UP_STATUS_CANNOT_BE_FIRST_VISIT';
    END IF;
    IF p_type='INITIAL' AND (
        NOT EXISTS (SELECT 1 FROM wc_assessment_choice_tb WHERE WC_ACH_CODE=JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.fever_choice_code')) AND WC_ACH_CATEGORY='FEVER' AND WC_ACH_IS_ACTIVE=1)
        OR NOT EXISTS (SELECT 1 FROM wc_assessment_choice_tb WHERE WC_ACH_CODE=JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.location_choice_code')) AND WC_ACH_CATEGORY='LOCATION' AND WC_ACH_IS_ACTIVE=1)
        OR NOT EXISTS (SELECT 1 FROM wc_assessment_choice_tb WHERE WC_ACH_CODE=JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.nutrition_choice_code')) AND WC_ACH_CATEGORY='NUTRITION' AND WC_ACH_IS_ACTIVE=1)
    ) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_INITIAL_ASSESSMENT_CHOICE'; END IF;
    IF NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.laterality_choice_code')),'null') IS NOT NULL AND NOT EXISTS (SELECT 1 FROM wc_assessment_choice_tb WHERE WC_ACH_CODE=JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.laterality_choice_code')) AND WC_ACH_CATEGORY='LATERALITY' AND WC_ACH_IS_ACTIVE=1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_LATERALITY_CHOICE';
    END IF;

    SELECT COUNT(*), COUNT(DISTINCT jt.code) INTO v_bed_count, @distinct_beds
    FROM JSON_TABLE(COALESCE(JSON_EXTRACT(p_assessment,'$.wound_bed_choice_codes'), JSON_ARRAY()), '$[*]' COLUMNS(code CHAR(36) PATH '$')) jt
    JOIN wc_assessment_choice_tb c ON c.WC_ACH_CODE=jt.code AND c.WC_ACH_CATEGORY='WOUND_BED' AND c.WC_ACH_IS_ACTIVE=1;
    IF v_bed_count=0 OR v_bed_count<>JSON_LENGTH(COALESCE(JSON_EXTRACT(p_assessment,'$.wound_bed_choice_codes'), JSON_ARRAY())) OR v_bed_count<>@distinct_beds THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_WOUND_BED_CHOICES';
    END IF;

    SELECT COUNT(*), COUNT(DISTINCT jt.code) INTO v_margin_count, @distinct_margins
    FROM JSON_TABLE(COALESCE(JSON_EXTRACT(p_assessment,'$.wound_margin_choice_codes'), JSON_ARRAY()), '$[*]' COLUMNS(code CHAR(36) PATH '$')) jt
    JOIN wc_assessment_choice_tb c ON c.WC_ACH_CODE=jt.code AND c.WC_ACH_CATEGORY='WOUND_MARGIN' AND c.WC_ACH_IS_ACTIVE=1;
    IF (p_type='INITIAL' AND v_margin_count=0)
       OR v_margin_count<>JSON_LENGTH(COALESCE(JSON_EXTRACT(p_assessment,'$.wound_margin_choice_codes'), JSON_ARRAY())) OR v_margin_count<>@distinct_margins THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_WOUND_MARGIN_CHOICES';
    END IF;
    IF v_margin_count>1 AND EXISTS (
        SELECT 1 FROM JSON_TABLE(JSON_EXTRACT(p_assessment,'$.wound_margin_choice_codes'), '$[*]' COLUMNS(code CHAR(36) PATH '$')) jt
        JOIN wc_assessment_choice_tb c ON c.WC_ACH_CODE=jt.code AND c.WC_ACH_KEY='NONE_OBSERVED'
    ) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='NONE_MARGIN_IS_EXCLUSIVE'; END IF;

    IF EXISTS (SELECT 1 FROM wc_assessment_choice_tb WHERE WC_ACH_CODE=v_amount AND WC_ACH_KEY='DRY') AND v_type IS NOT NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='DRY_WOUND_FORBIDS_EXUDATE_TYPE';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM wc_assessment_choice_tb WHERE WC_ACH_CODE=v_amount AND WC_ACH_KEY='DRY')
       AND (v_type IS NULL OR NOT EXISTS (SELECT 1 FROM wc_assessment_choice_tb WHERE WC_ACH_CODE=v_type AND WC_ACH_CATEGORY='EXUDATE_TYPE' AND WC_ACH_IS_ACTIVE=1)) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='EXUDATE_TYPE_REQUIRED';
    END IF;
    IF NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.dressing_code')),'null') IS NULL AND NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.medication_code')),'null') IS NULL
       AND NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.procedure_code')),'null') IS NULL AND NULLIF(NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.treatment_notes'))),''),'null') IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='TREATMENT_REQUIRED';
    END IF;
    IF NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.dressing_code')),'null') IS NOT NULL AND NOT EXISTS (SELECT 1 FROM wc_dressing_tb WHERE WC_DRESS_CODE=JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.dressing_code')) AND WC_DRESS_IS_ACTIVE=1) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_DRESSING'; END IF;
    IF NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.medication_code')),'null') IS NOT NULL AND NOT EXISTS (SELECT 1 FROM wc_medication_tb WHERE WC_MED_CODE=JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.medication_code')) AND WC_MED_IS_ACTIVE=1) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_MEDICATION'; END IF;
    IF NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.procedure_code')),'null') IS NOT NULL AND NOT EXISTS (SELECT 1 FROM wc_procedure_tb WHERE WC_PROC_CODE=JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.procedure_code')) AND WC_PROC_IS_ACTIVE=1) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_PROCEDURE'; END IF;
    IF (p_type='INITIAL' AND (CAST(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.length_mm')) AS DECIMAL(8,2))<=0
       OR CAST(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.width_mm')) AS DECIMAL(8,2))<=0
       OR CAST(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.depth_mm')) AS DECIMAL(8,2))<0))
       OR CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.length_mm')),0) AS DECIMAL(8,2))>1000
       OR CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.width_mm')),0) AS DECIMAL(8,2))>1000
       OR CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.depth_mm')),0) AS DECIMAL(8,2))>1000 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_MEASUREMENTS';
    END IF;

    INSERT INTO wc_wound_assessment_tb (
        WC_WA_CODE,WC_WA_HOS_NO,WC_WA_REG_DIAGNOSIS_CD,WC_WA_REVIEW_DETAIL_CD,WC_WA_TYPE,WC_WA_CLINICAL_EMP_CD,WC_WA_WCT_EMP_CD,WC_WA_DOCTOR_EMP_CD,
        WC_WA_ENTRY_USER_CD,WC_WA_ASSESSED_AT,WC_WA_WOUND_STATUS_CD,WC_WA_EXUDATE_AMOUNT_CD,WC_WA_EXUDATE_TYPE_CD,WC_WA_ODOR_CD,WC_WA_PAIN_SEVERITY_CD,
        WC_WA_PAIN_AT_DRESSING,WC_WA_FEVER_CD,WC_WA_LENGTH_MM,WC_WA_WIDTH_MM,WC_WA_DEPTH_MM,WC_WA_LOCATION_CD,WC_WA_LATERALITY_CD,WC_WA_LOCATION_NOTES,
        WC_WA_SWAB_TAKEN,WC_WA_NUTRITION_CD,WC_WA_DRESSING_CD,WC_WA_MEDICATION_CD,WC_WA_PROCEDURE_CD,WC_WA_TREATMENT_NOTES,WC_WA_CLINICAL_OBSERVATION,
        WC_WA_WOUND_CHANGES,WC_WA_FOLLOW_UP_REQUIRED
    ) VALUES (
        v_assessment,p_hos_no,p_diagnosis,p_detail,p_type,v_clinician,v_wct,v_doctor,
        JSON_UNQUOTE(JSON_EXTRACT(p_shared,'$.entry_user_code')), COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_shared,'$.assessed_at')),'null'),NOW()),
        v_status,v_amount,v_type,v_odor,v_pain,CASE JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.pain_at_dressing')) WHEN 'true' THEN 1 WHEN '1' THEN 1 WHEN 'false' THEN 0 WHEN '0' THEN 0 END,
        NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.fever_choice_code')),'null'), JSON_EXTRACT(p_assessment,'$.length_mm'),JSON_EXTRACT(p_assessment,'$.width_mm'),JSON_EXTRACT(p_assessment,'$.depth_mm'),
        NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.location_choice_code')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.laterality_choice_code')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.location_notes')),'null'),
        CASE JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.swab_taken')) WHEN 'true' THEN 1 WHEN '1' THEN 1 WHEN 'false' THEN 0 WHEN '0' THEN 0 END,NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.nutrition_choice_code')),'null'),
        NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.dressing_code')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.medication_code')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.procedure_code')),'null'),
        NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.treatment_notes')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.clinical_observation')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.wound_changes')),'null'),CASE JSON_UNQUOTE(JSON_EXTRACT(p_assessment,'$.follow_up_required')) WHEN 'false' THEN 0 WHEN '0' THEN 0 ELSE 1 END
    );
    INSERT INTO wc_assessment_finding_tb (WC_AF_CODE,WC_AF_ASSESSMENT_CD,WC_AF_CHOICE_CD)
    SELECT UUID(),v_assessment,code FROM JSON_TABLE(JSON_MERGE_PRESERVE(COALESCE(JSON_EXTRACT(p_assessment,'$.wound_bed_choice_codes'),JSON_ARRAY()),COALESCE(JSON_EXTRACT(p_assessment,'$.wound_margin_choice_codes'),JSON_ARRAY())), '$[*]' COLUMNS(code CHAR(36) PATH '$')) jt;
    INSERT INTO wc_wound_photo_tb (WC_PHOTO_CODE,WC_PHOTO_HOS_NO,WC_PHOTO_REG_DIAGNOSIS_CD,WC_PHOTO_REVIEW_DETAIL_CD,WC_PHOTO_STAGE,WC_PHOTO_PATH,WC_PHOTO_CAPTION,WC_PHOTO_TAKEN_AT,WC_PHOTO_NURSE_CD,WC_PHOTO_ENTRY_USER_CD,WC_PHOTO_ASSESSMENT_CD)
    SELECT COALESCE(photo_code,UUID()),p_hos_no,p_diagnosis,p_detail,IF(p_type='INITIAL','INITIAL','REVIEW'),path,caption,COALESCE(taken_at,NOW()),v_clinician,JSON_UNQUOTE(JSON_EXTRACT(p_shared,'$.entry_user_code')),v_assessment
    FROM JSON_TABLE(COALESCE(JSON_EXTRACT(p_assessment,'$.photos'),JSON_ARRAY()), '$[*]' COLUMNS(photo_code CHAR(36) PATH '$.code',path VARCHAR(255) PATH '$.path',caption TEXT PATH '$.caption',taken_at DATETIME PATH '$.taken_at')) photos;
END
SQL);
    }

    private function acceptRegistrationProcedure(): string
    {
        return $this->withHospitalTables(<<<'SQL'
CREATE PROCEDURE SP_WC_ACCEPT_REGISTRATION(IN p_request CHAR(36), IN p_payload JSON)
BEGIN
    DECLARE v_registration CHAR(36) DEFAULT UUID(); DECLARE v_patient INT; DECLARE v_hos_no INT; DECLARE v_status VARCHAR(30);
    DECLARE v_nurse INT; DECLARE v_entry_user CHAR(36);
    DECLARE v_i INT DEFAULT 0; DECLARE v_count INT; DECLARE v_item JSON; DECLARE v_diagnosis CHAR(36);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
    START TRANSACTION;
    -- The registration takes the request's hospital number; every employee code must exist in it.
    SELECT WC_REQ_PATIENT_CD,WC_REQ_HOS_NO,WC_REQ_STATUS INTO v_patient,v_hos_no,v_status FROM wc_request_reg_tb WHERE WC_REQ_CODE=p_request FOR UPDATE;
    IF v_status NOT IN ('PENDING','VISIT_IN_PROGRESS') OR EXISTS(SELECT 1 FROM wc_registration_tb WHERE WC_REG_REQUEST_CD=p_request) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='REQUEST_ALREADY_DECIDED'; END IF;
    SET v_nurse=CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.clinic_nurse_code')),'null') AS SIGNED);
    SET v_entry_user=JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.entry_user_code'));
    IF v_nurse IS NULL OR NOT EXISTS(SELECT 1 FROM {EMPLOYEE_TB} WHERE EMP_SERIAL=v_nurse AND HOS_NO=v_hos_no) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_EMPLOYEE'; END IF;
    SET v_count=JSON_LENGTH(COALESCE(JSON_EXTRACT(p_payload,'$.diagnoses'),JSON_ARRAY()));
    IF v_count=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='AT_LEAST_ONE_WOUND_REQUIRED'; END IF;
    IF JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.has_allergies')) IN ('true','1') AND NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.allergies_description'))),'') IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='ALLERGY_DESCRIPTION_REQUIRED'; END IF;
    IF JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.no_comorbidities')) IN ('true','1') AND JSON_LENGTH(COALESCE(JSON_EXTRACT(p_payload,'$.pmh_codes'),JSON_ARRAY()))>0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='PMH_NONE_IS_EXCLUSIVE'; END IF;
    IF NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.next_visit_date')),'null') IS NULL AND NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.follow_up_frequency'))),'') IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='FOLLOW_UP_PLAN_REQUIRED'; END IF;
    UPDATE wc_request_reg_tb SET WC_REQ_STATUS='ACCEPTED',WC_REQ_REVIEWED_BY_CD=v_nurse,WC_REQ_REVIEWED_ON=NOW() WHERE WC_REQ_CODE=p_request;
    INSERT INTO wc_registration_tb (WC_REG_CODE,WC_REG_HOS_NO,WC_REG_REQUEST_CD,WC_REG_PATIENT_CD,WC_REG_DEPARTMENT_CD,WC_REG_PROCEDURE_CD,WC_REG_PMH_NOTES,WC_REG_DATE,WC_REG_NURSE_CD,WC_REG_ENTRY_USER_CD,WC_REG_STATUS,WC_REG_HAS_ALLERGIES,WC_REG_ALLERGIES_DESCRIPTION,WC_REG_NO_COMORBIDITIES,WC_REG_NEXT_VISIT_DATE,WC_REG_FOLLOW_UP_FREQUENCY,WC_REG_FOLLOW_UP_OBJECTIVE)
    VALUES(v_registration,v_hos_no,p_request,v_patient,JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.department_code')),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.procedure_code')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.pmh_notes')),'null'),COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.assessed_at')),'null'),NOW()),v_nurse,v_entry_user,'ACTIVE',CASE JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.has_allergies')) WHEN 'true' THEN 1 WHEN '1' THEN 1 WHEN 'false' THEN 0 WHEN '0' THEN 0 END,NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.allergies_description')),'null'),CASE JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.no_comorbidities')) WHEN 'true' THEN 1 WHEN '1' THEN 1 WHEN 'false' THEN 0 WHEN '0' THEN 0 END,NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.next_visit_date')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.follow_up_frequency')),'null'),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.follow_up_objective')));
    INSERT INTO wc_registration_pmh_tb (WC_RPMH_CODE,WC_RPMH_REGISTRATION_CD,WC_RPMH_PMH_CD)
    SELECT UUID(),v_registration,code FROM JSON_TABLE(COALESCE(JSON_EXTRACT(p_payload,'$.pmh_codes'),JSON_ARRAY()),'$[*]' COLUMNS(code CHAR(36) PATH '$')) jt JOIN wc_pmh_tb p ON p.WC_PMH_CODE=jt.code AND p.WC_PMH_IS_ACTIVE=1;
    WHILE v_i<v_count DO
        SET v_item=JSON_EXTRACT(p_payload,CONCAT('$.diagnoses[',v_i,']')); SET v_diagnosis=UUID();
        IF NOT EXISTS(SELECT 1 FROM wc_wound_type_tb WHERE WC_WTYPE_CODE=JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.wound_type_code')) AND WC_WTYPE_IS_ACTIVE=1) OR NOT EXISTS(SELECT 1 FROM wc_diagnosis_tb WHERE WC_DIAG_CODE=JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.diagnosis_code')) AND WC_DIAG_IS_ACTIVE=1) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_WOUND_MASTER_DATA'; END IF;
        INSERT INTO wc_reg_diagnosis_tb (WC_RDIAG_CODE,WC_RDIAG_HOS_NO,WC_RDIAG_REGISTRATION_CD,WC_RDIAG_DIAGNOSIS_CD,WC_RDIAG_WOUND_TYPE_CD,WC_RDIAG_DESCRIPTION,WC_RDIAG_CLINICAL_EMP_CD,WC_RDIAG_ENTRY_USER_CD)
        VALUES(v_diagnosis,v_hos_no,v_registration,JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.diagnosis_code')),JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.wound_type_code')),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.location_notes')),'null'),v_nurse,v_entry_user);
        CALL SP_WC_INSERT_ASSESSMENT(v_diagnosis,NULL,'INITIAL',v_hos_no,p_payload,v_item); SET v_i=v_i+1;
    END WHILE;
    IF NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.next_visit_date')),'null') IS NOT NULL THEN
        INSERT INTO wc_scheduled_review_tb (WC_SREV_CODE,WC_SREV_REGISTRATION_CD,WC_SREV_CREATED_BY_CD,WC_SREV_SCHEDULED_DATE,WC_SREV_VISIT_SERIAL,WC_SREV_STATUS,WC_SREV_NOTES,WC_SREV_CREATED_ON,WC_SREV_COMPLETED_ON)
        VALUES(UUID(),v_registration,v_entry_user,JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.next_visit_date')),1,'PENDING',JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.follow_up_objective')),NOW(),NULL);
    END IF;
    DELETE s FROM wc_scheduled_review_tb s JOIN wc_registration_tb r ON r.WC_REG_CODE=s.WC_SREV_REGISTRATION_CD WHERE r.WC_REG_PATIENT_CD=v_patient AND r.WC_REG_HOS_NO=v_hos_no AND r.WC_REG_CODE<>v_registration AND s.WC_SREV_STATUS='PENDING';
    COMMIT; SELECT v_registration AS registration_code;
END
SQL);
    }

    private function addWoundProcedure(): string
    {
        return $this->withHospitalTables(<<<'SQL'
CREATE PROCEDURE SP_WC_ADD_WOUND(IN p_registration CHAR(36), IN p_payload JSON)
BEGIN
    DECLARE v_status VARCHAR(20); DECLARE v_hos_no INT; DECLARE v_nurse INT; DECLARE v_diagnosis CHAR(36) DEFAULT UUID(); DECLARE v_item JSON;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END; START TRANSACTION;
    SELECT WC_REG_STATUS,WC_REG_HOS_NO INTO v_status,v_hos_no FROM wc_registration_tb WHERE WC_REG_CODE=p_registration FOR UPDATE;
    IF v_status<>'ACTIVE' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='REGISTRATION_NOT_ACTIVE'; END IF;
    SET v_nurse=CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.clinic_nurse_code')),'null') AS SIGNED);
    IF v_nurse IS NULL OR NOT EXISTS(SELECT 1 FROM {EMPLOYEE_TB} WHERE EMP_SERIAL=v_nurse AND HOS_NO=v_hos_no) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_EMPLOYEE'; END IF;
    SET v_item=COALESCE(JSON_EXTRACT(p_payload,'$.assessment'),p_payload);
    IF NOT EXISTS(SELECT 1 FROM wc_wound_type_tb WHERE WC_WTYPE_CODE=JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.wound_type_code')) AND WC_WTYPE_IS_ACTIVE=1) OR NOT EXISTS(SELECT 1 FROM wc_diagnosis_tb WHERE WC_DIAG_CODE=JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.diagnosis_code')) AND WC_DIAG_IS_ACTIVE=1) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_WOUND_MASTER_DATA'; END IF;
    INSERT INTO wc_reg_diagnosis_tb (WC_RDIAG_CODE,WC_RDIAG_HOS_NO,WC_RDIAG_REGISTRATION_CD,WC_RDIAG_DIAGNOSIS_CD,WC_RDIAG_WOUND_TYPE_CD,WC_RDIAG_DESCRIPTION,WC_RDIAG_CLINICAL_EMP_CD,WC_RDIAG_ENTRY_USER_CD)
    VALUES(v_diagnosis,v_hos_no,p_registration,JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.diagnosis_code')),JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.wound_type_code')),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.location_notes')),'null'),v_nurse,JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.entry_user_code')));
    CALL SP_WC_INSERT_ASSESSMENT(v_diagnosis,NULL,'INITIAL',v_hos_no,p_payload,v_item);
    COMMIT; SELECT v_diagnosis AS registration_diagnosis_code;
END
SQL);
    }

    private function completeReviewProcedure(): string
    {
        return $this->withHospitalTables(<<<'SQL'
CREATE PROCEDURE SP_WC_COMPLETE_REVIEW(IN p_registration CHAR(36), IN p_scheduled CHAR(36), IN p_payload JSON)
main: BEGIN
    DECLARE v_review CHAR(36); DECLARE v_status VARCHAR(20); DECLARE v_schedule_status VARCHAR(20); DECLARE v_hos_no INT; DECLARE v_nurse INT;
    DECLARE v_i INT DEFAULT 0; DECLARE v_count INT; DECLARE v_expected INT; DECLARE v_item JSON; DECLARE v_diagnosis CHAR(36); DECLARE v_detail CHAR(36);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
    SELECT WC_DREV_CODE INTO v_review FROM wc_daily_review_tb WHERE WC_DREV_SUBMISSION_KEY=JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.submission_key')) LIMIT 1;
    IF v_review IS NOT NULL THEN SELECT v_review AS daily_review_code; LEAVE main; END IF;
    START TRANSACTION; SELECT WC_REG_STATUS,WC_REG_HOS_NO INTO v_status,v_hos_no FROM wc_registration_tb WHERE WC_REG_CODE=p_registration FOR UPDATE;
    IF p_scheduled IS NULL AND v_status<>'ACTIVE' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='REGISTRATION_NOT_ACTIVE'; END IF;
    IF p_scheduled IS NOT NULL THEN SELECT WC_SREV_STATUS INTO v_schedule_status FROM wc_scheduled_review_tb WHERE WC_SREV_CODE=p_scheduled AND WC_SREV_REGISTRATION_CD=p_registration FOR UPDATE; IF v_schedule_status<>'PENDING' OR v_schedule_status IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SCHEDULE_NOT_PENDING_OR_OWNED'; END IF; END IF;
    SET v_nurse=CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.nurse_code')),'null') AS SIGNED);
    IF v_nurse IS NULL OR NOT EXISTS(SELECT 1 FROM {EMPLOYEE_TB} WHERE EMP_SERIAL=v_nurse AND HOS_NO=v_hos_no) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_EMPLOYEE'; END IF;
    SET v_count=JSON_LENGTH(COALESCE(JSON_EXTRACT(p_payload,'$.details'),JSON_ARRAY())); SELECT COUNT(*) INTO v_expected FROM wc_reg_diagnosis_tb WHERE WC_RDIAG_REGISTRATION_CD=p_registration;
    SELECT COUNT(DISTINCT jt.code) INTO @covered FROM JSON_TABLE(JSON_EXTRACT(p_payload,'$.details'),'$[*]' COLUMNS(code CHAR(36) PATH '$.registration_diagnosis_code')) jt JOIN wc_reg_diagnosis_tb d ON d.WC_RDIAG_CODE=jt.code AND d.WC_RDIAG_REGISTRATION_CD=p_registration;
    IF v_count<>v_expected OR @covered<>v_expected THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='EXACT_DIAGNOSIS_COVERAGE_REQUIRED'; END IF;
    SET v_review=UUID();
    INSERT INTO wc_daily_review_tb (WC_DREV_CODE,WC_DREV_HOS_NO,WC_DREV_REGISTRATION_CD,WC_DREV_SCHEDULED_REVIEW_CD,WC_DREV_VISIT_DATE,WC_DREV_NURSE_CD,WC_DREV_ENTRY_USER_CD,WC_DREV_OVERALL_STATUS,WC_DREV_GENERAL_NOTES,WC_DREV_SUBMISSION_KEY)
    VALUES(v_review,v_hos_no,p_registration,p_scheduled,COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.assessed_at')),'null'),NOW()),v_nurse,JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.entry_user_code')),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.overall_status')),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.general_notes')),'null'),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.submission_key')));
    WHILE v_i<v_count DO
        SET v_item=JSON_EXTRACT(p_payload,CONCAT('$.details[',v_i,']')); SET v_diagnosis=JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.registration_diagnosis_code')); SET v_detail=UUID();
        INSERT INTO wc_review_detail_tb (WC_REVD_CODE,WC_REVD_REVIEW_CD,WC_REVD_REVIEW_TYPE,WC_DREVD_REG_DIAGNOSIS_CD,WC_DREVD_WOUND_DESCRIPTION,WC_DREVD_WOUND_CHANGES,WC_DREVD_DRESSING_CD,WC_DREVD_MEDICATION_CD,WC_DREVD_STATUS,WC_DREVD_FOLLOW_UP_REQUIRED)
        VALUES(v_detail,v_review,IF(p_scheduled IS NULL,'DAILY','SCHEDULED'),v_diagnosis,NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.clinical_observation')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.wound_changes')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.dressing_code')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.medication_code')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.outcome_status')),'null'),CASE JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.follow_up_required')) WHEN 'false' THEN 0 WHEN '0' THEN 0 ELSE 1 END);
        CALL SP_WC_INSERT_ASSESSMENT(v_diagnosis,v_detail,'FOLLOW_UP',v_hos_no,p_payload,v_item); SET v_i=v_i+1;
    END WHILE;
    IF p_scheduled IS NOT NULL THEN UPDATE wc_scheduled_review_tb SET WC_SREV_STATUS='COMPLETED',WC_SREV_COMPLETED_ON=NOW() WHERE WC_SREV_CODE=p_scheduled; END IF;
    UPDATE wc_registration_tb SET WC_REG_NEXT_VISIT_DATE=NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.next_visit_date')),'null'),WC_REG_FOLLOW_UP_FREQUENCY=NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.follow_up_frequency')),'null'),WC_REG_FOLLOW_UP_OBJECTIVE=NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.follow_up_objective')),'null') WHERE WC_REG_CODE=p_registration;
    IF NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.next_visit_date')),'null') IS NOT NULL THEN
        SELECT COALESCE(MAX(WC_SREV_VISIT_SERIAL),0)+1 INTO @serial FROM wc_scheduled_review_tb WHERE WC_SREV_REGISTRATION_CD=p_registration;
        INSERT INTO wc_scheduled_review_tb (WC_SREV_CODE,WC_SREV_REGISTRATION_CD,WC_SREV_CREATED_BY_CD,WC_SREV_SCHEDULED_DATE,WC_SREV_VISIT_SERIAL,WC_SREV_STATUS,WC_SREV_NOTES,WC_SREV_CREATED_ON,WC_SREV_COMPLETED_ON)
        VALUES(UUID(),p_registration,JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.entry_user_code')),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.next_visit_date')),@serial,'PENDING',NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.follow_up_objective')),'null'),NOW(),NULL);
    END IF;
    IF JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.overall_status')) IN ('IMPROVED_DISCHARGE','WORSENED_ADMISSION','DEATH') THEN UPDATE wc_registration_tb SET WC_REG_STATUS='DISCHARGED',WC_REG_DISCHARGED_AT=NOW(),WC_REG_DISCHARGED_BY_EMP_CD=v_nurse WHERE WC_REG_CODE=p_registration; END IF;
    COMMIT; SELECT v_review AS daily_review_code;
END
SQL);
    }
};
