<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. User Accounts
        Schema::create('per_users_tb', function (Blueprint $table) {
            $table->char('PER_USER_CODE', 36)->primary()->comment('المعرّف الفريد للمستخدم (UUID) - المفتاح الأساسي');
            $table->string('PER_USER_FULL_NAME', 255)->nullable()->comment('الاسم الكامل للمستخدم');
            $table->string('PER_USER_USERNAME', 100)->unique()->comment('اسم المستخدم لتسجيل الدخول (فريد)');
            $table->string('PER_USER_EMAIL', 255)->nullable()->comment('البريد الإلكتروني للمستخدم');
            $table->string('PER_USER_PASSWORD', 255)->nullable()->comment('كلمة المرور المشفرة (Hash)');
            $table->string('PER_USER_ROLE', 20)->nullable()->comment('دور وصلاحيات المستخدم (ADMIN / NURSE)');
            $table->smallInteger('PER_USER_IS_ACTIVE')->default(1)->comment('حالة حساب المستخدم: 1 = فعّال، 0 = معطّل');
            $table->comment('حسابات المستخدمين الذين يسجلون الدخول إلى النظام');
        });

        // 2. Employee Directory
        Schema::create('per_employee_tb', function (Blueprint $table) {
            $table->char('PER_EMP_CODE', 36)->primary()->comment('المعرّف الفريد للموظف (UUID) - المفتاح الأساسي');
            $table->string('PER_EMP_FULL_NAME', 255)->comment('الاسم الكامل للموظف');
            $table->string('PER_EMP_JOB_TITLE', 150)->nullable()->comment('المسمى الوظيفي للموظف');
            $table->char('PER_EMP_USER_CD', 36)->nullable()->index()->comment('ربط بحساب المستخدم في PER_USERS_TB (اختياري)');
            $table->smallInteger('PER_EMP_IS_ACTIVE')->default(1)->comment('حالة الموظف: 1 = على رأس عمله، 0 = غير فعّال');

            $table->foreign('PER_EMP_USER_CD')
                ->references('PER_USER_CODE')
                ->on('per_users_tb')
                ->nullOnDelete();
            $table->comment('دليل الموظفين (ممرضون، أطباء، إداريون)');
        });

        // 3. Patient Master Index
        Schema::create('mr_patient_tb', function (Blueprint $table) {
            $table->char('MR_PAT_CODE', 36)->primary()->comment('المعرّف الفريد للمريض (UUID) - المفتاح الأساسي');
            $table->string('MR_PAT_ID_NO', 50)->unique()->comment('رقم هوية المريض (فريد)');
            $table->string('MR_PAT_HOSPITAL_PATIENT_ID', 100)->nullable()->comment('رقم ملف المريض في نظام المستشفى');
            $table->string('MR_PAT_NATIONAL_ID', 100)->nullable()->comment('الرقم الوطني للمريض');
            $table->string('MR_PAT_FULL_NAME', 255)->comment('الاسم الكامل للمريض');
            $table->string('MR_PAT_PHONE', 30)->nullable()->comment('رقم هاتف المريض');
            $table->string('MR_PAT_GENDER', 10)->comment('جنس المريض (ذكر/أنثى)');
            $table->date('MR_PAT_DOB')->nullable()->comment('تاريخ ميلاد المريض');
            $table->string('MR_PAT_SUPERVISING_DOCTOR', 255)->nullable()->comment('اسم الطبيب المشرف على حالة المريض');
            $table->string('MR_PAT_HOSPITAL_DEPARTMENT', 255)->nullable()->comment('القسم الذي يرقد فيه المريض بالمستشفى');
            $table->date('MR_PAT_HOSPITAL_ADMISSION_DATE')->nullable()->comment('تاريخ دخول المريض للمستشفى');
            $table->dateTime('MR_PAT_LAST_SYNCED_AT')->nullable()->comment('تاريخ ووقت آخر مزامنة لبيانات المريض من المستشفى');
            $table->comment('الفهرس الرئيسي للمرضى (Master Patient Index)');
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
            $table->char('WC_REQ_PATIENT_CD', 36)->index()->comment('رمز المريض المرجعي (FK -> MR_PATIENT_TB)');
            $table->string('WC_REQ_PAT_NAME_SNAPSHOT', 150)->comment('نسخة ثابتة من اسم المريض وقت تقديم الطلب');
            $table->string('WC_REQ_PAT_ID_SNAPSHOT', 50)->comment('نسخة ثابتة من رقم هوية المريض وقت تقديم الطلب');
            $table->string('WC_REQ_PAT_PHONE_SNAPSHOT', 30)->nullable()->comment('نسخة ثابتة من هاتف المريض وقت تقديم الطلب');
            $table->char('WC_REQ_NURSE_CD', 36)->index()->comment('الممرض/ة مقدّم الطلب (FK -> PER_EMPLOYEE_TB)');
            $table->char('WC_REQ_ENTRY_USER_CD', 36)->index()->comment('المستخدم مدخل الطلب في النظام (FK -> PER_USERS_TB)');
            $table->char('WC_REQ_REVIEWED_BY_CD', 36)->nullable()->index()->comment('الموظف مراجع الطلب (FK -> PER_EMPLOYEE_TB)');
            $table->string('WC_REQ_STATUS', 20)->default('PENDING')->index()->comment('حالة الطلب (PENDING/ACCEPTED/REJECTED/VISIT_IN_PROGRESS)');
            $table->string('WC_REQ_REJECTION_REASON', 500)->nullable()->comment('سبب رفض الطلب في حال الرفض');
            $table->text('WC_REQ_NOTES')->nullable()->comment('ملاحظات إضافية مرفقة بطلب التحويل');
            $table->dateTime('WC_REQ_CREATED_AT')->comment('تاريخ ووقت تقديم طلب التسجيل');
            $table->dateTime('WC_REQ_REVIEWED_ON')->nullable()->comment('تاريخ ووقت مراجعة واتخاذ القرار بشأن الطلب');

            $table->foreign('WC_REQ_PATIENT_CD')->references('MR_PAT_CODE')->on('mr_patient_tb');
            $table->foreign('WC_REQ_NURSE_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
            $table->foreign('WC_REQ_ENTRY_USER_CD')->references('PER_USER_CODE')->on('per_users_tb');
            $table->foreign('WC_REQ_REVIEWED_BY_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
            $table->comment('طلبات تسجيل المرضى في عيادة العناية بالجروح');
        });

        // 6. Clinical Registration
        Schema::create('wc_registration_tb', function (Blueprint $table) {
            $table->char('WC_REG_CODE', 36)->primary()->comment('المعرّف الفريد للتسجيل السريري (UUID) - المفتاح الأساسي');
            $table->char('WC_REG_REQUEST_CD', 36)->index()->comment('رمز طلب التسجيل المرتبط (FK -> WC_REQUEST_REG_TB)');
            $table->char('WC_REG_PATIENT_CD', 36)->index()->comment('رمز المريض (FK -> MR_PATIENT_TB)');
            $table->char('WC_REG_DEPARTMENT_CD', 36)->nullable()->index()->comment('رمز القسم المحوِّل (FK -> WC_DEPARTMENT_TB)');
            $table->char('WC_REG_PROCEDURE_CD', 36)->nullable()->index()->comment('رمز الإجراء الطبي عند التسجيل (FK -> WC_PROCEDURE_TB)');
            $table->char('WC_REG_PROCEDURE_PERFORMED_BY_CD', 36)->nullable()->index()->comment('الموظف المنفذ للإجراء الأول (FK -> PER_EMPLOYEE_TB)');
            $table->dateTime('WC_REG_PROCEDURE_PERFORMED_AT')->nullable()->comment('تاريخ ووقت تنفيذ الإجراء الأول');
            $table->text('WC_REG_PMH_NOTES')->nullable()->comment('ملاحظات التاريخ المرضي السريرية المخصصة');
            $table->dateTime('WC_REG_DATE')->comment('تاريخ ووقت تسجيل فتح ملف العناية بالعيادة');
            $table->char('WC_REG_NURSE_CD', 36)->index()->comment('الممرض/ة المسؤول عن ملف التسجيل (FK -> PER_EMPLOYEE_TB)');
            $table->char('WC_REG_ENTRY_USER_CD', 36)->index()->comment('المستخدم الذي أدخل التسجيل (FK -> PER_USERS_TB)');
            $table->string('WC_REG_STATUS', 20)->default('ACTIVE')->index()->comment('حالة التسجيل السريري (ACTIVE / DISCHARGED)');
            $table->date('WC_REG_DISCHARGE_DATE')->nullable()->comment('تاريخ خروج المريض المعتمد سريرياً');
            $table->string('WC_REG_DISCHARGE_TYPE', 30)->nullable()->comment('سبب ونوع الخروج (شفاء، تحويل، وفاة)');
            $table->dateTime('WC_REG_DISCHARGED_AT')->nullable()->comment('تاريخ ووقت تنفيذ إغلاق الملف بالنظام');
            $table->char('WC_REG_DISCHARGED_BY_EMP_CD', 36)->nullable()->index()->comment('الموظف منفذ قرار التخريج (FK -> PER_EMPLOYEE_TB)');

            $table->foreign('WC_REG_REQUEST_CD')->references('WC_REQ_CODE')->on('wc_request_reg_tb');
            $table->foreign('WC_REG_PATIENT_CD')->references('MR_PAT_CODE')->on('mr_patient_tb');
            $table->foreign('WC_REG_DEPARTMENT_CD')->references('WC_DEPT_CODE')->on('wc_department_tb');
            $table->foreign('WC_REG_PROCEDURE_CD')->references('WC_PROC_CODE')->on('wc_procedure_tb');
            $table->foreign('WC_REG_PROCEDURE_PERFORMED_BY_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
            $table->foreign('WC_REG_NURSE_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
            $table->foreign('WC_REG_ENTRY_USER_CD')->references('PER_USER_CODE')->on('per_users_tb');
            $table->foreign('WC_REG_DISCHARGED_BY_EMP_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
            $table->comment('التسجيل السريري للمريض في العيادة بعد قبول الطلب');
        });

        // 7. Registration Diagnoses
        Schema::create('wc_reg_diagnosis_tb', function (Blueprint $table) {
            $table->char('WC_RDIAG_CODE', 36)->primary()->comment('المعرّف الفريد لتشخيص الجرح (UUID) - المفتاح الأساسي');
            $table->char('WC_RDIAG_REGISTRATION_CD', 36)->index()->comment('رمز التسجيل السريري التابع له (FK -> WC_REGISTRATION_TB)');
            $table->char('WC_RDIAG_DIAGNOSIS_CD', 36)->index()->comment('رمز التشخيص المرجعي (FK -> WC_DIAGNOSIS_TB)');
            $table->char('WC_RDIAG_WOUND_TYPE_CD', 36)->index()->comment('رمز نوع الجرح المرجعي (FK -> WC_WOUND_TYPE_TB)');
            $table->text('WC_RDIAG_DESCRIPTION')->nullable()->comment('الوصف السريري المخصص للجرح وموقعه وتفاصيله');
            $table->char('WC_RDIAG_CLINICAL_EMP_CD', 36)->nullable()->index()->comment('الموظف السريري المشخص للجرح (FK -> PER_EMPLOYEE_TB)');
            $table->char('WC_RDIAG_ENTRY_USER_CD', 36)->nullable()->index()->comment('المستخدم مدخل التشخيص (FK -> PER_USERS_TB)');

            $table->foreign('WC_RDIAG_REGISTRATION_CD')->references('WC_REG_CODE')->on('wc_registration_tb');
            $table->foreign('WC_RDIAG_DIAGNOSIS_CD')->references('WC_DIAG_CODE')->on('wc_diagnosis_tb');
            $table->foreign('WC_RDIAG_WOUND_TYPE_CD')->references('WC_WTYPE_CODE')->on('wc_wound_type_tb');
            $table->foreign('WC_RDIAG_CLINICAL_EMP_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
            $table->foreign('WC_RDIAG_ENTRY_USER_CD')->references('PER_USER_CODE')->on('per_users_tb');
            $table->comment('التشخيصات والجروح المسجَّلة ضمن تسجيل مريض واحد');
        });

        // 8. Scheduled Reviews
        Schema::create('wc_scheduled_review_tb', function (Blueprint $table) {
            $table->char('WC_SREV_CODE', 36)->primary()->comment('المعرّف الفريد للموعد المجدول (UUID) - المفتاح الأساسي');
            $table->char('WC_SREV_REGISTRATION_CD', 36)->index()->comment('رمز التسجيل السريري المجدول له الموعد (FK -> WC_REGISTRATION_TB)');
            $table->char('WC_SREV_CREATED_BY_CD', 36)->nullable()->index()->comment('المستخدم منشئ الموعد (FK -> PER_USERS_TB)');
            $table->dateTime('WC_SREV_SCHEDULED_DATE')->comment('تاريخ ووقت الموعد المستقبلي المجدول');
            $table->integer('WC_SREV_VISIT_SERIAL')->default(1)->comment('الرقم التسلسلي للمراجعة ضمن خطة العلاج');
            $table->string('WC_SREV_STATUS', 20)->default('PENDING')->index()->comment('حالة الموعد (PENDING/COMPLETED/MISSED/CANCELLED)');
            $table->text('WC_SREV_NOTES')->nullable()->comment('ملاحظات وإرشادات خاصة بالموعد');
            $table->dateTime('WC_SREV_CREATED_ON')->nullable()->comment('تاريخ إنشاء وتسجيل الموعد');
            $table->dateTime('WC_SREV_COMPLETED_ON')->nullable()->comment('تاريخ ووقت إتمام الزيارة فعلياً');

            $table->foreign('WC_SREV_REGISTRATION_CD')->references('WC_REG_CODE')->on('wc_registration_tb');
            $table->foreign('WC_SREV_CREATED_BY_CD')->references('PER_USER_CODE')->on('per_users_tb');
            $table->comment('مواعيد المراجعات المجدولة مستقبلاً للمريض المسجَّل');
        });

        // 9. Daily Review Visit Header
        Schema::create('wc_daily_review_tb', function (Blueprint $table) {
            $table->char('WC_DREV_CODE', 36)->primary()->comment('المعرّف الفريد للزيارة السريرية (UUID) - المفتاح الأساسي');
            $table->char('WC_DREV_REGISTRATION_CD', 36)->index()->comment('رمز التسجيل السريري التابع له الزيارة (FK -> WC_REGISTRATION_TB)');
            $table->char('WC_DREV_SCHEDULED_REVIEW_CD', 36)->nullable()->index()->comment('رمز الموعد المجدول المرتبط إن وجد (FK -> WC_SCHEDULED_REVIEW_TB)');
            $table->dateTime('WC_DREV_VISIT_DATE')->comment('تاريخ ووقت الزيارة والمراجعة الفعلية');
            $table->char('WC_DREV_NURSE_CD', 36)->index()->comment('الممرض/ة منفذ الزيارة والتقييم (FK -> PER_EMPLOYEE_TB)');
            $table->char('WC_DREV_ENTRY_USER_CD', 36)->index()->comment('المستخدم مدخل التقييم اليومي (FK -> PER_USERS_TB)');
            $table->string('WC_DREV_OVERALL_STATUS', 30)->index()->comment('التقييم العام الشامل لحالة المريض بالزيارة');
            $table->text('WC_DREV_GENERAL_NOTES')->nullable()->comment('ملاحظات الممرض العامة أثناء الزيارة');

            $table->foreign('WC_DREV_REGISTRATION_CD')->references('WC_REG_CODE')->on('wc_registration_tb');
            $table->foreign('WC_DREV_SCHEDULED_REVIEW_CD')->references('WC_SREV_CODE')->on('wc_scheduled_review_tb');
            $table->foreign('WC_DREV_NURSE_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
            $table->foreign('WC_DREV_ENTRY_USER_CD')->references('PER_USER_CODE')->on('per_users_tb');
            $table->comment('رأس الزيارة/المراجعة الفعلية للمريض');
        });

        // 10. Unified Review Detail Table
        Schema::create('wc_review_detail_tb', function (Blueprint $table) {
            $table->char('WC_REVD_CODE', 36)->primary()->comment('المعرّف الفريد لسطر تقييم الجرح (UUID) - المفتاح الأساسي');
            $table->char('WC_REVD_REVIEW_CD', 36)->index()->comment('رمز الزيارة المرجعية الأم (FK -> WC_DAILY_REVIEW_TB)');
            $table->string('WC_REVD_REVIEW_TYPE', 20)->default('DAILY')->index()->comment('نوع المراجعة السريرية (DAILY)');
            $table->char('WC_DREVD_REG_DIAGNOSIS_CD', 36)->index()->comment('رمز الجرح المشخص الجاري تقييمه (FK -> WC_REG_DIAGNOSIS_TB)');
            $table->text('WC_DREVD_WOUND_DESCRIPTION')->nullable()->comment('وصف حالة الجرح والحجم والإفرازات والأنسجة');
            $table->text('WC_DREVD_WOUND_CHANGES')->nullable()->comment('ملاحظات التغير في حالة الجرح مقارنة بالزيارة السابقة');
            $table->char('WC_DREVD_DRESSING_CD', 36)->nullable()->index()->comment('رمز الضماد المستعمل في المراجعة (FK -> WC_DRESSING_TB)');
            $table->char('WC_DREVD_MEDICATION_CD', 36)->nullable()->index()->comment('رمز العلاج/المرهم المستعمل بالمراجعة (FK -> WC_MEDICATION_TB)');
            $table->string('WC_DREVD_STATUS', 30)->index()->comment('حالة الجرح الخاصة بهذه الزيارة (تحسن/ثبات/تدهور/شفاء)');
            $table->boolean('WC_DREVD_FOLLOW_UP_REQUIRED')->default(false)->comment('هل يتطلب الجرح متابعة خاصة إضافية (1=نعم, 0=لا)');

            $table->foreign('WC_REVD_REVIEW_CD')->references('WC_DREV_CODE')->on('wc_daily_review_tb');
            $table->foreign('WC_DREVD_REG_DIAGNOSIS_CD')->references('WC_RDIAG_CODE')->on('wc_reg_diagnosis_tb');
            $table->foreign('WC_DREVD_DRESSING_CD')->references('WC_DRESS_CODE')->on('wc_dressing_tb');
            $table->foreign('WC_DREVD_MEDICATION_CD')->references('WC_MED_CODE')->on('wc_medication_tb');
            $table->comment('تفاصيل تقييم كل جرح داخل الزيارة (جدول موحَّد)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('wc_review_detail_tb');
        Schema::dropIfExists('wc_daily_review_detail_tb');
        Schema::dropIfExists('wc_daily_review_tb');
        Schema::dropIfExists('wc_scheduled_review_tb');
        Schema::dropIfExists('wc_reg_diagnosis_tb');
        Schema::dropIfExists('wc_registration_tb');
        Schema::dropIfExists('wc_request_reg_tb');
        Schema::dropIfExists('wc_procedure_tb');
        Schema::dropIfExists('wc_medication_tb');
        Schema::dropIfExists('wc_dressing_tb');
        Schema::dropIfExists('wc_wound_type_tb');
        Schema::dropIfExists('wc_diagnosis_tb');
        Schema::dropIfExists('wc_pmh_tb');
        Schema::dropIfExists('wc_department_tb');
        Schema::dropIfExists('mr_patient_tb');
        Schema::dropIfExists('per_employee_tb');
        Schema::dropIfExists('per_users_tb');

        Schema::enableForeignKeyConstraints();
    }
};
