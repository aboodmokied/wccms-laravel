-- ============================================================================
-- Wound Care Clinic Management System (WCCMS) - Complete Database Export
-- Database: wccms
-- Target Engine: MySQL / MariaDB (Portable DB-First Schema)
-- Generated Date: 2026-10-05 10:23:38
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;
SET TIME_ZONE = '+00:00';

-- ----------------------------------------------------------------------------
-- Table Structure for `per_users_tb`
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
  CONSTRAINT `wc_registration_tb_wc_reg_request_cd_foreign` FOREIGN KEY (`WC_REG_REQUEST_CD`) REFERENCES `wc_request_reg_tb` (`WC_REQ_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='التسجيل السريري للمريض في العيادة بعد قبول الطلب';

-- Data for `wc_registration_tb`
INSERT INTO `wc_registration_tb` (`WC_REG_CODE`, `WC_REG_REQUEST_CD`, `WC_REG_PATIENT_CD`, `WC_REG_DEPARTMENT_CD`, `WC_REG_PROCEDURE_CD`, `WC_REG_PROCEDURE_PERFORMED_BY_CD`, `WC_REG_PROCEDURE_PERFORMED_AT`, `WC_REG_PMH_NOTES`, `WC_REG_DATE`, `WC_REG_NURSE_CD`, `WC_REG_ENTRY_USER_CD`, `WC_REG_STATUS`, `WC_REG_DISCHARGE_DATE`, `WC_REG_DISCHARGE_TYPE`, `WC_REG_DISCHARGED_AT`, `WC_REG_DISCHARGED_BY_EMP_CD`) VALUES ('01b96648-79de-47fb-8392-2b79d4488f49', '066546a7-0d40-4fc8-a0fe-539c3359744b', '5a019650-e8e4-42b2-af41-e5801070ec65', '3ac8e8dc-99e1-4682-a15d-7f84f70fa255', NULL, NULL, NULL, NULL, '2026-10-05 10:23:13', 'a817e223-7e7c-461c-90db-93742a8fd86d', '2759d629-76e6-4c59-bfd2-3a4aeadc2c54', 'ACTIVE', NULL, NULL, NULL, NULL);
INSERT INTO `wc_registration_tb` (`WC_REG_CODE`, `WC_REG_REQUEST_CD`, `WC_REG_PATIENT_CD`, `WC_REG_DEPARTMENT_CD`, `WC_REG_PROCEDURE_CD`, `WC_REG_PROCEDURE_PERFORMED_BY_CD`, `WC_REG_PROCEDURE_PERFORMED_AT`, `WC_REG_PMH_NOTES`, `WC_REG_DATE`, `WC_REG_NURSE_CD`, `WC_REG_ENTRY_USER_CD`, `WC_REG_STATUS`, `WC_REG_DISCHARGE_DATE`, `WC_REG_DISCHARGE_TYPE`, `WC_REG_DISCHARGED_AT`, `WC_REG_DISCHARGED_BY_EMP_CD`) VALUES ('a9acc842-20bd-46df-b8dc-4fe965e9cda1', '503852bc-ecd4-43c3-8f7f-22d1746ebd8e', '6ab65c84-ac86-4fee-addc-e5b57fbe1ca4', '11d97034-fb23-4a17-aaaf-85cf2d5768ce', '1e38fe8e-3d4f-41a1-a823-650304a8fec1', NULL, NULL, 'مرض السكري من النوع الثاني + ارتفاع ضغط الدم', '2026-09-25 10:23:00', 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'da612d56-593a-4a66-a6ea-16ffafbf73bd', 'ACTIVE', NULL, NULL, NULL, NULL);
INSERT INTO `wc_registration_tb` (`WC_REG_CODE`, `WC_REG_REQUEST_CD`, `WC_REG_PATIENT_CD`, `WC_REG_DEPARTMENT_CD`, `WC_REG_PROCEDURE_CD`, `WC_REG_PROCEDURE_PERFORMED_BY_CD`, `WC_REG_PROCEDURE_PERFORMED_AT`, `WC_REG_PMH_NOTES`, `WC_REG_DATE`, `WC_REG_NURSE_CD`, `WC_REG_ENTRY_USER_CD`, `WC_REG_STATUS`, `WC_REG_DISCHARGE_DATE`, `WC_REG_DISCHARGE_TYPE`, `WC_REG_DISCHARGED_AT`, `WC_REG_DISCHARGED_BY_EMP_CD`) VALUES ('b858118c-f8b2-4951-a052-1a23e94f668b', 'b97c9b9f-b6df-4014-aae9-3fea3dacb731', '3dd77f14-fb20-4a8c-a789-a05bb0fdd88b', '11d97034-fb23-4a17-aaaf-85cf2d5768ce', '1e38fe8e-3d4f-41a1-a823-650304a8fec1', NULL, NULL, 'لا توجد أمراض مزمنة', '2026-09-15 10:23:00', 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'da612d56-593a-4a66-a6ea-16ffafbf73bd', 'DISCHARGED', NULL, NULL, '2026-10-03 10:23:00', 'bb948a14-477e-4d1b-84d3-6afec72e4ef3');
INSERT INTO `wc_registration_tb` (`WC_REG_CODE`, `WC_REG_REQUEST_CD`, `WC_REG_PATIENT_CD`, `WC_REG_DEPARTMENT_CD`, `WC_REG_PROCEDURE_CD`, `WC_REG_PROCEDURE_PERFORMED_BY_CD`, `WC_REG_PROCEDURE_PERFORMED_AT`, `WC_REG_PMH_NOTES`, `WC_REG_DATE`, `WC_REG_NURSE_CD`, `WC_REG_ENTRY_USER_CD`, `WC_REG_STATUS`, `WC_REG_DISCHARGE_DATE`, `WC_REG_DISCHARGE_TYPE`, `WC_REG_DISCHARGED_AT`, `WC_REG_DISCHARGED_BY_EMP_CD`) VALUES ('e93f27e2-01a7-4a6f-b7e7-6dda7f6ed3e2', '68734ec8-fb88-445b-b0a5-7590a9513d5f', 'e2215585-de10-4f9a-8caa-5f2d606f0466', '11d97034-fb23-4a17-aaaf-85cf2d5768ce', '2fd7b16e-f37f-4f14-b6e5-fc257d9777e7', NULL, NULL, 'حساسية من البنسلين', '2026-09-30 10:23:00', 'bb948a14-477e-4d1b-84d3-6afec72e4ef3', 'da612d56-593a-4a66-a6ea-16ffafbf73bd', 'ACTIVE', NULL, NULL, NULL, NULL);

-- ----------------------------------------------------------------------------
-- Table Structure for `wc_reg_diagnosis_tb`
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
  PRIMARY KEY (`WC_DREV_CODE`),
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
  PRIMARY KEY (`WC_PHOTO_CODE`),
  KEY `wc_wound_photo_tb_wc_photo_reg_diagnosis_cd_index` (`WC_PHOTO_REG_DIAGNOSIS_CD`),
  KEY `wc_wound_photo_tb_wc_photo_daily_review_detail_cd_index` (`WC_PHOTO_DAILY_REVIEW_DETAIL_CD`),
  KEY `wc_wound_photo_tb_wc_photo_stage_index` (`WC_PHOTO_STAGE`),
  KEY `wc_wound_photo_tb_wc_photo_nurse_cd_index` (`WC_PHOTO_NURSE_CD`),
  KEY `wc_wound_photo_tb_wc_photo_entry_user_cd_index` (`WC_PHOTO_ENTRY_USER_CD`),
  CONSTRAINT `wc_wound_photo_tb_wc_photo_daily_review_detail_cd_foreign` FOREIGN KEY (`WC_PHOTO_DAILY_REVIEW_DETAIL_CD`) REFERENCES `wc_review_detail_tb` (`WC_REVD_CODE`) ON DELETE SET NULL,
  CONSTRAINT `wc_wound_photo_tb_wc_photo_entry_user_cd_foreign` FOREIGN KEY (`WC_PHOTO_ENTRY_USER_CD`) REFERENCES `per_users_tb` (`PER_USER_CODE`),
  CONSTRAINT `wc_wound_photo_tb_wc_photo_nurse_cd_foreign` FOREIGN KEY (`WC_PHOTO_NURSE_CD`) REFERENCES `per_employee_tb` (`PER_EMP_CODE`),
  CONSTRAINT `wc_wound_photo_tb_wc_photo_reg_diagnosis_cd_foreign` FOREIGN KEY (`WC_PHOTO_REG_DIAGNOSIS_CD`) REFERENCES `wc_reg_diagnosis_tb` (`WC_RDIAG_CODE`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول التوثيق البصري وصور الجروح السريرية عند القبول وأثناء المراجعات الدوريّة';

-- Data for `wc_wound_photo_tb`
INSERT INTO `wc_wound_photo_tb` (`WC_PHOTO_CODE`, `WC_PHOTO_REG_DIAGNOSIS_CD`, `WC_PHOTO_DAILY_REVIEW_DETAIL_CD`, `WC_PHOTO_STAGE`, `WC_PHOTO_PATH`, `WC_PHOTO_CAPTION`, `WC_PHOTO_TAKEN_AT`, `WC_PHOTO_NURSE_CD`, `WC_PHOTO_ENTRY_USER_CD`) VALUES ('a1b4acf4-712d-4b05-8acd-e45964d8ccc6', '9ae42471-4842-4101-8404-5ace607b00a6', NULL, 'INITIAL', 'wound_photos/2026/10/demo_initial.jpg', 'صورة خط الأساس الأولية عند القبول', '2026-10-02 10:23:13', 'a817e223-7e7c-461c-90db-93742a8fd86d', '2759d629-76e6-4c59-bfd2-3a4aeadc2c54');
INSERT INTO `wc_wound_photo_tb` (`WC_PHOTO_CODE`, `WC_PHOTO_REG_DIAGNOSIS_CD`, `WC_PHOTO_DAILY_REVIEW_DETAIL_CD`, `WC_PHOTO_STAGE`, `WC_PHOTO_PATH`, `WC_PHOTO_CAPTION`, `WC_PHOTO_TAKEN_AT`, `WC_PHOTO_NURSE_CD`, `WC_PHOTO_ENTRY_USER_CD`) VALUES ('ba84bdac-18af-4455-a3c8-764d8f946b01', '9ae42471-4842-4101-8404-5ace607b00a6', 'c2d18a5a-e90f-4e50-96a0-dde296e153a4', 'REVIEW', 'wound_photos/2026/10/demo_review.jpg', 'صورة متابعة بعد 3 أيام من التضميد', '2026-10-05 10:23:13', 'a817e223-7e7c-461c-90db-93742a8fd86d', '2759d629-76e6-4c59-bfd2-3a4aeadc2c54');

SET FOREIGN_KEY_CHECKS = 1;
-- ============================================================================
-- End of Database Export
-- ============================================================================
