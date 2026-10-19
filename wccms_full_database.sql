-- ============================================================================
-- Wound Care Clinic Management System (WCCMS) - Complete Database Export
-- Database: wccms
-- Target Engine: MySQL / MariaDB (Portable DB-First Schema)
-- Generated Date: 2026-10-05 10:23:38
-- Synced with the system schema: 2026-10-19
--
-- حالة الجداول (السطر «الحالة» تحت عنوان كل جدول):
--   [خارجي — من نظام المستشفى]  جدول يملكه نظام المستشفى؛ نظامنا يتصل به ويقرأه ولا ينشئه.
--   [جدول النظام]                جدول من جداول العيادة يُبنى في قاعدة نظامنا.
--   [جدول النظام — جديد، غير موجود في القاعدة الحالية]
--                                جدول من جداول العيادة أُضيف إلى هذا الملف ولم يُبنَ بعد في القاعدة.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET TIME_ZONE = '+00:00';

-- ----------------------------------------------------------------------------
-- Table Structure for `per_users_tb`
-- الحالة: [خارجي — من نظام المستشفى] نظامنا يقرأ هذا الجدول ولا ينشئه؛ تعريفه هنا مرجع لمطابقة البنية فقط، والبيانات أدناه للتطوير والاختبار.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `per_users_tb`;
CREATE TABLE `per_users_tb` (
  `PER_USER_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد للمستخدم (UUID) - المفتاح الأساسي',
  `PER_USER_FULL_NAME` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'الاسم الكامل للمستخدم',
  `PER_USER_USERNAME` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'اسم المستخدم لتسجيل الدخول (فريد)',
  `PER_USER_EMAIL` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'البريد الإلكتروني للمستخدم',
  `PER_USER_PASSWORD` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'كلمة المرور المشفرة (Hash)',
  `PER_USER_ROLE` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'دور وصلاحيات المستخدم (ADMIN / NURSE)',
  `PER_USER_IS_ACTIVE` smallint NOT NULL DEFAULT '1' COMMENT 'حالة حساب المستخدم: 1 = فعّال، 0 = معطّل',
  PRIMARY KEY (`PER_USER_CODE`),
  UNIQUE KEY `per_users_tb_per_user_username_unique` (`PER_USER_USERNAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='حسابات المستخدمين الذين يسجلون الدخول إلى النظام';

-- Data for `per_users_tb`
INSERT INTO `per_users_tb` (`PER_USER_CODE`, `PER_USER_FULL_NAME`, `PER_USER_USERNAME`, `PER_USER_EMAIL`, `PER_USER_PASSWORD`, `PER_USER_ROLE`, `PER_USER_IS_ACTIVE`) VALUES ('2759d629-76e6-4c59-bfd2-3a4aeadc2c54', NULL, 'nurse_demo', NULL, NULL, 'NURSE', '1');
INSERT INTO `per_users_tb` (`PER_USER_CODE`, `PER_USER_FULL_NAME`, `PER_USER_USERNAME`, `PER_USER_EMAIL`, `PER_USER_PASSWORD`, `PER_USER_ROLE`, `PER_USER_IS_ACTIVE`) VALUES ('d928b57e-42ef-41e7-9148-31b1df353d77', NULL, 'admin_user', NULL, NULL, 'ADMIN', '1');
INSERT INTO `per_users_tb` (`PER_USER_CODE`, `PER_USER_FULL_NAME`, `PER_USER_USERNAME`, `PER_USER_EMAIL`, `PER_USER_PASSWORD`, `PER_USER_ROLE`, `PER_USER_IS_ACTIVE`) VALUES ('da612d56-593a-4a66-a6ea-16ffafbf73bd', NULL, 'nurse_sarah', NULL, NULL, 'NURSE', '1');
INSERT INTO `per_users_tb` (`PER_USER_CODE`, `PER_USER_FULL_NAME`, `PER_USER_USERNAME`, `PER_USER_EMAIL`, `PER_USER_PASSWORD`, `PER_USER_ROLE`, `PER_USER_IS_ACTIVE`) VALUES ('fc73994f-6aff-4020-a32c-0c00fd2ea484', NULL, 'nurse_khaled', NULL, NULL, 'NURSE', '1');

-- ----------------------------------------------------------------------------
-- Table Structure for `per_employee_tb`
-- الحالة: [خارجي — من نظام المستشفى] نظامنا يقرأ هذا الجدول ولا ينشئه؛ تعريفه هنا مرجع لمطابقة البنية فقط، والبيانات أدناه للتطوير والاختبار.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `per_employee_tb`;
CREATE TABLE `per_employee_tb` (
  `PER_EMP_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد للموظف (UUID) - المفتاح الأساسي',
  `PER_EMP_FULL_NAME` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'الاسم الكامل للموظف',
  `PER_EMP_JOB_TITLE` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'المسمى الوظيفي للموظف',
  `PER_EMP_USER_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ربط بحساب المستخدم في PER_USERS_TB (اختياري)',
  `PER_EMP_IS_ACTIVE` smallint NOT NULL DEFAULT '1' COMMENT 'حالة الموظف: 1 = على رأس عمله، 0 = غير فعّال',
  PRIMARY KEY (`PER_EMP_CODE`),
  KEY `per_employee_tb_per_emp_user_cd_index` (`PER_EMP_USER_CD`),
  CONSTRAINT `per_employee_tb_per_emp_user_cd_foreign` FOREIGN KEY (`PER_EMP_USER_CD`) REFERENCES `per_users_tb` (`PER_USER_CODE`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='دليل الموظفين (ممرضون، أطباء، إداريون)';

-- Data for `per_employee_tb`
INSERT INTO `per_employee_tb` (`PER_EMP_CODE`, `PER_EMP_FULL_NAME`, `PER_EMP_JOB_TITLE`, `PER_EMP_USER_CD`, `PER_EMP_IS_ACTIVE`) VALUES ('15bd4d3b-25dc-4e12-a5cf-3df7dc6b78b9', 'الممرض خالد العلي', NULL, 'fc73994f-6aff-4020-a32c-0c00fd2ea484', '1');
INSERT INTO `per_employee_tb` (`PER_EMP_CODE`, `PER_EMP_FULL_NAME`, `PER_EMP_JOB_TITLE`, `PER_EMP_USER_CD`, `PER_EMP_IS_ACTIVE`) VALUES ('a817e223-7e7c-461c-90db-93742a8fd86d', 'ممرض تجربة الصور السريرية', 'ممرض عيادة الجروح', '2759d629-76e6-4c59-bfd2-3a4aeadc2c54', '1');
INSERT INTO `per_employee_tb` (`PER_EMP_CODE`, `PER_EMP_FULL_NAME`, `PER_EMP_JOB_TITLE`, `PER_EMP_USER_CD`, `PER_EMP_IS_ACTIVE`) VALUES ('bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'الممرضة سارة أحمد', NULL, 'da612d56-593a-4a66-a6ea-16ffafbf73bd', '1');
INSERT INTO `per_employee_tb` (`PER_EMP_CODE`, `PER_EMP_FULL_NAME`, `PER_EMP_JOB_TITLE`, `PER_EMP_USER_CD`, `PER_EMP_IS_ACTIVE`) VALUES ('e0cc2613-30b9-4be5-90e0-4a3b169bdba1', 'د. أحمد السوري (مدير النظام)', NULL, 'd928b57e-42ef-41e7-9148-31b1df353d77', '1');

-- ----------------------------------------------------------------------------
-- Table Structure for `mr_patient_tb`
-- الحالة: [خارجي — من نظام المستشفى] نظامنا يقرأ هذا الجدول ولا ينشئه؛ تعريفه هنا مرجع لمطابقة البنية فقط، والبيانات أدناه للتطوير والاختبار.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `mr_patient_tb`;
CREATE TABLE `mr_patient_tb` (
  `MR_PAT_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد للمريض (UUID) - المفتاح الأساسي',
  `MR_PAT_ID_NO` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'رقم هوية المريض (فريد)',
  `MR_PAT_HOSPITAL_PATIENT_ID` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'رقم ملف المريض في نظام المستشفى',
  `MR_PAT_NATIONAL_ID` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'الرقم الوطني للمريض',
  `MR_PAT_FULL_NAME` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'الاسم الكامل للمريض',
  `MR_PAT_PHONE` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'رقم هاتف المريض',
  `MR_PAT_GENDER` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'جنس المريض (ذكر/أنثى)',
  `MR_PAT_DOB` date DEFAULT NULL COMMENT 'تاريخ ميلاد المريض',
  `MR_PAT_SUPERVISING_DOCTOR` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'اسم الطبيب المشرف على حالة المريض',
  `MR_PAT_HOSPITAL_DEPARTMENT` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'القسم الذي يرقد فيه المريض بالمستشفى',
  `MR_PAT_HOSPITAL_ADMISSION_DATE` date DEFAULT NULL COMMENT 'تاريخ دخول المريض للمستشفى',
  `MR_PAT_LAST_SYNCED_AT` datetime DEFAULT NULL COMMENT 'تاريخ ووقت آخر مزامنة لبيانات المريض من المستشفى',
  PRIMARY KEY (`MR_PAT_CODE`),
  UNIQUE KEY `mr_patient_tb_mr_pat_id_no_unique` (`MR_PAT_ID_NO`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='الفهرس الرئيسي للمرضى (Master Patient Index)';

-- Data for `mr_patient_tb`
INSERT INTO `mr_patient_tb` (`MR_PAT_CODE`, `MR_PAT_ID_NO`, `MR_PAT_HOSPITAL_PATIENT_ID`, `MR_PAT_NATIONAL_ID`, `MR_PAT_FULL_NAME`, `MR_PAT_PHONE`, `MR_PAT_GENDER`, `MR_PAT_DOB`, `MR_PAT_SUPERVISING_DOCTOR`, `MR_PAT_HOSPITAL_DEPARTMENT`, `MR_PAT_HOSPITAL_ADMISSION_DATE`, `MR_PAT_LAST_SYNCED_AT`) VALUES ('13cfac53-76f5-4f2b-b73e-a91cd7a02e3d', 'PAT-1004', NULL, NULL, 'خالد الزهراني', NULL, 'MALE', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `mr_patient_tb` (`MR_PAT_CODE`, `MR_PAT_ID_NO`, `MR_PAT_HOSPITAL_PATIENT_ID`, `MR_PAT_NATIONAL_ID`, `MR_PAT_FULL_NAME`, `MR_PAT_PHONE`, `MR_PAT_GENDER`, `MR_PAT_DOB`, `MR_PAT_SUPERVISING_DOCTOR`, `MR_PAT_HOSPITAL_DEPARTMENT`, `MR_PAT_HOSPITAL_ADMISSION_DATE`, `MR_PAT_LAST_SYNCED_AT`) VALUES ('3dd77f14-fb20-4a8c-a789-a05bb0fdd88b', 'PAT-1003', NULL, NULL, 'يوسف التميمي', NULL, 'MALE', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `mr_patient_tb` (`MR_PAT_CODE`, `MR_PAT_ID_NO`, `MR_PAT_HOSPITAL_PATIENT_ID`, `MR_PAT_NATIONAL_ID`, `MR_PAT_FULL_NAME`, `MR_PAT_PHONE`, `MR_PAT_GENDER`, `MR_PAT_DOB`, `MR_PAT_SUPERVISING_DOCTOR`, `MR_PAT_HOSPITAL_DEPARTMENT`, `MR_PAT_HOSPITAL_ADMISSION_DATE`, `MR_PAT_LAST_SYNCED_AT`) VALUES ('5a019650-e8e4-42b2-af41-e5801070ec65', '409998877', NULL, NULL, 'خالد عبد الله المنصور (تجربة توثيق الصور)', '0599112233', 'MALE', '1985-05-15', NULL, NULL, NULL, NULL);
INSERT INTO `mr_patient_tb` (`MR_PAT_CODE`, `MR_PAT_ID_NO`, `MR_PAT_HOSPITAL_PATIENT_ID`, `MR_PAT_NATIONAL_ID`, `MR_PAT_FULL_NAME`, `MR_PAT_PHONE`, `MR_PAT_GENDER`, `MR_PAT_DOB`, `MR_PAT_SUPERVISING_DOCTOR`, `MR_PAT_HOSPITAL_DEPARTMENT`, `MR_PAT_HOSPITAL_ADMISSION_DATE`, `MR_PAT_LAST_SYNCED_AT`) VALUES ('6ab65c84-ac86-4fee-addc-e5b57fbe1ca4', 'PAT-1001', NULL, NULL, 'عبدالله المحمد', NULL, 'MALE', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `mr_patient_tb` (`MR_PAT_CODE`, `MR_PAT_ID_NO`, `MR_PAT_HOSPITAL_PATIENT_ID`, `MR_PAT_NATIONAL_ID`, `MR_PAT_FULL_NAME`, `MR_PAT_PHONE`, `MR_PAT_GENDER`, `MR_PAT_DOB`, `MR_PAT_SUPERVISING_DOCTOR`, `MR_PAT_HOSPITAL_DEPARTMENT`, `MR_PAT_HOSPITAL_ADMISSION_DATE`, `MR_PAT_LAST_SYNCED_AT`) VALUES ('6ad0543e-05b5-4355-9197-ae19ea463db2', 'PAT-1005', NULL, NULL, 'فاطمة النجار', NULL, 'FEMALE', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `mr_patient_tb` (`MR_PAT_CODE`, `MR_PAT_ID_NO`, `MR_PAT_HOSPITAL_PATIENT_ID`, `MR_PAT_NATIONAL_ID`, `MR_PAT_FULL_NAME`, `MR_PAT_PHONE`, `MR_PAT_GENDER`, `MR_PAT_DOB`, `MR_PAT_SUPERVISING_DOCTOR`, `MR_PAT_HOSPITAL_DEPARTMENT`, `MR_PAT_HOSPITAL_ADMISSION_DATE`, `MR_PAT_LAST_SYNCED_AT`) VALUES ('e2215585-de10-4f9a-8caa-5f2d606f0466', 'PAT-1002', NULL, NULL, 'مريم العباسي', NULL, 'FEMALE', NULL, NULL, NULL, NULL, NULL);

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_department_tb`
-- الحالة: [جدول النظام]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_department_tb`;
CREATE TABLE `wc_department_tb` (
  `WC_DEPT_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد للقسم (UUID) - المفتاح الأساسي',
  `WC_DEPT_NAME` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'اسم القسم الطّبي المحوِّل',
  `WC_DEPT_IS_ACTIVE` smallint NOT NULL DEFAULT '1' COMMENT 'حالة القسم: 1 = فعّال، 0 = غير فعّال',
  PRIMARY KEY (`WC_DEPT_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='مرجع: الأقسام الطبية المحوِّلة للمرضى';

-- Data for `wc_department_tb`
INSERT INTO `wc_department_tb` (`WC_DEPT_CODE`, `WC_DEPT_NAME`, `WC_DEPT_IS_ACTIVE`) VALUES ('11d97034-fb23-4a17-aaaf-85cf2d5768ce', 'General Surgery', '1');
INSERT INTO `wc_department_tb` (`WC_DEPT_CODE`, `WC_DEPT_NAME`, `WC_DEPT_IS_ACTIVE`) VALUES ('3ac8e8dc-99e1-4682-a15d-7f84f70fa255', 'عيادة الجروح التخصصية', '1');
INSERT INTO `wc_department_tb` (`WC_DEPT_CODE`, `WC_DEPT_NAME`, `WC_DEPT_IS_ACTIVE`) VALUES ('aae6dade-4a26-4a4e-8131-51e1c4e2f91a', 'Emergency Department', '1');
INSERT INTO `wc_department_tb` (`WC_DEPT_CODE`, `WC_DEPT_NAME`, `WC_DEPT_IS_ACTIVE`) VALUES ('bd41785c-2ff6-4f09-a401-5a1abc898cf7', 'Wound Care Clinic', '1');
INSERT INTO `wc_department_tb` (`WC_DEPT_CODE`, `WC_DEPT_NAME`, `WC_DEPT_IS_ACTIVE`) VALUES ('d337e1ce-52a5-4285-b1a2-b6414aba7ea0', 'Outpatient Surgery', '1');
INSERT INTO `wc_department_tb` (`WC_DEPT_CODE`, `WC_DEPT_NAME`, `WC_DEPT_IS_ACTIVE`) VALUES ('e487c8f2-6c85-4737-9db3-99f63a944232', 'Dermatology Clinic', '1');

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_pmh_tb`
-- الحالة: [جدول النظام]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_pmh_tb`;
CREATE TABLE `wc_pmh_tb` (
  `WC_PMH_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد للحالة المرضية السابقة (UUID) - المفتاح الأساسي',
  `WC_PMH_NAME` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'اسم الحالة المرضية السابقة (مثل السكري، الضغط)',
  `WC_PMH_IS_ACTIVE` smallint NOT NULL DEFAULT '1' COMMENT 'حالة العنصر المرجعي: 1 = فعّال، 0 = غير فعّال',
  PRIMARY KEY (`WC_PMH_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='مرجع: التاريخ المرضي السابق للمريض (Past Medical History)';

-- Data for `wc_pmh_tb`
INSERT INTO `wc_pmh_tb` (`WC_PMH_CODE`, `WC_PMH_NAME`, `WC_PMH_IS_ACTIVE`) VALUES ('0f70f328-646c-423e-baf9-5600869ee335', 'Chronic Venous Insufficiency', '1');
INSERT INTO `wc_pmh_tb` (`WC_PMH_CODE`, `WC_PMH_NAME`, `WC_PMH_IS_ACTIVE`) VALUES ('34afef5e-ed8c-4186-84d1-e1cf86dba308', 'Diabetes Mellitus Type 2', '1');
INSERT INTO `wc_pmh_tb` (`WC_PMH_CODE`, `WC_PMH_NAME`, `WC_PMH_IS_ACTIVE`) VALUES ('757cd1fd-dddd-4a01-b9bb-af1024596e32', 'Peripheral Artery Disease', '1');
INSERT INTO `wc_pmh_tb` (`WC_PMH_CODE`, `WC_PMH_NAME`, `WC_PMH_IS_ACTIVE`) VALUES ('ab0d9beb-08a6-4de5-8b5c-44f4bfb05d2c', 'Immunosuppressed / Chemo', '1');
INSERT INTO `wc_pmh_tb` (`WC_PMH_CODE`, `WC_PMH_NAME`, `WC_PMH_IS_ACTIVE`) VALUES ('b3b8091d-2366-42ed-9413-9b9af15e4264', 'Essential Hypertension', '1');

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_diagnosis_tb`
-- الحالة: [جدول النظام]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_diagnosis_tb`;
CREATE TABLE `wc_diagnosis_tb` (
  `WC_DIAG_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد للتشخيص (UUID) - المفتاح الأساسي',
  `WC_DIAG_NAME` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'اسم التشخيص الطبي للجرح',
  `WC_DIAG_IS_ACTIVE` smallint NOT NULL DEFAULT '1' COMMENT 'حالة التشخيص: 1 = فعّال، 0 = غير فعّال',
  PRIMARY KEY (`WC_DIAG_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='مرجع: قائمة التشخيصات الطبية للجروح';

-- Data for `wc_diagnosis_tb`
INSERT INTO `wc_diagnosis_tb` (`WC_DIAG_CODE`, `WC_DIAG_NAME`, `WC_DIAG_IS_ACTIVE`) VALUES ('57c300ff-4a95-43ca-8760-21209636a419', 'Pressure Injury Stage III/IV', '1');
INSERT INTO `wc_diagnosis_tb` (`WC_DIAG_CODE`, `WC_DIAG_NAME`, `WC_DIAG_IS_ACTIVE`) VALUES ('70aa21bd-9294-4f47-8a1a-94311675682d', 'Diabetic Foot Ulcer', '1');
INSERT INTO `wc_diagnosis_tb` (`WC_DIAG_CODE`, `WC_DIAG_NAME`, `WC_DIAG_IS_ACTIVE`) VALUES ('869817cb-f14b-4592-be07-17d1199966f7', 'قرحة سكرية من الدرجة الثانية', '1');
INSERT INTO `wc_diagnosis_tb` (`WC_DIAG_CODE`, `WC_DIAG_NAME`, `WC_DIAG_IS_ACTIVE`) VALUES ('8f920bc3-1e51-4111-8b39-13e1da9b56a5', 'Venous Leg Ulcer', '1');
INSERT INTO `wc_diagnosis_tb` (`WC_DIAG_CODE`, `WC_DIAG_NAME`, `WC_DIAG_IS_ACTIVE`) VALUES ('da634060-418d-4f0d-b663-a16e85d7ae85', 'Ischemic Arterial Ulcer', '1');
INSERT INTO `wc_diagnosis_tb` (`WC_DIAG_CODE`, `WC_DIAG_NAME`, `WC_DIAG_IS_ACTIVE`) VALUES ('eb0f37d8-874f-4985-8ead-4a53875ef3f8', 'Surgical Site Infection', '1');

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_wound_type_tb`
-- الحالة: [جدول النظام]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_wound_type_tb`;
CREATE TABLE `wc_wound_type_tb` (
  `WC_WTYPE_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد لنوع الجرح (UUID) - المفتاح الأساسي',
  `WC_WTYPE_NAME` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'اسم نوع الجرح (مثل قرحة وريدية، حرق، جرح جراحي)',
  `WC_WTYPE_IS_ACTIVE` smallint NOT NULL DEFAULT '1' COMMENT 'حالة نوع الجرح: 1 = فعّال، 0 = غير فعّال',
  PRIMARY KEY (`WC_WTYPE_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='مرجع: أنواع الجروح';

-- Data for `wc_wound_type_tb`
INSERT INTO `wc_wound_type_tb` (`WC_WTYPE_CODE`, `WC_WTYPE_NAME`, `WC_WTYPE_IS_ACTIVE`) VALUES ('09b99646-9668-4786-a50b-1a0e64aa82dd', 'Chronic Neuropathic Ulcer', '1');
INSERT INTO `wc_wound_type_tb` (`WC_WTYPE_CODE`, `WC_WTYPE_NAME`, `WC_WTYPE_IS_ACTIVE`) VALUES ('21c9571b-b908-4898-87ed-437916f73b31', 'جرح قدم سكرية deep ulcer', '1');
INSERT INTO `wc_wound_type_tb` (`WC_WTYPE_CODE`, `WC_WTYPE_NAME`, `WC_WTYPE_IS_ACTIVE`) VALUES ('24de1f30-9dd2-41cf-829d-ffab92c3e426', 'Venous Stasis Ulcer', '1');
INSERT INTO `wc_wound_type_tb` (`WC_WTYPE_CODE`, `WC_WTYPE_NAME`, `WC_WTYPE_IS_ACTIVE`) VALUES ('3edae106-707b-4af7-be21-da7d34a312d0', 'Surgical Incisional Wound', '1');
INSERT INTO `wc_wound_type_tb` (`WC_WTYPE_CODE`, `WC_WTYPE_NAME`, `WC_WTYPE_IS_ACTIVE`) VALUES ('695e66d8-4b12-43f4-9be9-f7d1b9428bf2', 'Second Degree Burn', '1');
INSERT INTO `wc_wound_type_tb` (`WC_WTYPE_CODE`, `WC_WTYPE_NAME`, `WC_WTYPE_IS_ACTIVE`) VALUES ('cb76219d-2eb3-40bf-a23d-8150238f30a8', 'Traumatic Laceration', '1');

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_dressing_tb`
-- الحالة: [جدول النظام]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_dressing_tb`;
CREATE TABLE `wc_dressing_tb` (
  `WC_DRESS_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد للضماد (UUID) - المفتاح الأساسي',
  `WC_DRESS_NAME` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'اسم الضماد المستعمل في التغطية والتغيير',
  `WC_DRESS_IS_ACTIVE` smallint NOT NULL DEFAULT '1' COMMENT 'حالة الضماد: 1 = فعّال، 0 = غير فعّال',
  PRIMARY KEY (`WC_DRESS_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='مرجع: أنواع الضمادات الطبية';

-- Data for `wc_dressing_tb`
INSERT INTO `wc_dressing_tb` (`WC_DRESS_CODE`, `WC_DRESS_NAME`, `WC_DRESS_IS_ACTIVE`) VALUES ('930640c3-d86c-4910-9afe-a5e0092daa38', 'Transparent Film Dressing', '1');
INSERT INTO `wc_dressing_tb` (`WC_DRESS_CODE`, `WC_DRESS_NAME`, `WC_DRESS_IS_ACTIVE`) VALUES ('a28bb7c7-c1b2-4aeb-ac7c-258f2eec9bbc', 'Hydrocolloid Bandage', '1');
INSERT INTO `wc_dressing_tb` (`WC_DRESS_CODE`, `WC_DRESS_NAME`, `WC_DRESS_IS_ACTIVE`) VALUES ('c7a64c49-5dc1-497c-97cf-551099f490ba', 'Silver Alginate Dressing', '1');
INSERT INTO `wc_dressing_tb` (`WC_DRESS_CODE`, `WC_DRESS_NAME`, `WC_DRESS_IS_ACTIVE`) VALUES ('dccdc520-5c7f-4ff6-bdaa-260aeac0a4a0', 'Sterile Gauze & Tape', '1');
INSERT INTO `wc_dressing_tb` (`WC_DRESS_CODE`, `WC_DRESS_NAME`, `WC_DRESS_IS_ACTIVE`) VALUES ('ed73a3ac-a561-40bb-a41e-a7b56ee0fab6', 'Absorbent Foam Dressing', '1');

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_medication_tb`
-- الحالة: [جدول النظام]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_medication_tb`;
CREATE TABLE `wc_medication_tb` (
  `WC_MED_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد للدواء (UUID) - المفتاح الأساسي',
  `WC_MED_NAME` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'اسم الدواء أو المرهم الموضعي',
  `WC_MED_IS_ACTIVE` smallint NOT NULL DEFAULT '1' COMMENT 'حالة الدواء: 1 = فعّال، 0 = غير فعّال',
  PRIMARY KEY (`WC_MED_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='مرجع: الأدوية والمراهم الموضعية المستخدمة';

-- Data for `wc_medication_tb`
INSERT INTO `wc_medication_tb` (`WC_MED_CODE`, `WC_MED_NAME`, `WC_MED_IS_ACTIVE`) VALUES ('4a5cccca-82c3-4a62-ada4-035d9cce61d7', 'Povidone-Iodine Antiseptic Wash', '1');
INSERT INTO `wc_medication_tb` (`WC_MED_CODE`, `WC_MED_NAME`, `WC_MED_IS_ACTIVE`) VALUES ('6f4e5a65-4e90-417f-9485-ef10ca0f2cac', 'Normal Saline 0.9% Solution', '1');
INSERT INTO `wc_medication_tb` (`WC_MED_CODE`, `WC_MED_NAME`, `WC_MED_IS_ACTIVE`) VALUES ('8392cd21-8241-460b-a645-f6664b1b964b', 'Topical Antibiotic Ointment (Mupirocin)', '1');
INSERT INTO `wc_medication_tb` (`WC_MED_CODE`, `WC_MED_NAME`, `WC_MED_IS_ACTIVE`) VALUES ('e137b622-fced-4a43-9335-e64e6eb9d425', 'Silver Sulfadiazine Cream 1%', '1');
INSERT INTO `wc_medication_tb` (`WC_MED_CODE`, `WC_MED_NAME`, `WC_MED_IS_ACTIVE`) VALUES ('e816c0cd-8ec5-4483-af27-dafaf9333c00', 'Enzymatic Debriding Ointment', '1');

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_procedure_tb`
-- الحالة: [جدول النظام]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_procedure_tb`;
CREATE TABLE `wc_procedure_tb` (
  `WC_PROC_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد للإجراء (UUID) - المفتاح الأساسي',
  `WC_PROC_NAME` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'اسم الإجراء الطبي المُجرى على الجرح',
  `WC_PROC_IS_ACTIVE` smallint NOT NULL DEFAULT '1' COMMENT 'حالة الإجراء: 1 = فعّال، 0 = غير فعّال',
  PRIMARY KEY (`WC_PROC_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='مرجع: الإجراءات الطبية التي تُجرى للمريض';

-- Data for `wc_procedure_tb`
INSERT INTO `wc_procedure_tb` (`WC_PROC_CODE`, `WC_PROC_NAME`, `WC_PROC_IS_ACTIVE`) VALUES ('1e38fe8e-3d4f-41a1-a823-650304a8fec1', 'Negative Pressure Wound Therapy (NPWT)', '1');
INSERT INTO `wc_procedure_tb` (`WC_PROC_CODE`, `WC_PROC_NAME`, `WC_PROC_IS_ACTIVE`) VALUES ('2fd7b16e-f37f-4f14-b6e5-fc257d9777e7', 'Enzymatic Debridement Application', '1');
INSERT INTO `wc_procedure_tb` (`WC_PROC_CODE`, `WC_PROC_NAME`, `WC_PROC_IS_ACTIVE`) VALUES ('33e38a83-b219-475c-ba5b-3a31fe1ffbe4', 'Sharp Surgical Debridement', '1');
INSERT INTO `wc_procedure_tb` (`WC_PROC_CODE`, `WC_PROC_NAME`, `WC_PROC_IS_ACTIVE`) VALUES ('afee3f1a-4c11-48d1-adfe-82639c5d7c41', 'Mechanical Cleansing & Irrigation', '1');
INSERT INTO `wc_procedure_tb` (`WC_PROC_CODE`, `WC_PROC_NAME`, `WC_PROC_IS_ACTIVE`) VALUES ('ffe19d98-d37b-4cd1-a9af-260d6c723b47', 'Standard Wound Dressing Change', '1');

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_request_reg_tb`
-- الحالة: [جدول النظام]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_request_reg_tb`;
CREATE TABLE `wc_request_reg_tb` (
  `WC_REQ_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد لطلب التسجيل (UUID) - المفتاح الأساسي',
  `WC_REQ_PATIENT_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'رمز المريض المرجعي (FK -> MR_PATIENT_TB)',
  `WC_REQ_PAT_NAME_SNAPSHOT` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'نسخة ثابتة من اسم المريض وقت تقديم الطلب',
  `WC_REQ_PAT_ID_SNAPSHOT` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'نسخة ثابتة من رقم هوية المريض وقت تقديم الطلب',
  `WC_REQ_PAT_PHONE_SNAPSHOT` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'نسخة ثابتة من هاتف المريض وقت تقديم الطلب',
  `WC_REQ_NURSE_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'الممرض/ة مقدّم الطلب (FK -> PER_EMPLOYEE_TB)',
  `WC_REQ_ENTRY_USER_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المستخدم مدخل الطلب في النظام (FK -> PER_USERS_TB)',
  `WC_REQ_REVIEWED_BY_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'الموظف مراجع الطلب (FK -> PER_EMPLOYEE_TB)',
  `WC_REQ_STATUS` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING' COMMENT 'حالة الطلب (PENDING/ACCEPTED/REJECTED/VISIT_IN_PROGRESS)',
  `WC_REQ_REJECTION_REASON` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'سبب رفض الطلب في حال الرفض',
  `WC_REQ_NOTES` text COLLATE utf8mb4_unicode_ci COMMENT 'ملاحظات إضافية مرفقة بطلب التحويل',
  `WC_REQ_CREATED_AT` datetime NOT NULL COMMENT 'تاريخ ووقت تقديم طلب التسجيل',
  `WC_REQ_REVIEWED_ON` datetime DEFAULT NULL COMMENT 'تاريخ ووقت مراجعة واتخاذ القرار بشأن الطلب',
  PRIMARY KEY (`WC_REQ_CODE`),
  KEY `wc_request_reg_tb_wc_req_patient_cd_index` (`WC_REQ_PATIENT_CD`),
  KEY `wc_request_reg_tb_wc_req_nurse_cd_index` (`WC_REQ_NURSE_CD`),
  KEY `wc_request_reg_tb_wc_req_entry_user_cd_index` (`WC_REQ_ENTRY_USER_CD`),
  KEY `wc_request_reg_tb_wc_req_reviewed_by_cd_index` (`WC_REQ_REVIEWED_BY_CD`),
  KEY `wc_request_reg_tb_wc_req_status_index` (`WC_REQ_STATUS`),
  CONSTRAINT `wc_request_reg_tb_wc_req_entry_user_cd_foreign` FOREIGN KEY (`WC_REQ_ENTRY_USER_CD`) REFERENCES `per_users_tb` (`PER_USER_CODE`),
  CONSTRAINT `wc_request_reg_tb_wc_req_nurse_cd_foreign` FOREIGN KEY (`WC_REQ_NURSE_CD`) REFERENCES `per_employee_tb` (`PER_EMP_CODE`),
  CONSTRAINT `wc_request_reg_tb_wc_req_patient_cd_foreign` FOREIGN KEY (`WC_REQ_PATIENT_CD`) REFERENCES `mr_patient_tb` (`MR_PAT_CODE`),
  CONSTRAINT `wc_request_reg_tb_wc_req_reviewed_by_cd_foreign` FOREIGN KEY (`WC_REQ_REVIEWED_BY_CD`) REFERENCES `per_employee_tb` (`PER_EMP_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='طلبات تسجيل المرضى في عيادة العناية بالجروح';

-- Data for `wc_request_reg_tb`
INSERT INTO `wc_request_reg_tb` (`WC_REQ_CODE`, `WC_REQ_PATIENT_CD`, `WC_REQ_PAT_NAME_SNAPSHOT`, `WC_REQ_PAT_ID_SNAPSHOT`, `WC_REQ_PAT_PHONE_SNAPSHOT`, `WC_REQ_NURSE_CD`, `WC_REQ_ENTRY_USER_CD`, `WC_REQ_REVIEWED_BY_CD`, `WC_REQ_STATUS`, `WC_REQ_REJECTION_REASON`, `WC_REQ_NOTES`, `WC_REQ_CREATED_AT`, `WC_REQ_REVIEWED_ON`) VALUES ('066546a7-0d40-4fc8-a0fe-539c3359744b', '5a019650-e8e4-42b2-af41-e5801070ec65', 'خالد عبد الله المنصور (تجربة توثيق الصور)', '409998877', NULL, 'a817e223-7e7c-461c-90db-93742a8fd86d', '2759d629-76e6-4c59-bfd2-3a4aeadc2c54', NULL, 'ACCEPTED', NULL, NULL, '2026-10-05 10:23:13', NULL);
INSERT INTO `wc_request_reg_tb` (`WC_REQ_CODE`, `WC_REQ_PATIENT_CD`, `WC_REQ_PAT_NAME_SNAPSHOT`, `WC_REQ_PAT_ID_SNAPSHOT`, `WC_REQ_PAT_PHONE_SNAPSHOT`, `WC_REQ_NURSE_CD`, `WC_REQ_ENTRY_USER_CD`, `WC_REQ_REVIEWED_BY_CD`, `WC_REQ_STATUS`, `WC_REQ_REJECTION_REASON`, `WC_REQ_NOTES`, `WC_REQ_CREATED_AT`, `WC_REQ_REVIEWED_ON`) VALUES ('14b5d853-ee43-41e7-a846-f3c9c2b20401', '6ad0543e-05b5-4355-9197-ae19ea463db2', 'فاطمة النجار', 'PAT-1005', NULL, '15bd4d3b-25dc-4e12-a5cf-3df7dc6b78b9', 'fc73994f-6aff-4020-a32c-0c00fd2ea484', 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'VISIT_IN_PROGRESS', NULL, 'زيارة التقييم جارية حالياً بالقسم الداخلي', '2026-10-05 08:23:00', NULL);
INSERT INTO `wc_request_reg_tb` (`WC_REQ_CODE`, `WC_REQ_PATIENT_CD`, `WC_REQ_PAT_NAME_SNAPSHOT`, `WC_REQ_PAT_ID_SNAPSHOT`, `WC_REQ_PAT_PHONE_SNAPSHOT`, `WC_REQ_NURSE_CD`, `WC_REQ_ENTRY_USER_CD`, `WC_REQ_REVIEWED_BY_CD`, `WC_REQ_STATUS`, `WC_REQ_REJECTION_REASON`, `WC_REQ_NOTES`, `WC_REQ_CREATED_AT`, `WC_REQ_REVIEWED_ON`) VALUES ('503852bc-ecd4-43c3-8f7f-22d1746ebd8e', '6ab65c84-ac86-4fee-addc-e5b57fbe1ca4', 'عبدالله المحمد', 'PAT-1001', NULL, '15bd4d3b-25dc-4e12-a5cf-3df7dc6b78b9', 'fc73994f-6aff-4020-a32c-0c00fd2ea484', 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'APPROVED', NULL, 'تحويل حالة قدم سكرية بحاجة لغيار دوري', '2026-09-23 10:23:00', NULL);
INSERT INTO `wc_request_reg_tb` (`WC_REQ_CODE`, `WC_REQ_PATIENT_CD`, `WC_REQ_PAT_NAME_SNAPSHOT`, `WC_REQ_PAT_ID_SNAPSHOT`, `WC_REQ_PAT_PHONE_SNAPSHOT`, `WC_REQ_NURSE_CD`, `WC_REQ_ENTRY_USER_CD`, `WC_REQ_REVIEWED_BY_CD`, `WC_REQ_STATUS`, `WC_REQ_REJECTION_REASON`, `WC_REQ_NOTES`, `WC_REQ_CREATED_AT`, `WC_REQ_REVIEWED_ON`) VALUES ('62556575-794f-48a3-9bef-026a32fe98a0', '13cfac53-76f5-4f2b-b73e-a91cd7a02e3d', 'خالد الزهراني', 'PAT-1004', NULL, '15bd4d3b-25dc-4e12-a5cf-3df7dc6b78b9', 'fc73994f-6aff-4020-a32c-0c00fd2ea484', NULL, 'PENDING', NULL, 'طلب تحويل حديث لقرحة في القدم اليسرى', '2026-10-05 06:23:00', NULL);
INSERT INTO `wc_request_reg_tb` (`WC_REQ_CODE`, `WC_REQ_PATIENT_CD`, `WC_REQ_PAT_NAME_SNAPSHOT`, `WC_REQ_PAT_ID_SNAPSHOT`, `WC_REQ_PAT_PHONE_SNAPSHOT`, `WC_REQ_NURSE_CD`, `WC_REQ_ENTRY_USER_CD`, `WC_REQ_REVIEWED_BY_CD`, `WC_REQ_STATUS`, `WC_REQ_REJECTION_REASON`, `WC_REQ_NOTES`, `WC_REQ_CREATED_AT`, `WC_REQ_REVIEWED_ON`) VALUES ('68734ec8-fb88-445b-b0a5-7590a9513d5f', 'e2215585-de10-4f9a-8caa-5f2d606f0466', 'مريم العباسي', 'PAT-1002', NULL, '15bd4d3b-25dc-4e12-a5cf-3df7dc6b78b9', 'fc73994f-6aff-4020-a32c-0c00fd2ea484', 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'APPROVED', NULL, 'حروق من الدرجة الثانية في الطرف العلوي الأيمن', '2026-09-29 10:23:00', NULL);
INSERT INTO `wc_request_reg_tb` (`WC_REQ_CODE`, `WC_REQ_PATIENT_CD`, `WC_REQ_PAT_NAME_SNAPSHOT`, `WC_REQ_PAT_ID_SNAPSHOT`, `WC_REQ_PAT_PHONE_SNAPSHOT`, `WC_REQ_NURSE_CD`, `WC_REQ_ENTRY_USER_CD`, `WC_REQ_REVIEWED_BY_CD`, `WC_REQ_STATUS`, `WC_REQ_REJECTION_REASON`, `WC_REQ_NOTES`, `WC_REQ_CREATED_AT`, `WC_REQ_REVIEWED_ON`) VALUES ('b97c9b9f-b6df-4014-aae9-3fea3dacb731', '3dd77f14-fb20-4a8c-a789-a05bb0fdd88b', 'يوسف التميمي', 'PAT-1003', NULL, '15bd4d3b-25dc-4e12-a5cf-3df7dc6b78b9', 'fc73994f-6aff-4020-a32c-0c00fd2ea484', 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'APPROVED', NULL, 'جرح جراحي بعد عملية استئصال', '2026-09-10 10:23:00', NULL);

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_registration_tb`
-- الحالة: [جدول النظام]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_registration_tb`;
CREATE TABLE `wc_registration_tb` (
  `WC_REG_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد للتسجيل السريري (UUID) - المفتاح الأساسي',
  `WC_REG_REQUEST_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'رمز طلب التسجيل المرتبط (FK -> WC_REQUEST_REG_TB)',
  `WC_REG_PATIENT_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'رمز المريض (FK -> MR_PATIENT_TB)',
  `WC_REG_DEPARTMENT_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'رمز القسم المحوِّل (FK -> WC_DEPARTMENT_TB)',
  `WC_REG_PROCEDURE_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'رمز الإجراء الطبي عند التسجيل (FK -> WC_PROCEDURE_TB)',
  `WC_REG_PROCEDURE_PERFORMED_BY_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'الموظف المنفذ للإجراء الأول (FK -> PER_EMPLOYEE_TB)',
  `WC_REG_PROCEDURE_PERFORMED_AT` datetime DEFAULT NULL COMMENT 'تاريخ ووقت تنفيذ الإجراء الأول',
  `WC_REG_PMH_NOTES` text COLLATE utf8mb4_unicode_ci COMMENT 'ملاحظات التاريخ المرضي السريرية المخصصة',
  `WC_REG_DATE` datetime NOT NULL COMMENT 'تاريخ ووقت تسجيل فتح ملف العناية بالعيادة',
  `WC_REG_NURSE_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'الممرض/ة المسؤول عن ملف التسجيل (FK -> PER_EMPLOYEE_TB)',
  `WC_REG_ENTRY_USER_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المستخدم الذي أدخل التسجيل (FK -> PER_USERS_TB)',
  `WC_REG_STATUS` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE' COMMENT 'حالة التسجيل السريري (ACTIVE / DISCHARGED)',
  `WC_REG_DISCHARGE_DATE` date DEFAULT NULL COMMENT 'تاريخ خروج المريض المعتمد سريرياً',
  `WC_REG_DISCHARGE_TYPE` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'سبب ونوع الخروج (شفاء، تحويل، وفاة)',
  `WC_REG_DISCHARGED_AT` datetime DEFAULT NULL COMMENT 'تاريخ ووقت تنفيذ إغلاق الملف بالنظام',
  `WC_REG_DISCHARGED_BY_EMP_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'الموظف منفذ قرار التخريج (FK -> PER_EMPLOYEE_TB)',
  `WC_REG_HAS_ALLERGIES` tinyint(1) DEFAULT NULL COMMENT 'هل لدى المريض حساسية: 1 = نعم، 0 = لا، فارغ = سجل قديم قبل التقييم المنظم',
  `WC_REG_ALLERGIES_DESCRIPTION` text COLLATE utf8mb4_unicode_ci COMMENT 'وصف الحساسية (مطلوب عند وجود حساسية وفارغ عند عدمها)',
  `WC_REG_NO_COMORBIDITIES` tinyint(1) DEFAULT NULL COMMENT 'لا توجد أمراض مصاحبة: 1 = لا توجد (لا تُربط أمراض في WC_REGISTRATION_PMH_TB)',
  `WC_REG_NEXT_VISIT_DATE` datetime DEFAULT NULL COMMENT 'تاريخ الزيارة القادمة في خطة المتابعة',
  `WC_REG_FOLLOW_UP_FREQUENCY` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'تكرار المتابعة (مثال: كل أسبوع)',
  `WC_REG_FOLLOW_UP_OBJECTIVE` text COLLATE utf8mb4_unicode_ci COMMENT 'هدف خطة المتابعة',
  PRIMARY KEY (`WC_REG_CODE`),
  KEY `wc_registration_tb_wc_reg_request_cd_index` (`WC_REG_REQUEST_CD`),
  KEY `wc_registration_tb_wc_reg_patient_cd_index` (`WC_REG_PATIENT_CD`),
  KEY `wc_registration_tb_wc_reg_department_cd_index` (`WC_REG_DEPARTMENT_CD`),
  KEY `wc_registration_tb_wc_reg_procedure_cd_index` (`WC_REG_PROCEDURE_CD`),
  KEY `wc_registration_tb_wc_reg_procedure_performed_by_cd_index` (`WC_REG_PROCEDURE_PERFORMED_BY_CD`),
  KEY `wc_registration_tb_wc_reg_nurse_cd_index` (`WC_REG_NURSE_CD`),
  KEY `wc_registration_tb_wc_reg_entry_user_cd_index` (`WC_REG_ENTRY_USER_CD`),
  KEY `wc_registration_tb_wc_reg_status_index` (`WC_REG_STATUS`),
  KEY `wc_registration_tb_wc_reg_discharged_by_emp_cd_index` (`WC_REG_DISCHARGED_BY_EMP_CD`),
  CONSTRAINT `wc_registration_tb_wc_reg_department_cd_foreign` FOREIGN KEY (`WC_REG_DEPARTMENT_CD`) REFERENCES `wc_department_tb` (`WC_DEPT_CODE`),
  CONSTRAINT `wc_registration_tb_wc_reg_discharged_by_emp_cd_foreign` FOREIGN KEY (`WC_REG_DISCHARGED_BY_EMP_CD`) REFERENCES `per_employee_tb` (`PER_EMP_CODE`),
  CONSTRAINT `wc_registration_tb_wc_reg_entry_user_cd_foreign` FOREIGN KEY (`WC_REG_ENTRY_USER_CD`) REFERENCES `per_users_tb` (`PER_USER_CODE`),
  CONSTRAINT `wc_registration_tb_wc_reg_nurse_cd_foreign` FOREIGN KEY (`WC_REG_NURSE_CD`) REFERENCES `per_employee_tb` (`PER_EMP_CODE`),
  CONSTRAINT `wc_registration_tb_wc_reg_patient_cd_foreign` FOREIGN KEY (`WC_REG_PATIENT_CD`) REFERENCES `mr_patient_tb` (`MR_PAT_CODE`),
  CONSTRAINT `wc_registration_tb_wc_reg_procedure_cd_foreign` FOREIGN KEY (`WC_REG_PROCEDURE_CD`) REFERENCES `wc_procedure_tb` (`WC_PROC_CODE`),
  CONSTRAINT `wc_registration_tb_wc_reg_procedure_performed_by_cd_foreign` FOREIGN KEY (`WC_REG_PROCEDURE_PERFORMED_BY_CD`) REFERENCES `per_employee_tb` (`PER_EMP_CODE`),
  CONSTRAINT `wc_registration_tb_wc_reg_request_cd_foreign` FOREIGN KEY (`WC_REG_REQUEST_CD`) REFERENCES `wc_request_reg_tb` (`WC_REQ_CODE`),
  CONSTRAINT `wc_reg_allergy_context_check` CHECK (((`WC_REG_HAS_ALLERGIES` is null) or ((`WC_REG_HAS_ALLERGIES` = 1) and (nullif(trim(`WC_REG_ALLERGIES_DESCRIPTION`),_utf8mb4'') is not null)) or ((`WC_REG_HAS_ALLERGIES` = 0) and (`WC_REG_ALLERGIES_DESCRIPTION` is null))))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='التسجيل السريري للمريض في العيادة بعد قبول الطلب';

-- Data for `wc_registration_tb`
INSERT INTO `wc_registration_tb` (`WC_REG_CODE`, `WC_REG_REQUEST_CD`, `WC_REG_PATIENT_CD`, `WC_REG_DEPARTMENT_CD`, `WC_REG_PROCEDURE_CD`, `WC_REG_PROCEDURE_PERFORMED_BY_CD`, `WC_REG_PROCEDURE_PERFORMED_AT`, `WC_REG_PMH_NOTES`, `WC_REG_DATE`, `WC_REG_NURSE_CD`, `WC_REG_ENTRY_USER_CD`, `WC_REG_STATUS`, `WC_REG_DISCHARGE_DATE`, `WC_REG_DISCHARGE_TYPE`, `WC_REG_DISCHARGED_AT`, `WC_REG_DISCHARGED_BY_EMP_CD`) VALUES ('01b96648-79de-47fb-8392-2b79d4488f49', '066546a7-0d40-4fc8-a0fe-539c3359744b', '5a019650-e8e4-42b2-af41-e5801070ec65', '3ac8e8dc-99e1-4682-a15d-7f84f70fa255', NULL, NULL, NULL, NULL, '2026-10-05 10:23:13', 'a817e223-7e7c-461c-90db-93742a8fd86d', '2759d629-76e6-4c59-bfd2-3a4aeadc2c54', 'ACTIVE', NULL, NULL, NULL, NULL);
INSERT INTO `wc_registration_tb` (`WC_REG_CODE`, `WC_REG_REQUEST_CD`, `WC_REG_PATIENT_CD`, `WC_REG_DEPARTMENT_CD`, `WC_REG_PROCEDURE_CD`, `WC_REG_PROCEDURE_PERFORMED_BY_CD`, `WC_REG_PROCEDURE_PERFORMED_AT`, `WC_REG_PMH_NOTES`, `WC_REG_DATE`, `WC_REG_NURSE_CD`, `WC_REG_ENTRY_USER_CD`, `WC_REG_STATUS`, `WC_REG_DISCHARGE_DATE`, `WC_REG_DISCHARGE_TYPE`, `WC_REG_DISCHARGED_AT`, `WC_REG_DISCHARGED_BY_EMP_CD`) VALUES ('a9acc842-20bd-46df-b8dc-4fe965e9cda1', '503852bc-ecd4-43c3-8f7f-22d1746ebd8e', '6ab65c84-ac86-4fee-addc-e5b57fbe1ca4', '11d97034-fb23-4a17-aaaf-85cf2d5768ce', '1e38fe8e-3d4f-41a1-a823-650304a8fec1', NULL, NULL, 'مرض السكري من النوع الثاني + ارتفاع ضغط الدم', '2026-09-25 10:23:00', 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'da612d56-593a-4a66-a6ea-16ffafbf73bd', 'ACTIVE', NULL, NULL, NULL, NULL);
INSERT INTO `wc_registration_tb` (`WC_REG_CODE`, `WC_REG_REQUEST_CD`, `WC_REG_PATIENT_CD`, `WC_REG_DEPARTMENT_CD`, `WC_REG_PROCEDURE_CD`, `WC_REG_PROCEDURE_PERFORMED_BY_CD`, `WC_REG_PROCEDURE_PERFORMED_AT`, `WC_REG_PMH_NOTES`, `WC_REG_DATE`, `WC_REG_NURSE_CD`, `WC_REG_ENTRY_USER_CD`, `WC_REG_STATUS`, `WC_REG_DISCHARGE_DATE`, `WC_REG_DISCHARGE_TYPE`, `WC_REG_DISCHARGED_AT`, `WC_REG_DISCHARGED_BY_EMP_CD`) VALUES ('b858118c-f8b2-4951-a052-1a23e94f668b', 'b97c9b9f-b6df-4014-aae9-3fea3dacb731', '3dd77f14-fb20-4a8c-a789-a05bb0fdd88b', '11d97034-fb23-4a17-aaaf-85cf2d5768ce', '1e38fe8e-3d4f-41a1-a823-650304a8fec1', NULL, NULL, 'لا توجد أمراض مزمنة', '2026-09-15 10:23:00', 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'da612d56-593a-4a66-a6ea-16ffafbf73bd', 'DISCHARGED', NULL, NULL, '2026-10-03 10:23:00', 'bb948a14-477e-4d1b-84d3-6afec72e4ef3');
INSERT INTO `wc_registration_tb` (`WC_REG_CODE`, `WC_REG_REQUEST_CD`, `WC_REG_PATIENT_CD`, `WC_REG_DEPARTMENT_CD`, `WC_REG_PROCEDURE_CD`, `WC_REG_PROCEDURE_PERFORMED_BY_CD`, `WC_REG_PROCEDURE_PERFORMED_AT`, `WC_REG_PMH_NOTES`, `WC_REG_DATE`, `WC_REG_NURSE_CD`, `WC_REG_ENTRY_USER_CD`, `WC_REG_STATUS`, `WC_REG_DISCHARGE_DATE`, `WC_REG_DISCHARGE_TYPE`, `WC_REG_DISCHARGED_AT`, `WC_REG_DISCHARGED_BY_EMP_CD`) VALUES ('e93f27e2-01a7-4a6f-b7e7-6dda7f6ed3e2', '68734ec8-fb88-445b-b0a5-7590a9513d5f', 'e2215585-de10-4f9a-8caa-5f2d606f0466', '11d97034-fb23-4a17-aaaf-85cf2d5768ce', '2fd7b16e-f37f-4f14-b6e5-fc257d9777e7', NULL, NULL, 'حساسية من البنسلين', '2026-09-30 10:23:00', 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'da612d56-593a-4a66-a6ea-16ffafbf73bd', 'ACTIVE', NULL, NULL, NULL, NULL);

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_reg_diagnosis_tb`
-- الحالة: [جدول النظام]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_reg_diagnosis_tb`;
CREATE TABLE `wc_reg_diagnosis_tb` (
  `WC_RDIAG_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد لتشخيص الجرح (UUID) - المفتاح الأساسي',
  `WC_RDIAG_REGISTRATION_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'رمز التسجيل السريري التابع له (FK -> WC_REGISTRATION_TB)',
  `WC_RDIAG_DIAGNOSIS_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'رمز التشخيص المرجعي (FK -> WC_DIAGNOSIS_TB)',
  `WC_RDIAG_WOUND_TYPE_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'رمز نوع الجرح المرجعي (FK -> WC_WOUND_TYPE_TB)',
  `WC_RDIAG_DESCRIPTION` text COLLATE utf8mb4_unicode_ci COMMENT 'الوصف السريري المخصص للجرح وموقعه وتفاصيله',
  `WC_RDIAG_CLINICAL_EMP_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'الموظف السريري المشخص للجرح (FK -> PER_EMPLOYEE_TB)',
  `WC_RDIAG_ENTRY_USER_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'المستخدم مدخل التشخيص (FK -> PER_USERS_TB)',
  PRIMARY KEY (`WC_RDIAG_CODE`),
  KEY `wc_reg_diagnosis_tb_wc_rdiag_registration_cd_index` (`WC_RDIAG_REGISTRATION_CD`),
  KEY `wc_reg_diagnosis_tb_wc_rdiag_diagnosis_cd_index` (`WC_RDIAG_DIAGNOSIS_CD`),
  KEY `wc_reg_diagnosis_tb_wc_rdiag_wound_type_cd_index` (`WC_RDIAG_WOUND_TYPE_CD`),
  KEY `wc_reg_diagnosis_tb_wc_rdiag_clinical_emp_cd_index` (`WC_RDIAG_CLINICAL_EMP_CD`),
  KEY `wc_reg_diagnosis_tb_wc_rdiag_entry_user_cd_index` (`WC_RDIAG_ENTRY_USER_CD`),
  CONSTRAINT `wc_reg_diagnosis_tb_wc_rdiag_clinical_emp_cd_foreign` FOREIGN KEY (`WC_RDIAG_CLINICAL_EMP_CD`) REFERENCES `per_employee_tb` (`PER_EMP_CODE`),
  CONSTRAINT `wc_reg_diagnosis_tb_wc_rdiag_diagnosis_cd_foreign` FOREIGN KEY (`WC_RDIAG_DIAGNOSIS_CD`) REFERENCES `wc_diagnosis_tb` (`WC_DIAG_CODE`),
  CONSTRAINT `wc_reg_diagnosis_tb_wc_rdiag_entry_user_cd_foreign` FOREIGN KEY (`WC_RDIAG_ENTRY_USER_CD`) REFERENCES `per_users_tb` (`PER_USER_CODE`),
  CONSTRAINT `wc_reg_diagnosis_tb_wc_rdiag_registration_cd_foreign` FOREIGN KEY (`WC_RDIAG_REGISTRATION_CD`) REFERENCES `wc_registration_tb` (`WC_REG_CODE`),
  CONSTRAINT `wc_reg_diagnosis_tb_wc_rdiag_wound_type_cd_foreign` FOREIGN KEY (`WC_RDIAG_WOUND_TYPE_CD`) REFERENCES `wc_wound_type_tb` (`WC_WTYPE_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='التشخيصات والجروح المسجَّلة ضمن تسجيل مريض واحد';

-- Data for `wc_reg_diagnosis_tb`
INSERT INTO `wc_reg_diagnosis_tb` (`WC_RDIAG_CODE`, `WC_RDIAG_REGISTRATION_CD`, `WC_RDIAG_DIAGNOSIS_CD`, `WC_RDIAG_WOUND_TYPE_CD`, `WC_RDIAG_DESCRIPTION`, `WC_RDIAG_CLINICAL_EMP_CD`, `WC_RDIAG_ENTRY_USER_CD`) VALUES ('0255c023-ef4c-4db2-877f-210465502406', 'e93f27e2-01a7-4a6f-b7e7-6dda7f6ed3e2', '57c300ff-4a95-43ca-8760-21209636a419', '09b99646-9668-4786-a50b-1a0e64aa82dd', NULL, 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'da612d56-593a-4a66-a6ea-16ffafbf73bd');
INSERT INTO `wc_reg_diagnosis_tb` (`WC_RDIAG_CODE`, `WC_RDIAG_REGISTRATION_CD`, `WC_RDIAG_DIAGNOSIS_CD`, `WC_RDIAG_WOUND_TYPE_CD`, `WC_RDIAG_DESCRIPTION`, `WC_RDIAG_CLINICAL_EMP_CD`, `WC_RDIAG_ENTRY_USER_CD`) VALUES ('664a3367-c59e-4690-9956-f8bf50a0915a', 'a9acc842-20bd-46df-b8dc-4fe965e9cda1', '70aa21bd-9294-4f47-8a1a-94311675682d', '09b99646-9668-4786-a50b-1a0e64aa82dd', NULL, 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'da612d56-593a-4a66-a6ea-16ffafbf73bd');
INSERT INTO `wc_reg_diagnosis_tb` (`WC_RDIAG_CODE`, `WC_RDIAG_REGISTRATION_CD`, `WC_RDIAG_DIAGNOSIS_CD`, `WC_RDIAG_WOUND_TYPE_CD`, `WC_RDIAG_DESCRIPTION`, `WC_RDIAG_CLINICAL_EMP_CD`, `WC_RDIAG_ENTRY_USER_CD`) VALUES ('94a3a7fb-9cd9-41d7-9fd7-73f7700cbcb7', 'a9acc842-20bd-46df-b8dc-4fe965e9cda1', '57c300ff-4a95-43ca-8760-21209636a419', '09b99646-9668-4786-a50b-1a0e64aa82dd', NULL, 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'da612d56-593a-4a66-a6ea-16ffafbf73bd');
INSERT INTO `wc_reg_diagnosis_tb` (`WC_RDIAG_CODE`, `WC_RDIAG_REGISTRATION_CD`, `WC_RDIAG_DIAGNOSIS_CD`, `WC_RDIAG_WOUND_TYPE_CD`, `WC_RDIAG_DESCRIPTION`, `WC_RDIAG_CLINICAL_EMP_CD`, `WC_RDIAG_ENTRY_USER_CD`) VALUES ('9ae42471-4842-4101-8404-5ace607b00a6', '01b96648-79de-47fb-8392-2b79d4488f49', '869817cb-f14b-4592-be07-17d1199966f7', '21c9571b-b908-4898-87ed-437916f73b31', NULL, 'a817e223-7e7c-461c-90db-93742a8fd86d', '2759d629-76e6-4c59-bfd2-3a4aeadc2c54');
INSERT INTO `wc_reg_diagnosis_tb` (`WC_RDIAG_CODE`, `WC_RDIAG_REGISTRATION_CD`, `WC_RDIAG_DIAGNOSIS_CD`, `WC_RDIAG_WOUND_TYPE_CD`, `WC_RDIAG_DESCRIPTION`, `WC_RDIAG_CLINICAL_EMP_CD`, `WC_RDIAG_ENTRY_USER_CD`) VALUES ('f9d559c1-81ab-4991-a550-07322ed68148', 'b858118c-f8b2-4951-a052-1a23e94f668b', '57c300ff-4a95-43ca-8760-21209636a419', '09b99646-9668-4786-a50b-1a0e64aa82dd', NULL, 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'da612d56-593a-4a66-a6ea-16ffafbf73bd');

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_scheduled_review_tb`
-- الحالة: [جدول النظام]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_scheduled_review_tb`;
CREATE TABLE `wc_scheduled_review_tb` (
  `WC_SREV_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد للموعد المجدول (UUID) - المفتاح الأساسي',
  `WC_SREV_REGISTRATION_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'رمز التسجيل السريري المجدول له الموعد (FK -> WC_REGISTRATION_TB)',
  `WC_SREV_CREATED_BY_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'المستخدم منشئ الموعد (FK -> PER_USERS_TB)',
  `WC_SREV_SCHEDULED_DATE` datetime NOT NULL COMMENT 'تاريخ ووقت الموعد المستقبلي المجدول',
  `WC_SREV_VISIT_SERIAL` int NOT NULL DEFAULT '1' COMMENT 'الرقم التسلسلي للمراجعة ضمن خطة العلاج',
  `WC_SREV_STATUS` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING' COMMENT 'حالة الموعد (PENDING/COMPLETED/MISSED/CANCELLED)',
  `WC_SREV_NOTES` text COLLATE utf8mb4_unicode_ci COMMENT 'ملاحظات وإرشادات خاصة بالموعد',
  `WC_SREV_CREATED_ON` datetime DEFAULT NULL COMMENT 'تاريخ إنشاء وتسجيل الموعد',
  `WC_SREV_COMPLETED_ON` datetime DEFAULT NULL COMMENT 'تاريخ ووقت إتمام الزيارة فعلياً',
  PRIMARY KEY (`WC_SREV_CODE`),
  KEY `wc_scheduled_review_tb_wc_srev_registration_cd_index` (`WC_SREV_REGISTRATION_CD`),
  KEY `wc_scheduled_review_tb_wc_srev_created_by_cd_index` (`WC_SREV_CREATED_BY_CD`),
  KEY `wc_scheduled_review_tb_wc_srev_status_index` (`WC_SREV_STATUS`),
  CONSTRAINT `wc_scheduled_review_tb_wc_srev_created_by_cd_foreign` FOREIGN KEY (`WC_SREV_CREATED_BY_CD`) REFERENCES `per_users_tb` (`PER_USER_CODE`),
  CONSTRAINT `wc_scheduled_review_tb_wc_srev_registration_cd_foreign` FOREIGN KEY (`WC_SREV_REGISTRATION_CD`) REFERENCES `wc_registration_tb` (`WC_REG_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='مواعيد المراجعات المجدولة مستقبلاً للمريض المسجَّل';

-- Data for `wc_scheduled_review_tb`
INSERT INTO `wc_scheduled_review_tb` (`WC_SREV_CODE`, `WC_SREV_REGISTRATION_CD`, `WC_SREV_CREATED_BY_CD`, `WC_SREV_SCHEDULED_DATE`, `WC_SREV_VISIT_SERIAL`, `WC_SREV_STATUS`, `WC_SREV_NOTES`, `WC_SREV_CREATED_ON`, `WC_SREV_COMPLETED_ON`) VALUES ('1bd65c3d-de9e-4056-9784-33255d40b52c', 'a9acc842-20bd-46df-b8dc-4fe965e9cda1', 'da612d56-593a-4a66-a6ea-16ffafbf73bd', '2026-10-06 00:00:00', '1', 'PENDING', 'متابعة تقييم الجرح والغيار التالي', '2026-10-04 10:23:00', NULL);
INSERT INTO `wc_scheduled_review_tb` (`WC_SREV_CODE`, `WC_SREV_REGISTRATION_CD`, `WC_SREV_CREATED_BY_CD`, `WC_SREV_SCHEDULED_DATE`, `WC_SREV_VISIT_SERIAL`, `WC_SREV_STATUS`, `WC_SREV_NOTES`, `WC_SREV_CREATED_ON`, `WC_SREV_COMPLETED_ON`) VALUES ('4a3e91e1-cba5-432e-af7e-d92abe6dd4b9', 'e93f27e2-01a7-4a6f-b7e7-6dda7f6ed3e2', 'da612d56-593a-4a66-a6ea-16ffafbf73bd', '2026-10-05 00:00:00', '1', 'PENDING', 'متابعة التئام حروق الطرف العلوي اليوم', '2026-10-03 10:23:00', NULL);
INSERT INTO `wc_scheduled_review_tb` (`WC_SREV_CODE`, `WC_SREV_REGISTRATION_CD`, `WC_SREV_CREATED_BY_CD`, `WC_SREV_SCHEDULED_DATE`, `WC_SREV_VISIT_SERIAL`, `WC_SREV_STATUS`, `WC_SREV_NOTES`, `WC_SREV_CREATED_ON`, `WC_SREV_COMPLETED_ON`) VALUES ('dc192029-4624-4df2-b65d-070cf1a1db5c', 'b858118c-f8b2-4951-a052-1a23e94f668b', 'da612d56-593a-4a66-a6ea-16ffafbf73bd', '2026-10-03 00:00:00', '1', 'COMPLETED', 'موعد المتابعة الأخير والتخريج', '2026-09-30 10:23:00', '2026-10-03 10:23:00');
INSERT INTO `wc_scheduled_review_tb` (`WC_SREV_CODE`, `WC_SREV_REGISTRATION_CD`, `WC_SREV_CREATED_BY_CD`, `WC_SREV_SCHEDULED_DATE`, `WC_SREV_VISIT_SERIAL`, `WC_SREV_STATUS`, `WC_SREV_NOTES`, `WC_SREV_CREATED_ON`, `WC_SREV_COMPLETED_ON`) VALUES ('ed96c4b1-8261-4da9-a5a1-4d0f7f472e69', 'a9acc842-20bd-46df-b8dc-4fe965e9cda1', 'da612d56-593a-4a66-a6ea-16ffafbf73bd', '2026-10-01 00:00:00', '1', 'MISSED', 'لم يحضر المريض للموعد المجدول', '2026-09-29 10:23:00', NULL);

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_daily_review_tb`
-- الحالة: [جدول النظام]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_daily_review_tb`;
CREATE TABLE `wc_daily_review_tb` (
  `WC_DREV_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد للزيارة السريرية (UUID) - المفتاح الأساسي',
  `WC_DREV_REGISTRATION_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'رمز التسجيل السريري التابع له الزيارة (FK -> WC_REGISTRATION_TB)',
  `WC_DREV_SCHEDULED_REVIEW_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'رمز الموعد المجدول المرتبط إن وجد (FK -> WC_SCHEDULED_REVIEW_TB)',
  `WC_DREV_VISIT_DATE` datetime NOT NULL COMMENT 'تاريخ ووقت الزيارة والمراجعة الفعلية',
  `WC_DREV_NURSE_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'الممرض/ة منفذ الزيارة والتقييم (FK -> PER_EMPLOYEE_TB)',
  `WC_DREV_ENTRY_USER_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المستخدم مدخل التقييم اليومي (FK -> PER_USERS_TB)',
  `WC_DREV_OVERALL_STATUS` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'التقييم العام الشامل لحالة المريض بالزيارة',
  `WC_DREV_GENERAL_NOTES` text COLLATE utf8mb4_unicode_ci COMMENT 'ملاحظات الممرض العامة أثناء الزيارة',
  `WC_DREV_SUBMISSION_KEY` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'مفتاح الإرسال الفريد لمنع تكرار حفظ الزيارة نفسها (فارغ للسجلات القديمة)',
  PRIMARY KEY (`WC_DREV_CODE`),
  UNIQUE KEY `wc_drev_scheduled_review_unique` (`WC_DREV_SCHEDULED_REVIEW_CD`),
  UNIQUE KEY `wc_drev_submission_key_unique` (`WC_DREV_SUBMISSION_KEY`),
  KEY `wc_daily_review_tb_wc_drev_registration_cd_index` (`WC_DREV_REGISTRATION_CD`),
  KEY `wc_daily_review_tb_wc_drev_scheduled_review_cd_index` (`WC_DREV_SCHEDULED_REVIEW_CD`),
  KEY `wc_daily_review_tb_wc_drev_nurse_cd_index` (`WC_DREV_NURSE_CD`),
  KEY `wc_daily_review_tb_wc_drev_entry_user_cd_index` (`WC_DREV_ENTRY_USER_CD`),
  KEY `wc_daily_review_tb_wc_drev_overall_status_index` (`WC_DREV_OVERALL_STATUS`),
  CONSTRAINT `wc_daily_review_tb_wc_drev_entry_user_cd_foreign` FOREIGN KEY (`WC_DREV_ENTRY_USER_CD`) REFERENCES `per_users_tb` (`PER_USER_CODE`),
  CONSTRAINT `wc_daily_review_tb_wc_drev_nurse_cd_foreign` FOREIGN KEY (`WC_DREV_NURSE_CD`) REFERENCES `per_employee_tb` (`PER_EMP_CODE`),
  CONSTRAINT `wc_daily_review_tb_wc_drev_registration_cd_foreign` FOREIGN KEY (`WC_DREV_REGISTRATION_CD`) REFERENCES `wc_registration_tb` (`WC_REG_CODE`),
  CONSTRAINT `wc_daily_review_tb_wc_drev_scheduled_review_cd_foreign` FOREIGN KEY (`WC_DREV_SCHEDULED_REVIEW_CD`) REFERENCES `wc_scheduled_review_tb` (`WC_SREV_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='رأس الزيارة/المراجعة الفعلية للمريض';

-- Data for `wc_daily_review_tb`
INSERT INTO `wc_daily_review_tb` (`WC_DREV_CODE`, `WC_DREV_REGISTRATION_CD`, `WC_DREV_SCHEDULED_REVIEW_CD`, `WC_DREV_VISIT_DATE`, `WC_DREV_NURSE_CD`, `WC_DREV_ENTRY_USER_CD`, `WC_DREV_OVERALL_STATUS`, `WC_DREV_GENERAL_NOTES`) VALUES ('11d8b7f2-db49-49c5-a82e-92b4852a2f81', 'b858118c-f8b2-4951-a052-1a23e94f668b', 'dc192029-4624-4df2-b65d-070cf1a1db5c', '2026-10-03 10:23:00', 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'da612d56-593a-4a66-a6ea-16ffafbf73bd', 'IMPROVED_DISCHARGE', 'التئام كامل للجرح وتخريج المريض بنجاح من العيادة');
INSERT INTO `wc_daily_review_tb` (`WC_DREV_CODE`, `WC_DREV_REGISTRATION_CD`, `WC_DREV_SCHEDULED_REVIEW_CD`, `WC_DREV_VISIT_DATE`, `WC_DREV_NURSE_CD`, `WC_DREV_ENTRY_USER_CD`, `WC_DREV_OVERALL_STATUS`, `WC_DREV_GENERAL_NOTES`) VALUES ('13e876f5-b9f5-4c9c-af28-e9647c496c4f', 'e93f27e2-01a7-4a6f-b7e7-6dda7f6ed3e2', NULL, '2026-10-03 10:23:00', 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'da612d56-593a-4a66-a6ea-16ffafbf73bd', 'IMPROVED_FOLLOWUP', 'استجابة جيدة لضماد الحروق');
INSERT INTO `wc_daily_review_tb` (`WC_DREV_CODE`, `WC_DREV_REGISTRATION_CD`, `WC_DREV_SCHEDULED_REVIEW_CD`, `WC_DREV_VISIT_DATE`, `WC_DREV_NURSE_CD`, `WC_DREV_ENTRY_USER_CD`, `WC_DREV_OVERALL_STATUS`, `WC_DREV_GENERAL_NOTES`) VALUES ('14728f18-e8ec-40ff-bf30-7111d6f12086', 'a9acc842-20bd-46df-b8dc-4fe965e9cda1', NULL, '2026-10-04 10:23:00', 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'da612d56-593a-4a66-a6ea-16ffafbf73bd', 'IMPROVED_SCHEDULED', 'تحسن ملحوظ في حواف الجرح وتراجع الإفرازات');
INSERT INTO `wc_daily_review_tb` (`WC_DREV_CODE`, `WC_DREV_REGISTRATION_CD`, `WC_DREV_SCHEDULED_REVIEW_CD`, `WC_DREV_VISIT_DATE`, `WC_DREV_NURSE_CD`, `WC_DREV_ENTRY_USER_CD`, `WC_DREV_OVERALL_STATUS`, `WC_DREV_GENERAL_NOTES`) VALUES ('61aa4ae8-cf6b-41e4-8e80-7c980504af06', '01b96648-79de-47fb-8392-2b79d4488f49', NULL, '2026-10-05 10:23:13', 'a817e223-7e7c-461c-90db-93742a8fd86d', '2759d629-76e6-4c59-bfd2-3a4aeadc2c54', 'WORSENED_STAY', 'زيارة متابعة دورية لتوثيق التحسن البصري للجرح');
INSERT INTO `wc_daily_review_tb` (`WC_DREV_CODE`, `WC_DREV_REGISTRATION_CD`, `WC_DREV_SCHEDULED_REVIEW_CD`, `WC_DREV_VISIT_DATE`, `WC_DREV_NURSE_CD`, `WC_DREV_ENTRY_USER_CD`, `WC_DREV_OVERALL_STATUS`, `WC_DREV_GENERAL_NOTES`) VALUES ('9a7d72bc-8721-4479-acd7-e4f87a680e66', 'a9acc842-20bd-46df-b8dc-4fe965e9cda1', NULL, '2026-09-28 10:23:00', 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'da612d56-593a-4a66-a6ea-16ffafbf73bd', 'WORSENED_STAY', 'جرح القدم السكرية يتطلب تنظيفاً مكثفاً');

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_review_detail_tb`
-- الحالة: [جدول النظام]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_review_detail_tb`;
CREATE TABLE `wc_review_detail_tb` (
  `WC_REVD_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد لسطر تقييم الجرح (UUID) - المفتاح الأساسي',
  `WC_REVD_REVIEW_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'رمز الزيارة المرجعية الأم (FK -> WC_DAILY_REVIEW_TB)',
  `WC_REVD_REVIEW_TYPE` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DAILY' COMMENT 'نوع المراجعة السريرية (DAILY)',
  `WC_DREVD_REG_DIAGNOSIS_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'رمز الجرح المشخص الجاري تقييمه (FK -> WC_REG_DIAGNOSIS_TB)',
  `WC_DREVD_WOUND_DESCRIPTION` text COLLATE utf8mb4_unicode_ci COMMENT 'وصف حالة الجرح والحجم والإفرازات والأنسجة',
  `WC_DREVD_WOUND_CHANGES` text COLLATE utf8mb4_unicode_ci COMMENT 'ملاحظات التغير في حالة الجرح مقارنة بالزيارة السابقة',
  `WC_DREVD_DRESSING_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'رمز الضماد المستعمل في المراجعة (FK -> WC_DRESSING_TB)',
  `WC_DREVD_MEDICATION_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'رمز العلاج/المرهم المستعمل بالمراجعة (FK -> WC_MEDICATION_TB)',
  `WC_DREVD_STATUS` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'حالة الجرح الخاصة بهذه الزيارة (تحسن/ثبات/تدهور/شفاء)',
  `WC_DREVD_FOLLOW_UP_REQUIRED` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'هل يتطلب الجرح متابعة خاصة إضافية (1=نعم, 0=لا)',
  PRIMARY KEY (`WC_REVD_CODE`),
  KEY `wc_review_detail_tb_wc_revd_review_cd_index` (`WC_REVD_REVIEW_CD`),
  KEY `wc_review_detail_tb_wc_revd_review_type_index` (`WC_REVD_REVIEW_TYPE`),
  KEY `wc_review_detail_tb_wc_drevd_reg_diagnosis_cd_index` (`WC_DREVD_REG_DIAGNOSIS_CD`),
  KEY `wc_review_detail_tb_wc_drevd_dressing_cd_index` (`WC_DREVD_DRESSING_CD`),
  KEY `wc_review_detail_tb_wc_drevd_medication_cd_index` (`WC_DREVD_MEDICATION_CD`),
  KEY `wc_review_detail_tb_wc_drevd_status_index` (`WC_DREVD_STATUS`),
  CONSTRAINT `wc_review_detail_tb_wc_drevd_dressing_cd_foreign` FOREIGN KEY (`WC_DREVD_DRESSING_CD`) REFERENCES `wc_dressing_tb` (`WC_DRESS_CODE`),
  CONSTRAINT `wc_review_detail_tb_wc_drevd_medication_cd_foreign` FOREIGN KEY (`WC_DREVD_MEDICATION_CD`) REFERENCES `wc_medication_tb` (`WC_MED_CODE`),
  CONSTRAINT `wc_review_detail_tb_wc_drevd_reg_diagnosis_cd_foreign` FOREIGN KEY (`WC_DREVD_REG_DIAGNOSIS_CD`) REFERENCES `wc_reg_diagnosis_tb` (`WC_RDIAG_CODE`),
  CONSTRAINT `wc_review_detail_tb_wc_revd_review_cd_foreign` FOREIGN KEY (`WC_REVD_REVIEW_CD`) REFERENCES `wc_daily_review_tb` (`WC_DREV_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='تفاصيل تقييم كل جرح داخل الزيارة (جدول موحَّد)';

-- Data for `wc_review_detail_tb`
INSERT INTO `wc_review_detail_tb` (`WC_REVD_CODE`, `WC_REVD_REVIEW_CD`, `WC_REVD_REVIEW_TYPE`, `WC_DREVD_REG_DIAGNOSIS_CD`, `WC_DREVD_WOUND_DESCRIPTION`, `WC_DREVD_WOUND_CHANGES`, `WC_DREVD_DRESSING_CD`, `WC_DREVD_MEDICATION_CD`, `WC_DREVD_STATUS`, `WC_DREVD_FOLLOW_UP_REQUIRED`) VALUES ('946212e8-0efe-45b5-9dae-9bcee87dce59', '14728f18-e8ec-40ff-bf30-7111d6f12086', 'DAILY', '94a3a7fb-9cd9-41d7-9fd7-73f7700cbcb7', 'نظافة حواف الجرح وتكون أنسجة حمراء جديدة', 'تحسن في نمو الأنسجة', 'a28bb7c7-c1b2-4aeb-ac7c-258f2eec9bbc', '4a5cccca-82c3-4a62-ada4-035d9cce61d7', 'IMPROVED_SCHEDULED', '1');
INSERT INTO `wc_review_detail_tb` (`WC_REVD_CODE`, `WC_REVD_REVIEW_CD`, `WC_REVD_REVIEW_TYPE`, `WC_DREVD_REG_DIAGNOSIS_CD`, `WC_DREVD_WOUND_DESCRIPTION`, `WC_DREVD_WOUND_CHANGES`, `WC_DREVD_DRESSING_CD`, `WC_DREVD_MEDICATION_CD`, `WC_DREVD_STATUS`, `WC_DREVD_FOLLOW_UP_REQUIRED`) VALUES ('c2d18a5a-e90f-4e50-96a0-dde296e153a4', '61aa4ae8-cf6b-41e4-8e80-7c980504af06', 'DAILY', '9ae42471-4842-4101-8404-5ace607b00a6', 'تحسن ملحوظ في حواف الجرح ونظافة الأنسجة', NULL, NULL, NULL, 'IMPROVED_FOLLOWUP', '0');
INSERT INTO `wc_review_detail_tb` (`WC_REVD_CODE`, `WC_REVD_REVIEW_CD`, `WC_REVD_REVIEW_TYPE`, `WC_DREVD_REG_DIAGNOSIS_CD`, `WC_DREVD_WOUND_DESCRIPTION`, `WC_DREVD_WOUND_CHANGES`, `WC_DREVD_DRESSING_CD`, `WC_DREVD_MEDICATION_CD`, `WC_DREVD_STATUS`, `WC_DREVD_FOLLOW_UP_REQUIRED`) VALUES ('d22e03be-0a32-40ec-bc53-bc4fe0919a14', '9a7d72bc-8721-4479-acd7-e4f87a680e66', 'DAILY', '94a3a7fb-9cd9-41d7-9fd7-73f7700cbcb7', 'جرح بعمق 0.5 سم مع وجود إفرازات قليل', 'تغير في لون حواف الجرح', '930640c3-d86c-4910-9afe-a5e0092daa38', '4a5cccca-82c3-4a62-ada4-035d9cce61d7', 'WORSENED_STAY', '1');
INSERT INTO `wc_review_detail_tb` (`WC_REVD_CODE`, `WC_REVD_REVIEW_CD`, `WC_REVD_REVIEW_TYPE`, `WC_DREVD_REG_DIAGNOSIS_CD`, `WC_DREVD_WOUND_DESCRIPTION`, `WC_DREVD_WOUND_CHANGES`, `WC_DREVD_DRESSING_CD`, `WC_DREVD_MEDICATION_CD`, `WC_DREVD_STATUS`, `WC_DREVD_FOLLOW_UP_REQUIRED`) VALUES ('f6b93bc5-91e7-4449-bbfb-57c233fc551b', '11d8b7f2-db49-49c5-a82e-92b4852a2f81', 'DAILY', 'f9d559c1-81ab-4991-a550-07322ed68148', 'التئام كامل لخط الجراحة وإزالة الغرز', 'ندبة نظيفة بدون أي علامات التهاب', '930640c3-d86c-4910-9afe-a5e0092daa38', NULL, 'IMPROVED_DISCHARGE', '0');
INSERT INTO `wc_review_detail_tb` (`WC_REVD_CODE`, `WC_REVD_REVIEW_CD`, `WC_REVD_REVIEW_TYPE`, `WC_DREVD_REG_DIAGNOSIS_CD`, `WC_DREVD_WOUND_DESCRIPTION`, `WC_DREVD_WOUND_CHANGES`, `WC_DREVD_DRESSING_CD`, `WC_DREVD_MEDICATION_CD`, `WC_DREVD_STATUS`, `WC_DREVD_FOLLOW_UP_REQUIRED`) VALUES ('f7655ea1-fd04-41bb-a3b9-68158028b0d5', '13e876f5-b9f5-4c9c-af28-e9647c496c4f', 'DAILY', '0255c023-ef4c-4db2-877f-210465502406', 'سطح الحرق يبدي التئاماً جزيئاً', 'تراجع الاحمرار والانتفاخ', 'a28bb7c7-c1b2-4aeb-ac7c-258f2eec9bbc', '6f4e5a65-4e90-417f-9485-ef10ca0f2cac', 'IMPROVED_FOLLOWUP', '1');

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_wound_photo_tb`
-- الحالة: [جدول النظام]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_wound_photo_tb`;
CREATE TABLE `wc_wound_photo_tb` (
  `WC_PHOTO_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد للصورة السريرية (UUID) - المفتاح الأساسي',
  `WC_PHOTO_REG_DIAGNOSIS_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'الجرح/التشخيص الذي تتبع له الصورة (FK -> WC_REG_DIAGNOSIS_TB)',
  `WC_PHOTO_DAILY_REVIEW_DETAIL_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'سطر تفاصيل المراجعة اليومية إذا كانت الصورة مأخوذة في مراجعة (FK -> WC_REVIEW_DETAIL_TB)',
  `WC_PHOTO_STAGE` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'مرحلة الصورة السريرية (INITIAL = خط الأساس الأولي / REVIEW = أثناء المراجعات)',
  `WC_PHOTO_PATH` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'مسار التخزين الفرعي للصورة في القرص السريري',
  `WC_PHOTO_CAPTION` text COLLATE utf8mb4_unicode_ci COMMENT 'الوصف/التعليق السريري على الصورة',
  `WC_PHOTO_TAKEN_AT` datetime NOT NULL COMMENT 'تاريخ ووقت التقاط الصورة السريرية',
  `WC_PHOTO_NURSE_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'الممرض/ة المسؤول/ة عن التقاط الصورة (FK -> PER_EMPLOYEE_TB)',
  `WC_PHOTO_ENTRY_USER_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المستخدم الذي أدخل سجل الصورة في النظام (FK -> PER_USERS_TB)',
  `WC_PHOTO_ASSESSMENT_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'التقييم المنظم الذي التُقطت فيه الصورة (FK -> WC_WOUND_ASSESSMENT_TB، فارغ للسجلات القديمة)',
  PRIMARY KEY (`WC_PHOTO_CODE`),
  KEY `wc_wound_photo_tb_wc_photo_reg_diagnosis_cd_index` (`WC_PHOTO_REG_DIAGNOSIS_CD`),
  KEY `wc_wound_photo_tb_wc_photo_daily_review_detail_cd_index` (`WC_PHOTO_DAILY_REVIEW_DETAIL_CD`),
  KEY `wc_wound_photo_tb_wc_photo_stage_index` (`WC_PHOTO_STAGE`),
  KEY `wc_wound_photo_tb_wc_photo_nurse_cd_index` (`WC_PHOTO_NURSE_CD`),
  KEY `wc_wound_photo_tb_wc_photo_entry_user_cd_index` (`WC_PHOTO_ENTRY_USER_CD`),
  KEY `wc_wound_photo_tb_wc_photo_assessment_cd_index` (`WC_PHOTO_ASSESSMENT_CD`),
  CONSTRAINT `wc_wound_photo_tb_wc_photo_daily_review_detail_cd_foreign` FOREIGN KEY (`WC_PHOTO_DAILY_REVIEW_DETAIL_CD`) REFERENCES `wc_review_detail_tb` (`WC_REVD_CODE`) ON DELETE SET NULL,
  CONSTRAINT `wc_wound_photo_tb_wc_photo_entry_user_cd_foreign` FOREIGN KEY (`WC_PHOTO_ENTRY_USER_CD`) REFERENCES `per_users_tb` (`PER_USER_CODE`),
  CONSTRAINT `wc_wound_photo_tb_wc_photo_nurse_cd_foreign` FOREIGN KEY (`WC_PHOTO_NURSE_CD`) REFERENCES `per_employee_tb` (`PER_EMP_CODE`),
  CONSTRAINT `wc_wound_photo_tb_wc_photo_assessment_cd_foreign` FOREIGN KEY (`WC_PHOTO_ASSESSMENT_CD`) REFERENCES `wc_wound_assessment_tb` (`WC_WA_CODE`) ON DELETE SET NULL,
  CONSTRAINT `wc_wound_photo_tb_wc_photo_reg_diagnosis_cd_foreign` FOREIGN KEY (`WC_PHOTO_REG_DIAGNOSIS_CD`) REFERENCES `wc_reg_diagnosis_tb` (`WC_RDIAG_CODE`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول التوثيق البصري وصور الجروح السريرية عند القبول وأثناء المراجعات الدوريّة';

-- Data for `wc_wound_photo_tb`
INSERT INTO `wc_wound_photo_tb` (`WC_PHOTO_CODE`, `WC_PHOTO_REG_DIAGNOSIS_CD`, `WC_PHOTO_DAILY_REVIEW_DETAIL_CD`, `WC_PHOTO_STAGE`, `WC_PHOTO_PATH`, `WC_PHOTO_CAPTION`, `WC_PHOTO_TAKEN_AT`, `WC_PHOTO_NURSE_CD`, `WC_PHOTO_ENTRY_USER_CD`) VALUES ('a1b4acf4-712d-4b05-8acd-e45964d8ccc6', '9ae42471-4842-4101-8404-5ace607b00a6', NULL, 'INITIAL', 'wound_photos/2026/10/demo_initial.jpg', 'صورة خط الأساس الأولية عند القبول', '2026-10-02 10:23:13', 'a817e223-7e7c-461c-90db-93742a8fd86d', '2759d629-76e6-4c59-bfd2-3a4aeadc2c54');
INSERT INTO `wc_wound_photo_tb` (`WC_PHOTO_CODE`, `WC_PHOTO_REG_DIAGNOSIS_CD`, `WC_PHOTO_DAILY_REVIEW_DETAIL_CD`, `WC_PHOTO_STAGE`, `WC_PHOTO_PATH`, `WC_PHOTO_CAPTION`, `WC_PHOTO_TAKEN_AT`, `WC_PHOTO_NURSE_CD`, `WC_PHOTO_ENTRY_USER_CD`) VALUES ('ba84bdac-18af-4455-a3c8-764d8f946b01', '9ae42471-4842-4101-8404-5ace607b00a6', 'c2d18a5a-e90f-4e50-96a0-dde296e153a4', 'REVIEW', 'wound_photos/2026/10/demo_review.jpg', 'صورة متابعة بعد 3 أيام من التضميد', '2026-10-05 10:23:13', 'a817e223-7e7c-461c-90db-93742a8fd86d', '2759d629-76e6-4c59-bfd2-3a4aeadc2c54');

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_assessment_choice_tb`
-- الحالة: [جدول النظام — جديد، غير موجود في القاعدة الحالية]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_assessment_choice_tb`;
CREATE TABLE `wc_assessment_choice_tb` (
  `WC_ACH_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد لخيار التقييم (UUID) - المفتاح الأساسي',
  `WC_ACH_CATEGORY` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'فئة الخيار الثابتة (WOUND_STATUS / WOUND_BED / WOUND_MARGIN / EXUDATE_AMOUNT / ...)',
  `WC_ACH_KEY` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المفتاح الثابت للخيار داخل فئته؛ لا يتغير حفاظاً على السجل التاريخي',
  `WC_ACH_LABEL_AR` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'التسمية العربية للخيار (محفوظة، والمعروض هو الاسم الإنجليزي)',
  `WC_ACH_LABEL_EN` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'الاسم الإنجليزي المعروض للخيار (المصطلحات الطبية تُعرض بالإنجليزية)',
  `WC_ACH_IS_ACTIVE` smallint NOT NULL DEFAULT '1' COMMENT 'حالة الخيار: 1 = فعّال، 0 = معطّل (يبقى مقروءاً في السجلات القديمة)',
  `WC_ACH_DISPLAY_ORDER` int NOT NULL DEFAULT '0' COMMENT 'ترتيب عرض الخيار داخل فئته',
  PRIMARY KEY (`WC_ACH_CODE`),
  UNIQUE KEY `wc_ach_category_key_unique` (`WC_ACH_CATEGORY`,`WC_ACH_KEY`),
  KEY `wc_ach_category_active_order_index` (`WC_ACH_CATEGORY`,`WC_ACH_IS_ACTIVE`,`WC_ACH_DISPLAY_ORDER`),
  CONSTRAINT `wc_ach_active_check` CHECK ((`WC_ACH_IS_ACTIVE` in (0,1)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='خيارات التقييم المنظم للجروح (قاع الجرح، الحواف، الإفرازات، الألم، الموقع...)';

-- Data for `wc_assessment_choice_tb`
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('74636a0f-6c4c-5f00-8283-5f73565e1189', 'WOUND_STATUS', 'FIRST_VISIT', 'الزيارة الأولى', 'First Visit', '1', '1');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('d8475fb6-eae6-5c57-a708-ff4dda9a6641', 'WOUND_STATUS', 'DETERIORATING', 'متدهور', 'Deteriorating', '1', '2');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('75ffdf3c-10a9-58f8-b1f2-375b9693278b', 'WOUND_STATUS', 'STATIC', 'ثابت', 'Static', '1', '3');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('fc988323-62c7-5e16-8938-e377a2db8399', 'WOUND_STATUS', 'IMPROVING', 'يتحسن', 'Improving', '1', '4');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('2d4da355-77d1-53cb-aa6b-2e803fb4f38b', 'WOUND_BED', 'GRANULATION', 'نسيج حبيبي', 'Granulation', '1', '1');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('fd72be76-4949-586c-be90-df16d8f4feb8', 'WOUND_BED', 'NECROTIC', 'نسيج نخري', 'Necrotic', '1', '2');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('8ac13d45-0745-5c9a-86bb-689e9f814bef', 'WOUND_BED', 'SLOUGHING', 'نسيج رخو متموت', 'Sloughing', '1', '3');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('671acde1-5c40-5e7d-87d2-007d751b59c8', 'WOUND_BED', 'EPITHELIALIZING', 'تكوّن ظهارة', 'Epithelializing', '1', '4');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('6545d402-17e8-5a8e-ae5b-a2a47712f3fd', 'WOUND_MARGIN', 'NONE_OBSERVED', 'لا توجد علامة مسجلة', 'None Observed', '1', '1');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('3a63bc4d-5566-5758-90bb-b7eac470839d', 'WOUND_MARGIN', 'ERYTHEMA', 'احمرار', 'Erythema', '1', '2');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('63777fca-5f85-5e4f-a262-0a8238058ec7', 'WOUND_MARGIN', 'EDEMA', 'وذمة', 'Edema', '1', '3');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('b0e19189-d70b-5503-9930-7547f0adb7fe', 'WOUND_MARGIN', 'DELAYED_HEALING', 'تأخر الالتئام', 'Delayed Healing', '1', '4');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('49f3a7f0-7793-5cad-9408-35cabf2b97ae', 'WOUND_MARGIN', 'POCKETING', 'جيوب', 'Pocketing', '1', '5');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('eb9f2b1e-695d-570d-a531-a61b1e10c11e', 'EXUDATE_AMOUNT', 'DRY', 'جاف', 'Dry', '1', '1');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('19f895da-643d-5ae9-9de3-8cc9a163822f', 'EXUDATE_AMOUNT', 'LOW', 'قليل', 'Low', '1', '2');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('76ac6747-9888-5728-a2db-63874c72aa31', 'EXUDATE_AMOUNT', 'MEDIUM', 'متوسط', 'Moderate', '1', '3');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('72164366-2474-5c14-86a4-de6b277f1c7b', 'EXUDATE_AMOUNT', 'HIGH', 'غزير', 'High', '1', '4');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('edaf969b-f319-5c89-93c3-d8e837621a94', 'EXUDATE_TYPE', 'WATERY', 'مائي', 'Watery', '1', '1');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('6d8a2470-bc35-5b86-9776-b488eba9af62', 'EXUDATE_TYPE', 'CLOUDY', 'عكر', 'Cloudy', '1', '2');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('e967154e-4bbd-52cf-8157-85a42a540022', 'EXUDATE_TYPE', 'PURULENT', 'صديدي', 'Purulent', '1', '3');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('d0be4b00-9abf-59a6-94a3-942aed07bd15', 'EXUDATE_TYPE', 'CLEAR', 'صافي', 'Clear', '1', '4');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('7796d504-faa8-5d33-a36c-34fe35a40bb8', 'ODOR', 'NONE', 'لا توجد رائحة', 'None', '1', '1');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('302d9143-1cee-5181-8cf3-f12c03a06d15', 'ODOR', 'SLIGHT', 'خفيفة', 'Slight', '1', '2');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('9bf9dcd9-f3b9-5ced-b9dd-3ff33a71c24c', 'ODOR', 'MODERATE', 'متوسطة', 'Moderate', '1', '3');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('7258dbf1-28d9-5e0c-860b-780852dbf893', 'ODOR', 'OFFENSIVE', 'كريهة', 'Offensive', '1', '4');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('e51134bd-d344-5e78-a9e2-80cee7d83f33', 'PAIN_SEVERITY', 'NONE', 'لا يوجد ألم', 'None', '1', '1');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('a0df59cf-f123-5ed0-8334-a01b6daa1c4a', 'PAIN_SEVERITY', 'MODERATE', 'متوسط', 'Moderate', '1', '2');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('d6a4b0ac-11f0-52ae-afa0-9455532aaa81', 'PAIN_SEVERITY', 'SEVERE', 'شديد', 'Severe', '1', '3');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('71a8b48d-7648-54e4-a8c0-fd699749fa22', 'FEVER', 'NONE', 'لا توجد حرارة', 'None', '1', '1');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('457159fa-50d3-5b92-bd77-306ed8a1c4cb', 'FEVER', 'LOW', 'حرارة بسيطة', 'Low-grade', '1', '2');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('03a25574-42b1-5ada-8800-7719aa38ac32', 'FEVER', 'HIGH', 'حرارة مرتفعة', 'High', '1', '3');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('07b2f891-1e5e-55a9-84e3-a109ad770324', 'NUTRITION', 'WELL_NOURISHED', 'جيد التغذية', 'Well Nourished', '1', '1');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('9dc115fd-efb9-5ce5-8d27-8ed276a40453', 'NUTRITION', 'MALNOURISHED', 'سوء تغذية', 'Malnourished', '1', '2');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('0920b847-5300-5563-98b4-e57f36e340fd', 'LOCATION', 'HEAD', 'الرأس', 'Head', '1', '1');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('e7838ae8-913e-5356-8f8b-9a687a04f86a', 'LOCATION', 'ABDOMEN', 'البطن', 'Abdomen', '1', '2');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('b9f67614-35f4-59b4-90ba-0ada1bde0aa1', 'LOCATION', 'BACK', 'الظهر', 'Back', '1', '3');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('260188a7-0083-5756-bcbb-f43613d5d3ea', 'LOCATION', 'LEG', 'الساق', 'Leg', '1', '4');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('5129c0d4-2079-5d2a-9e1b-98026048a62c', 'LOCATION', 'BUTTOCKS', 'الأرداف', 'Buttocks', '1', '5');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('4f421b77-a05a-5b15-a484-b241a025d862', 'LOCATION', 'FOOT', 'القدم', 'Foot', '1', '6');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('79353c82-2b51-57db-afad-9d9cfaedcf89', 'LOCATION', 'HAND', 'اليد', 'Hand', '1', '7');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('6fb9866a-2d63-549b-a15e-90e635a0564f', 'LOCATION', 'FOREARM', 'الساعد', 'Forearm', '1', '8');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('2b1269f2-68d6-5251-872a-6584546d00d4', 'LOCATION', 'OTHER', 'أخرى', 'Other', '1', '9');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('cbec0a9e-de4d-5df2-ac16-a6209cb7e4e2', 'LATERALITY', 'NOT_APPLICABLE', 'لا ينطبق', 'Not Applicable', '1', '1');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('503d315f-ffaa-5b7e-980c-b10298d2f491', 'LATERALITY', 'LEFT', 'يسار', 'Left', '1', '2');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('80edc088-f465-5b79-ba61-0fea9ba8c587', 'LATERALITY', 'RIGHT', 'يمين', 'Right', '1', '3');
INSERT INTO `wc_assessment_choice_tb` (`WC_ACH_CODE`, `WC_ACH_CATEGORY`, `WC_ACH_KEY`, `WC_ACH_LABEL_AR`, `WC_ACH_LABEL_EN`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`) VALUES ('6ba07a68-10cb-5023-aac6-549ddcebd6ae', 'LATERALITY', 'BILATERAL', 'ثنائي الجانب', 'Bilateral', '1', '4');

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_registration_pmh_tb`
-- الحالة: [جدول النظام — جديد، غير موجود في القاعدة الحالية]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_registration_pmh_tb`;
CREATE TABLE `wc_registration_pmh_tb` (
  `WC_RPMH_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد لربط التسجيل بالمرض المزمن (UUID) - المفتاح الأساسي',
  `WC_RPMH_REGISTRATION_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'رمز التسجيل السريري (FK -> WC_REGISTRATION_TB)',
  `WC_RPMH_PMH_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'رمز المرض المزمن من القائمة الأساسية (FK -> WC_PMH_TB)',
  PRIMARY KEY (`WC_RPMH_CODE`),
  UNIQUE KEY `wc_rpmh_registration_pmh_unique` (`WC_RPMH_REGISTRATION_CD`,`WC_RPMH_PMH_CD`),
  KEY `wc_registration_pmh_tb_wc_rpmh_pmh_cd_foreign` (`WC_RPMH_PMH_CD`),
  CONSTRAINT `wc_registration_pmh_tb_wc_rpmh_pmh_cd_foreign` FOREIGN KEY (`WC_RPMH_PMH_CD`) REFERENCES `wc_pmh_tb` (`WC_PMH_CODE`),
  CONSTRAINT `wc_registration_pmh_tb_wc_rpmh_registration_cd_foreign` FOREIGN KEY (`WC_RPMH_REGISTRATION_CD`) REFERENCES `wc_registration_tb` (`WC_REG_CODE`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='الأمراض المزمنة (PMH) المسجلة لكل تسجيل سريري';

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_wound_assessment_tb`
-- الحالة: [جدول النظام — جديد، غير موجود في القاعدة الحالية]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_wound_assessment_tb`;
CREATE TABLE `wc_wound_assessment_tb` (
  `WC_WA_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد للتقييم المنظم (UUID) - المفتاح الأساسي',
  `WC_WA_REG_DIAGNOSIS_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'الجرح المقيَّم (FK -> WC_REG_DIAGNOSIS_TB)',
  `WC_WA_REVIEW_DETAIL_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'سطر المراجعة لتقييم المتابعة؛ فارغ للتقييم الأولي (FK -> WC_REVIEW_DETAIL_TB)',
  `WC_WA_TYPE` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'نوع التقييم (INITIAL = أولي عند التسجيل / FOLLOW_UP = متابعة في مراجعة)',
  `WC_WA_CLINICAL_EMP_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'الموظف السريري منفذ التقييم (FK -> PER_EMPLOYEE_TB)',
  `WC_WA_WCT_EMP_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'عضو فريق الجروح المشارك (FK -> PER_EMPLOYEE_TB)',
  `WC_WA_DOCTOR_EMP_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'الطبيب المشارك (FK -> PER_EMPLOYEE_TB)',
  `WC_WA_ENTRY_USER_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المستخدم مدخل التقييم (FK -> PER_USERS_TB)',
  `WC_WA_ASSESSED_AT` datetime NOT NULL COMMENT 'تاريخ ووقت التقييم',
  `WC_WA_WOUND_STATUS_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Wound Status (FK -> WC_ASSESSMENT_CHOICE_TB، فئة WOUND_STATUS)',
  `WC_WA_EXUDATE_AMOUNT_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Exudate Amount (FK -> WC_ASSESSMENT_CHOICE_TB، فئة EXUDATE_AMOUNT)',
  `WC_WA_EXUDATE_TYPE_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Exudate Type؛ فارغ عندما تكون الإفرازات Dry (FK -> WC_ASSESSMENT_CHOICE_TB)',
  `WC_WA_ODOR_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Odor (FK -> WC_ASSESSMENT_CHOICE_TB، فئة ODOR)',
  `WC_WA_PAIN_SEVERITY_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Pain Severity (FK -> WC_ASSESSMENT_CHOICE_TB، فئة PAIN_SEVERITY)',
  `WC_WA_PAIN_AT_DRESSING` tinyint(1) NOT NULL COMMENT 'Pain at Dressing Change: 1 = نعم، 0 = لا',
  `WC_WA_FEVER_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Fever (FK -> WC_ASSESSMENT_CHOICE_TB، فئة FEVER)',
  `WC_WA_LENGTH_MM` decimal(8,2) DEFAULT NULL COMMENT 'Length بالمليمتر (أكبر من 0 وحتى 1000)',
  `WC_WA_WIDTH_MM` decimal(8,2) DEFAULT NULL COMMENT 'Width بالمليمتر (أكبر من 0 وحتى 1000)',
  `WC_WA_DEPTH_MM` decimal(8,2) DEFAULT NULL COMMENT 'Depth بالمليمتر (من 0 حتى 1000)',
  `WC_WA_LOCATION_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Location (FK -> WC_ASSESSMENT_CHOICE_TB، فئة LOCATION)',
  `WC_WA_LATERALITY_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Laterality (FK -> WC_ASSESSMENT_CHOICE_TB، فئة LATERALITY)',
  `WC_WA_LOCATION_NOTES` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Location Details نص حر',
  `WC_WA_SWAB_TAKEN` tinyint(1) DEFAULT NULL COMMENT 'Swab Taken: 1 = نعم، 0 = لا',
  `WC_WA_NUTRITION_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nutrition (FK -> WC_ASSESSMENT_CHOICE_TB، فئة NUTRITION)',
  `WC_WA_DRESSING_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Type of DSG (FK -> WC_DRESSING_TB)',
  `WC_WA_MEDICATION_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Medication (FK -> WC_MEDICATION_TB)',
  `WC_WA_PROCEDURE_CD` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Procedure (FK -> WC_PROCEDURE_TB)',
  `WC_WA_TREATMENT_NOTES` text COLLATE utf8mb4_unicode_ci COMMENT 'Treatment Plan / Notes نص حر',
  `WC_WA_CLINICAL_OBSERVATION` text COLLATE utf8mb4_unicode_ci COMMENT 'Clinical Observation نص حر',
  `WC_WA_WOUND_CHANGES` text COLLATE utf8mb4_unicode_ci COMMENT 'Wound Changes منذ الزيارة السابقة، نص حر',
  `WC_WA_FOLLOW_UP_REQUIRED` tinyint(1) NOT NULL COMMENT 'هل يحتاج الجرح متابعة: 1 = نعم، 0 = لا',
  PRIMARY KEY (`WC_WA_CODE`),
  UNIQUE KEY `wc_wa_review_detail_unique` (`WC_WA_REVIEW_DETAIL_CD`),
  KEY `wc_wa_diagnosis_assessed_index` (`WC_WA_REG_DIAGNOSIS_CD`,`WC_WA_ASSESSED_AT`),
  KEY `wc_wound_assessment_tb_wc_wa_clinical_emp_cd_foreign` (`WC_WA_CLINICAL_EMP_CD`),
  KEY `wc_wound_assessment_tb_wc_wa_wct_emp_cd_foreign` (`WC_WA_WCT_EMP_CD`),
  KEY `wc_wound_assessment_tb_wc_wa_doctor_emp_cd_foreign` (`WC_WA_DOCTOR_EMP_CD`),
  KEY `wc_wound_assessment_tb_wc_wa_entry_user_cd_foreign` (`WC_WA_ENTRY_USER_CD`),
  KEY `wc_wound_assessment_tb_wc_wa_wound_status_cd_foreign` (`WC_WA_WOUND_STATUS_CD`),
  KEY `wc_wound_assessment_tb_wc_wa_exudate_amount_cd_foreign` (`WC_WA_EXUDATE_AMOUNT_CD`),
  KEY `wc_wound_assessment_tb_wc_wa_exudate_type_cd_foreign` (`WC_WA_EXUDATE_TYPE_CD`),
  KEY `wc_wound_assessment_tb_wc_wa_odor_cd_foreign` (`WC_WA_ODOR_CD`),
  KEY `wc_wound_assessment_tb_wc_wa_pain_severity_cd_foreign` (`WC_WA_PAIN_SEVERITY_CD`),
  KEY `wc_wound_assessment_tb_wc_wa_fever_cd_foreign` (`WC_WA_FEVER_CD`),
  KEY `wc_wound_assessment_tb_wc_wa_location_cd_foreign` (`WC_WA_LOCATION_CD`),
  KEY `wc_wound_assessment_tb_wc_wa_laterality_cd_foreign` (`WC_WA_LATERALITY_CD`),
  KEY `wc_wound_assessment_tb_wc_wa_nutrition_cd_foreign` (`WC_WA_NUTRITION_CD`),
  KEY `wc_wound_assessment_tb_wc_wa_dressing_cd_foreign` (`WC_WA_DRESSING_CD`),
  KEY `wc_wound_assessment_tb_wc_wa_medication_cd_foreign` (`WC_WA_MEDICATION_CD`),
  KEY `wc_wound_assessment_tb_wc_wa_procedure_cd_foreign` (`WC_WA_PROCEDURE_CD`),
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_clinical_emp_cd_foreign` FOREIGN KEY (`WC_WA_CLINICAL_EMP_CD`) REFERENCES `per_employee_tb` (`PER_EMP_CODE`),
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_doctor_emp_cd_foreign` FOREIGN KEY (`WC_WA_DOCTOR_EMP_CD`) REFERENCES `per_employee_tb` (`PER_EMP_CODE`),
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_dressing_cd_foreign` FOREIGN KEY (`WC_WA_DRESSING_CD`) REFERENCES `wc_dressing_tb` (`WC_DRESS_CODE`),
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_entry_user_cd_foreign` FOREIGN KEY (`WC_WA_ENTRY_USER_CD`) REFERENCES `per_users_tb` (`PER_USER_CODE`),
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_exudate_amount_cd_foreign` FOREIGN KEY (`WC_WA_EXUDATE_AMOUNT_CD`) REFERENCES `wc_assessment_choice_tb` (`WC_ACH_CODE`),
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_exudate_type_cd_foreign` FOREIGN KEY (`WC_WA_EXUDATE_TYPE_CD`) REFERENCES `wc_assessment_choice_tb` (`WC_ACH_CODE`),
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_fever_cd_foreign` FOREIGN KEY (`WC_WA_FEVER_CD`) REFERENCES `wc_assessment_choice_tb` (`WC_ACH_CODE`),
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_laterality_cd_foreign` FOREIGN KEY (`WC_WA_LATERALITY_CD`) REFERENCES `wc_assessment_choice_tb` (`WC_ACH_CODE`),
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_location_cd_foreign` FOREIGN KEY (`WC_WA_LOCATION_CD`) REFERENCES `wc_assessment_choice_tb` (`WC_ACH_CODE`),
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_medication_cd_foreign` FOREIGN KEY (`WC_WA_MEDICATION_CD`) REFERENCES `wc_medication_tb` (`WC_MED_CODE`),
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_nutrition_cd_foreign` FOREIGN KEY (`WC_WA_NUTRITION_CD`) REFERENCES `wc_assessment_choice_tb` (`WC_ACH_CODE`),
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_odor_cd_foreign` FOREIGN KEY (`WC_WA_ODOR_CD`) REFERENCES `wc_assessment_choice_tb` (`WC_ACH_CODE`),
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_pain_severity_cd_foreign` FOREIGN KEY (`WC_WA_PAIN_SEVERITY_CD`) REFERENCES `wc_assessment_choice_tb` (`WC_ACH_CODE`),
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_procedure_cd_foreign` FOREIGN KEY (`WC_WA_PROCEDURE_CD`) REFERENCES `wc_procedure_tb` (`WC_PROC_CODE`),
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_reg_diagnosis_cd_foreign` FOREIGN KEY (`WC_WA_REG_DIAGNOSIS_CD`) REFERENCES `wc_reg_diagnosis_tb` (`WC_RDIAG_CODE`) ON DELETE CASCADE,
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_review_detail_cd_foreign` FOREIGN KEY (`WC_WA_REVIEW_DETAIL_CD`) REFERENCES `wc_review_detail_tb` (`WC_REVD_CODE`) ON DELETE CASCADE,
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_wct_emp_cd_foreign` FOREIGN KEY (`WC_WA_WCT_EMP_CD`) REFERENCES `per_employee_tb` (`PER_EMP_CODE`),
  CONSTRAINT `wc_wound_assessment_tb_wc_wa_wound_status_cd_foreign` FOREIGN KEY (`WC_WA_WOUND_STATUS_CD`) REFERENCES `wc_assessment_choice_tb` (`WC_ACH_CODE`),
  CONSTRAINT `wc_wa_measurement_check` CHECK ((((`WC_WA_LENGTH_MM` is null) or ((`WC_WA_LENGTH_MM` > 0) and (`WC_WA_LENGTH_MM` <= 1000))) and ((`WC_WA_WIDTH_MM` is null) or ((`WC_WA_WIDTH_MM` > 0) and (`WC_WA_WIDTH_MM` <= 1000))) and ((`WC_WA_DEPTH_MM` is null) or ((`WC_WA_DEPTH_MM` >= 0) and (`WC_WA_DEPTH_MM` <= 1000))))),
  CONSTRAINT `wc_wa_type_link_check` CHECK ((((`WC_WA_TYPE` = _utf8mb4'INITIAL') and (`WC_WA_REVIEW_DETAIL_CD` is null)) or ((`WC_WA_TYPE` = _utf8mb4'FOLLOW_UP') and (`WC_WA_REVIEW_DETAIL_CD` is not null))))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='التقييم المنظم لكل جرح: أولي عند التسجيل ومتابعة في كل مراجعة';

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_assessment_finding_tb`
-- الحالة: [جدول النظام — جديد، غير موجود في القاعدة الحالية]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_assessment_finding_tb`;
CREATE TABLE `wc_assessment_finding_tb` (
  `WC_AF_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد للنتيجة (UUID) - المفتاح الأساسي',
  `WC_AF_ASSESSMENT_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'التقييم المنظم (FK -> WC_WOUND_ASSESSMENT_TB)',
  `WC_AF_CHOICE_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'الخيار المختار من Wound Bed أو Wound Margins (FK -> WC_ASSESSMENT_CHOICE_TB)',
  PRIMARY KEY (`WC_AF_CODE`),
  UNIQUE KEY `wc_af_assessment_choice_unique` (`WC_AF_ASSESSMENT_CD`,`WC_AF_CHOICE_CD`),
  KEY `wc_assessment_finding_tb_wc_af_choice_cd_foreign` (`WC_AF_CHOICE_CD`),
  CONSTRAINT `wc_assessment_finding_tb_wc_af_assessment_cd_foreign` FOREIGN KEY (`WC_AF_ASSESSMENT_CD`) REFERENCES `wc_wound_assessment_tb` (`WC_WA_CODE`) ON DELETE CASCADE,
  CONSTRAINT `wc_assessment_finding_tb_wc_af_choice_cd_foreign` FOREIGN KEY (`WC_AF_CHOICE_CD`) REFERENCES `wc_assessment_choice_tb` (`WC_ACH_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='نتائج قاع الجرح وحوافه المختارة في كل تقييم منظم (اختيار متعدد)';

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_report_export_tb`
-- الحالة: [جدول النظام — جديد، غير موجود في القاعدة الحالية]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `wc_report_export_tb`;
CREATE TABLE `wc_report_export_tb` (
  `WC_RPEX_CODE` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المعرّف الفريد لعملية تصدير التقرير (UUID) - المفتاح الأساسي',
  `WC_RPEX_USER_CD` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'المستخدم الذي صدّر التقرير (FK -> PER_USERS_TB)',
  `WC_RPEX_EXPORTED_AT` datetime NOT NULL COMMENT 'تاريخ ووقت التصدير',
  `WC_RPEX_PERIOD_FROM` date NOT NULL COMMENT 'أول يوم من الشهر الأول في فترة التقرير',
  `WC_RPEX_PERIOD_TO` date NOT NULL COMMENT 'أول يوم من الشهر الأخير في فترة التقرير',
  `WC_RPEX_DEPARTMENT_CODES` text COLLATE utf8mb4_unicode_ci COMMENT 'رموز الأقسام المحددة (JSON)، فارغ = كل الأقسام',
  `WC_RPEX_CASE_COUNT` int NOT NULL COMMENT 'عدد الحالات في ورقة الحالات',
  PRIMARY KEY (`WC_RPEX_CODE`),
  KEY `wc_report_export_tb_wc_rpex_user_cd_index` (`WC_RPEX_USER_CD`),
  KEY `wc_report_export_tb_wc_rpex_exported_at_index` (`WC_RPEX_EXPORTED_AT`),
  CONSTRAINT `wc_report_export_tb_wc_rpex_user_cd_foreign` FOREIGN KEY (`WC_RPEX_USER_CD`) REFERENCES `per_users_tb` (`PER_USER_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='سجل عمليات تصدير التقارير الشهرية (من صدّر، ومتى، وأي فترة وأقسام)';

-- ============================================================================
-- Stored Procedures — العمليات السريرية داخل قاعدة البيانات
-- الحالة: [جدول النظام — جديد، غير موجود في القاعدة الحالية] (إجراءات مخزنة وليست جداول)
-- ============================================================================
-- لماذا توجد هذه الإجراءات:
--   - حسب دستور المشروع (Database-First) تُنفَّذ العمليات السريرية الأساسية داخل قاعدة البيانات، لا في التطبيق.
--   - كل عملية تكتب في عدة جداول معاً (التسجيل، الجروح، التقييمات، الصور، المواعيد)؛ الإجراء يكتبها كلها
--     في transaction واحدة: إما أن تُحفظ كاملة أو لا يُحفظ منها شيء.
-- كيف يستدعيها النظام:
--   - من App\Services\ClinicalWorkflows\MySqlClinicalWorkflowGateway بأمر CALL، ويُرسل البيانات كنص JSON واحد.
--   - كل إجراء يُرجع صفاً واحداً فيه رمز السجل الذي أنشأه.
--   - عند أي خطأ في البيانات يوقف الإجراء العملية بـ SIGNAL SQLSTATE '45000' ورسالة رمزية (مثل TREATMENT_REQUIRED)،
--     ويتراجع عن كل ما كتبه؛ النظام يعرض للمستخدم رسالة عربية عامة.
-- متطلبات مهمة:
--   - MySQL 8.0 أو أحدث (تستخدم JSON_TABLE).
--   - بعض الإجراءات تُدرج صفوفاً بلا أسماء أعمدة (INSERT INTO ... VALUES)، فهي تعتمد على ترتيب الأعمدة كما في هذا الملف:
--     wc_reg_diagnosis_tb و wc_review_detail_tb و wc_scheduled_review_tb و wc_wound_assessment_tb.
--     أي تغيير في ترتيب أعمدة هذه الجداول يتطلب تعديل الإجراءات.
--   - عند النقل إلى Oracle تُعاد كتابتها يدوياً بـ PL/SQL (المبدأ السادس في الدستور).
--   - DELIMITER $$ يعمل في mysql client و MySQL Workbench و phpMyAdmin.
--   - يحفظ MySQL مع كل إجراء الـ collation والـ sql_mode الخاصين بالاتصال لحظة إنشائه. لذلك يجب إنشاؤها بنفس
--     إعدادات اتصال Laravel (السطران التاليان). لو أُنشئت بالـ collation الافتراضي في MySQL 8 (utf8mb4_0900_ai_ci)
--     تفشل كل العمليات بخطأ «Illegal mix of collations»، ولو أُنشئت بـ sql_mode فارغ قد تُقبل بيانات خاطئة بصمت.

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

DELIMITER $$

-- ----------------------------------------------------------------------------
-- SP_WC_INSERT_STRUCTURED_ASSESSMENT(p_diagnosis, p_detail, p_type, p_shared, p_assessment)
-- الدور: إجراء مساعد داخلي يحفظ تقييماً منظماً واحداً لجرح واحد. لا يستدعيه التطبيق مباشرة؛
--        تستدعيه الإجراءات الثلاثة الأخرى، ويعمل داخل الـ transaction الخاصة بالإجراء الذي استدعاه.
-- المدخلات: p_diagnosis = الجرح، p_detail = سطر المراجعة (فارغ للتقييم الأولي)،
--           p_type = INITIAL أو FOLLOW_UP، p_shared = بيانات مشتركة (الممرض، المستخدم، الوقت)، p_assessment = بيانات التقييم.
-- يتحقق من:
--   - أن كل خيار مختار فعّال ومن الفئة الصحيحة في wc_assessment_choice_tb.
--   - Wound Status في التقييم الأولي = First Visit، وفي المتابعة ليس First Visit.
--   - التقييم الأولي فيه Fever و Location و Nutrition.
--   - Wound Bed خيار واحد على الأقل بلا تكرار؛ Wound Margins مطلوب في التقييم الأولي، و None Observed لا يجتمع مع غيره.
--   - إذا كانت Exudate Amount = Dry فلا يُسمح بـ Exudate Type، وإلا فهو مطلوب.
--   - علاج واحد على الأقل: ضماد أو دواء أو إجراء أو ملاحظة علاج، وكلها فعّالة.
--   - القياسات: الطول والعرض أكبر من 0، والعمق 0 أو أكثر، وكلها حتى 1000 mm.
-- يكتب في: wc_wound_assessment_tb (التقييم)، wc_assessment_finding_tb (اختيارات قاع الجرح وحوافه)،
--          wc_wound_photo_tb (بيانات الصور المرفقة بالتقييم).
-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `SP_WC_INSERT_STRUCTURED_ASSESSMENT`$$
CREATE PROCEDURE SP_WC_INSERT_STRUCTURED_ASSESSMENT(
    IN p_diagnosis CHAR(36), IN p_detail CHAR(36), IN p_type VARCHAR(20),
    IN p_shared JSON, IN p_assessment JSON
)
BEGIN
    DECLARE v_assessment CHAR(36) DEFAULT UUID();
    DECLARE v_status CHAR(36); DECLARE v_amount CHAR(36); DECLARE v_type CHAR(36);
    DECLARE v_odor CHAR(36); DECLARE v_pain CHAR(36); DECLARE v_clinician CHAR(36);
    DECLARE v_bed_count INT DEFAULT 0; DECLARE v_margin_count INT DEFAULT 0;
    SET v_status = JSON_UNQUOTE(JSON_EXTRACT(p_assessment, '$.wound_status_choice_code'));
    SET v_amount = JSON_UNQUOTE(JSON_EXTRACT(p_assessment, '$.exudate_amount_choice_code'));
    SET v_type = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_assessment, '$.exudate_type_choice_code')), 'null');
    SET v_odor = JSON_UNQUOTE(JSON_EXTRACT(p_assessment, '$.odor_choice_code'));
    SET v_pain = JSON_UNQUOTE(JSON_EXTRACT(p_assessment, '$.pain_severity_choice_code'));
    SET v_clinician = COALESCE(JSON_UNQUOTE(JSON_EXTRACT(p_shared, '$.clinic_nurse_code')), JSON_UNQUOTE(JSON_EXTRACT(p_shared, '$.nurse_code')));

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

    INSERT INTO wc_wound_assessment_tb VALUES (
        v_assessment,p_diagnosis,p_detail,p_type,v_clinician,
        NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_shared,'$.wct_employee_code')),'null'), NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_shared,'$.doctor_employee_code')),'null'),
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
    INSERT INTO wc_wound_photo_tb (WC_PHOTO_CODE,WC_PHOTO_REG_DIAGNOSIS_CD,WC_PHOTO_DAILY_REVIEW_DETAIL_CD,WC_PHOTO_STAGE,WC_PHOTO_PATH,WC_PHOTO_CAPTION,WC_PHOTO_TAKEN_AT,WC_PHOTO_NURSE_CD,WC_PHOTO_ENTRY_USER_CD,WC_PHOTO_ASSESSMENT_CD)
    SELECT COALESCE(photo_code,UUID()),p_diagnosis,p_detail,IF(p_type='INITIAL','INITIAL','REVIEW'),path,caption,COALESCE(taken_at,NOW()),v_clinician,JSON_UNQUOTE(JSON_EXTRACT(p_shared,'$.entry_user_code')),v_assessment
    FROM JSON_TABLE(COALESCE(JSON_EXTRACT(p_assessment,'$.photos'),JSON_ARRAY()), '$[*]' COLUMNS(photo_code CHAR(36) PATH '$.code',path VARCHAR(255) PATH '$.path',caption TEXT PATH '$.caption',taken_at DATETIME PATH '$.taken_at')) photos;
END$$

-- ----------------------------------------------------------------------------
-- SP_WC_ACCEPT_REGISTRATION_WITH_ASSESSMENTS(p_request, p_payload)
-- الدور: قبول طلب تسجيل وفتح ملف المريض في العيادة مع التقييم الأولي لكل جروحه.
-- يُستدعى من: قبول الطلب في شاشة الطلب (RegistrationRequestController@accept).
-- يتحقق من:
--   - أن الطلب PENDING أو VISIT_IN_PROGRESS ولم يُنشأ له تسجيل من قبل (يقفل صف الطلب أثناء العملية).
--   - جرح واحد على الأقل.
--   - وصف الحساسية موجود عند وجود حساسية، و«لا توجد أمراض مصاحبة» لا يجتمع مع اختيار أمراض.
--   - خطة متابعة: تاريخ الزيارة القادمة أو تكرار المتابعة.
--   - نوع الجرح والتشخيص لكل جرح فعّالان.
-- يكتب في: يغيّر حالة الطلب إلى ACCEPTED، وينشئ صف wc_registration_tb، وروابط wc_registration_pmh_tb،
--          وصفاً في wc_reg_diagnosis_tb لكل جرح مع تقييمه الأولي (عبر SP_WC_INSERT_STRUCTURED_ASSESSMENT)،
--          وموعداً PENDING في wc_scheduled_review_tb إن حُدد تاريخ الزيارة القادمة،
--          ويحذف المواعيد المعلقة للمريض من تسجيلاته السابقة.
-- يُرجع: registration_code.
-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `SP_WC_ACCEPT_REGISTRATION_WITH_ASSESSMENTS`$$
CREATE PROCEDURE SP_WC_ACCEPT_REGISTRATION_WITH_ASSESSMENTS(IN p_request CHAR(36), IN p_payload JSON)
BEGIN
    DECLARE v_registration CHAR(36) DEFAULT UUID(); DECLARE v_patient CHAR(36); DECLARE v_status VARCHAR(30);
    DECLARE v_i INT DEFAULT 0; DECLARE v_count INT; DECLARE v_item JSON; DECLARE v_diagnosis CHAR(36);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
    START TRANSACTION;
    SELECT WC_REQ_PATIENT_CD,WC_REQ_STATUS INTO v_patient,v_status FROM wc_request_reg_tb WHERE WC_REQ_CODE=p_request FOR UPDATE;
    IF v_status NOT IN ('PENDING','VISIT_IN_PROGRESS') OR EXISTS(SELECT 1 FROM wc_registration_tb WHERE WC_REG_REQUEST_CD=p_request) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='REQUEST_ALREADY_DECIDED'; END IF;
    SET v_count=JSON_LENGTH(COALESCE(JSON_EXTRACT(p_payload,'$.diagnoses'),JSON_ARRAY()));
    IF v_count=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='AT_LEAST_ONE_WOUND_REQUIRED'; END IF;
    IF JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.has_allergies')) IN ('true','1') AND NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.allergies_description'))),'') IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='ALLERGY_DESCRIPTION_REQUIRED'; END IF;
    IF JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.no_comorbidities')) IN ('true','1') AND JSON_LENGTH(COALESCE(JSON_EXTRACT(p_payload,'$.pmh_codes'),JSON_ARRAY()))>0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='PMH_NONE_IS_EXCLUSIVE'; END IF;
    IF NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.next_visit_date')),'null') IS NULL AND NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.follow_up_frequency'))),'') IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='FOLLOW_UP_PLAN_REQUIRED'; END IF;
    UPDATE wc_request_reg_tb SET WC_REQ_STATUS='ACCEPTED',WC_REQ_REVIEWED_BY_CD=JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.clinic_nurse_code')),WC_REQ_REVIEWED_ON=NOW() WHERE WC_REQ_CODE=p_request;
    INSERT INTO wc_registration_tb (WC_REG_CODE,WC_REG_REQUEST_CD,WC_REG_PATIENT_CD,WC_REG_DEPARTMENT_CD,WC_REG_PROCEDURE_CD,WC_REG_PMH_NOTES,WC_REG_DATE,WC_REG_NURSE_CD,WC_REG_ENTRY_USER_CD,WC_REG_STATUS,WC_REG_HAS_ALLERGIES,WC_REG_ALLERGIES_DESCRIPTION,WC_REG_NO_COMORBIDITIES,WC_REG_NEXT_VISIT_DATE,WC_REG_FOLLOW_UP_FREQUENCY,WC_REG_FOLLOW_UP_OBJECTIVE)
    VALUES(v_registration,p_request,v_patient,JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.department_code')),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.procedure_code')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.pmh_notes')),'null'),COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.assessed_at')),'null'),NOW()),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.clinic_nurse_code')),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.entry_user_code')),'ACTIVE',CASE JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.has_allergies')) WHEN 'true' THEN 1 WHEN '1' THEN 1 WHEN 'false' THEN 0 WHEN '0' THEN 0 END,NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.allergies_description')),'null'),CASE JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.no_comorbidities')) WHEN 'true' THEN 1 WHEN '1' THEN 1 WHEN 'false' THEN 0 WHEN '0' THEN 0 END,NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.next_visit_date')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.follow_up_frequency')),'null'),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.follow_up_objective')));
    INSERT INTO wc_registration_pmh_tb SELECT UUID(),v_registration,code FROM JSON_TABLE(COALESCE(JSON_EXTRACT(p_payload,'$.pmh_codes'),JSON_ARRAY()),'$[*]' COLUMNS(code CHAR(36) PATH '$')) jt JOIN wc_pmh_tb p ON p.WC_PMH_CODE=jt.code AND p.WC_PMH_IS_ACTIVE=1;
    WHILE v_i<v_count DO
        SET v_item=JSON_EXTRACT(p_payload,CONCAT('$.diagnoses[',v_i,']')); SET v_diagnosis=UUID();
        IF NOT EXISTS(SELECT 1 FROM wc_wound_type_tb WHERE WC_WTYPE_CODE=JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.wound_type_code')) AND WC_WTYPE_IS_ACTIVE=1) OR NOT EXISTS(SELECT 1 FROM wc_diagnosis_tb WHERE WC_DIAG_CODE=JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.diagnosis_code')) AND WC_DIAG_IS_ACTIVE=1) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_WOUND_MASTER_DATA'; END IF;
        INSERT INTO wc_reg_diagnosis_tb VALUES(v_diagnosis,v_registration,JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.diagnosis_code')),JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.wound_type_code')),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.location_notes')),'null'),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.clinic_nurse_code')),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.entry_user_code')));
        CALL SP_WC_INSERT_STRUCTURED_ASSESSMENT(v_diagnosis,NULL,'INITIAL',p_payload,v_item); SET v_i=v_i+1;
    END WHILE;
    IF NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.next_visit_date')),'null') IS NOT NULL THEN INSERT INTO wc_scheduled_review_tb VALUES(UUID(),v_registration,JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.entry_user_code')),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.next_visit_date')),1,'PENDING',JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.follow_up_objective')),NOW(),NULL); END IF;
    DELETE s FROM wc_scheduled_review_tb s JOIN wc_registration_tb r ON r.WC_REG_CODE=s.WC_SREV_REGISTRATION_CD WHERE r.WC_REG_PATIENT_CD=v_patient AND r.WC_REG_CODE<>v_registration AND s.WC_SREV_STATUS='PENDING';
    COMMIT; SELECT v_registration AS registration_code;
END$$

-- ----------------------------------------------------------------------------
-- SP_WC_ADD_WOUND_WITH_ASSESSMENT(p_registration, p_payload)
-- الدور: إضافة جرح جديد إلى تسجيل نشط، مع تقييمه الأولي.
-- يُستدعى من: نافذة إضافة جرح في ملف التسجيل (RegistrationController@storeDiagnosis).
-- يتحقق من: أن التسجيل ACTIVE (يقفل صفه أثناء العملية)، وأن نوع الجرح والتشخيص فعّالان.
-- يكتب في: wc_reg_diagnosis_tb، ثم التقييم الأولي عبر SP_WC_INSERT_STRUCTURED_ASSESSMENT.
-- يُرجع: registration_diagnosis_code.
-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `SP_WC_ADD_WOUND_WITH_ASSESSMENT`$$
CREATE PROCEDURE SP_WC_ADD_WOUND_WITH_ASSESSMENT(IN p_registration CHAR(36), IN p_payload JSON)
BEGIN
    DECLARE v_status VARCHAR(20); DECLARE v_diagnosis CHAR(36) DEFAULT UUID(); DECLARE v_item JSON;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END; START TRANSACTION;
    SELECT WC_REG_STATUS INTO v_status FROM wc_registration_tb WHERE WC_REG_CODE=p_registration FOR UPDATE;
    IF v_status<>'ACTIVE' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='REGISTRATION_NOT_ACTIVE'; END IF;
    SET v_item=COALESCE(JSON_EXTRACT(p_payload,'$.assessment'),p_payload);
    IF NOT EXISTS(SELECT 1 FROM wc_wound_type_tb WHERE WC_WTYPE_CODE=JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.wound_type_code')) AND WC_WTYPE_IS_ACTIVE=1) OR NOT EXISTS(SELECT 1 FROM wc_diagnosis_tb WHERE WC_DIAG_CODE=JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.diagnosis_code')) AND WC_DIAG_IS_ACTIVE=1) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='INVALID_WOUND_MASTER_DATA'; END IF;
    INSERT INTO wc_reg_diagnosis_tb VALUES(v_diagnosis,p_registration,JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.diagnosis_code')),JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.wound_type_code')),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.location_notes')),'null'),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.clinic_nurse_code')),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.entry_user_code')));
    CALL SP_WC_INSERT_STRUCTURED_ASSESSMENT(v_diagnosis,NULL,'INITIAL',p_payload,v_item);
    COMMIT; SELECT v_diagnosis AS registration_diagnosis_code;
END$$

-- ----------------------------------------------------------------------------
-- SP_WC_COMPLETE_REVIEW_WITH_ASSESSMENTS(p_registration, p_scheduled, p_payload)
-- الدور: حفظ مراجعة يومية (p_scheduled فارغ) أو إتمام موعد مراجعة مجدول (p_scheduled = رمز الموعد)،
--        مع تقييم متابعة لكل جرح.
-- يُستدعى من: حفظ المراجعة اليومية أو المجدولة (DailyReviewController@store).
-- منع التكرار: إذا وصل submission_key نفسه مرة ثانية (ضغط الزر مرتين مثلاً) يُرجع المراجعة الموجودة ولا يكتب شيئاً.
-- يتحقق من:
--   - المراجعة اليومية: التسجيل ACTIVE. المراجعة المجدولة: الموعد PENDING ويتبع هذا التسجيل.
--   - أن المراجعة تحوي سطراً واحداً لكل جرح من جروح التسجيل، لا أقل ولا أكثر.
-- يكتب في: رأس المراجعة في wc_daily_review_tb، وسطراً لكل جرح في wc_review_detail_tb مع تقييم متابعته،
--          ويغيّر الموعد المجدول إلى COMPLETED، ويحدّث خطة المتابعة في wc_registration_tb،
--          وينشئ الموعد التالي PENDING برقم تسلسلي إن حُدد تاريخه،
--          ويُغلق التسجيل (DISCHARGED) إذا كانت النتيجة الإجمالية IMPROVED_DISCHARGE أو WORSENED_ADMISSION أو DEATH.
-- يُرجع: daily_review_code.
-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `SP_WC_COMPLETE_REVIEW_WITH_ASSESSMENTS`$$
CREATE PROCEDURE SP_WC_COMPLETE_REVIEW_WITH_ASSESSMENTS(IN p_registration CHAR(36), IN p_scheduled CHAR(36), IN p_payload JSON)
main: BEGIN
    DECLARE v_review CHAR(36); DECLARE v_status VARCHAR(20); DECLARE v_schedule_status VARCHAR(20);
    DECLARE v_i INT DEFAULT 0; DECLARE v_count INT; DECLARE v_expected INT; DECLARE v_item JSON; DECLARE v_diagnosis CHAR(36); DECLARE v_detail CHAR(36);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
    SELECT WC_DREV_CODE INTO v_review FROM wc_daily_review_tb WHERE WC_DREV_SUBMISSION_KEY=JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.submission_key')) LIMIT 1;
    IF v_review IS NOT NULL THEN SELECT v_review AS daily_review_code; LEAVE main; END IF;
    START TRANSACTION; SELECT WC_REG_STATUS INTO v_status FROM wc_registration_tb WHERE WC_REG_CODE=p_registration FOR UPDATE;
    IF p_scheduled IS NULL AND v_status<>'ACTIVE' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='REGISTRATION_NOT_ACTIVE'; END IF;
    IF p_scheduled IS NOT NULL THEN SELECT WC_SREV_STATUS INTO v_schedule_status FROM wc_scheduled_review_tb WHERE WC_SREV_CODE=p_scheduled AND WC_SREV_REGISTRATION_CD=p_registration FOR UPDATE; IF v_schedule_status<>'PENDING' OR v_schedule_status IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SCHEDULE_NOT_PENDING_OR_OWNED'; END IF; END IF;
    SET v_count=JSON_LENGTH(COALESCE(JSON_EXTRACT(p_payload,'$.details'),JSON_ARRAY())); SELECT COUNT(*) INTO v_expected FROM wc_reg_diagnosis_tb WHERE WC_RDIAG_REGISTRATION_CD=p_registration;
    SELECT COUNT(DISTINCT jt.code) INTO @covered FROM JSON_TABLE(JSON_EXTRACT(p_payload,'$.details'),'$[*]' COLUMNS(code CHAR(36) PATH '$.registration_diagnosis_code')) jt JOIN wc_reg_diagnosis_tb d ON d.WC_RDIAG_CODE=jt.code AND d.WC_RDIAG_REGISTRATION_CD=p_registration;
    IF v_count<>v_expected OR @covered<>v_expected THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='EXACT_DIAGNOSIS_COVERAGE_REQUIRED'; END IF;
    SET v_review=UUID();
    INSERT INTO wc_daily_review_tb (WC_DREV_CODE,WC_DREV_REGISTRATION_CD,WC_DREV_SCHEDULED_REVIEW_CD,WC_DREV_VISIT_DATE,WC_DREV_NURSE_CD,WC_DREV_ENTRY_USER_CD,WC_DREV_OVERALL_STATUS,WC_DREV_GENERAL_NOTES,WC_DREV_SUBMISSION_KEY)
    VALUES(v_review,p_registration,p_scheduled,COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.assessed_at')),'null'),NOW()),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.nurse_code')),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.entry_user_code')),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.overall_status')),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.general_notes')),'null'),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.submission_key')));
    WHILE v_i<v_count DO
        SET v_item=JSON_EXTRACT(p_payload,CONCAT('$.details[',v_i,']')); SET v_diagnosis=JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.registration_diagnosis_code')); SET v_detail=UUID();
        INSERT INTO wc_review_detail_tb VALUES(v_detail,v_review,IF(p_scheduled IS NULL,'DAILY','SCHEDULED'),v_diagnosis,NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.clinical_observation')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.wound_changes')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.dressing_code')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.medication_code')),'null'),NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.outcome_status')),'null'),CASE JSON_UNQUOTE(JSON_EXTRACT(v_item,'$.follow_up_required')) WHEN 'false' THEN 0 WHEN '0' THEN 0 ELSE 1 END);
        CALL SP_WC_INSERT_STRUCTURED_ASSESSMENT(v_diagnosis,v_detail,'FOLLOW_UP',p_payload,v_item); SET v_i=v_i+1;
    END WHILE;
    IF p_scheduled IS NOT NULL THEN UPDATE wc_scheduled_review_tb SET WC_SREV_STATUS='COMPLETED',WC_SREV_COMPLETED_ON=NOW() WHERE WC_SREV_CODE=p_scheduled; END IF;
    UPDATE wc_registration_tb SET WC_REG_NEXT_VISIT_DATE=NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.next_visit_date')),'null'),WC_REG_FOLLOW_UP_FREQUENCY=NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.follow_up_frequency')),'null'),WC_REG_FOLLOW_UP_OBJECTIVE=NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.follow_up_objective')),'null') WHERE WC_REG_CODE=p_registration;
    IF NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.next_visit_date')),'null') IS NOT NULL THEN SELECT COALESCE(MAX(WC_SREV_VISIT_SERIAL),0)+1 INTO @serial FROM wc_scheduled_review_tb WHERE WC_SREV_REGISTRATION_CD=p_registration; INSERT INTO wc_scheduled_review_tb VALUES(UUID(),p_registration,JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.entry_user_code')),JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.next_visit_date')),@serial,'PENDING',NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.follow_up_objective')),'null'),NOW(),NULL); END IF;
    IF JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.overall_status')) IN ('IMPROVED_DISCHARGE','WORSENED_ADMISSION','DEATH') THEN UPDATE wc_registration_tb SET WC_REG_STATUS='DISCHARGED',WC_REG_DISCHARGED_AT=NOW(),WC_REG_DISCHARGED_BY_EMP_CD=JSON_UNQUOTE(JSON_EXTRACT(p_payload,'$.nurse_code')) WHERE WC_REG_CODE=p_registration; END IF;
    COMMIT; SELECT v_review AS daily_review_code;
END$$

DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;
-- ============================================================================
-- End of Database Export
-- ============================================================================
