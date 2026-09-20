/**
 * ============================================================================
 * WCCMS — Wound Care Clinic Management System
 * Shared Data Layer (Schema-Aligned)
 *
 * Normalized entities matching the database ERD exactly.
 * Provides WCCMS.api.* functions for all page controllers.
 *
 * Tables:
 *   MASTER:  USERS, DEPARTMENTS, DIAGNOSES, WOUND_TYPES, DRESSINGS,
 *            MEDICATIONS, PROCEDURES, PMH_LIST
 *   CORE:   PATIENTS, REGISTRATION_REQUESTS, REGISTRATIONS,
 *            REGISTRATION_PMH, REGISTRATION_DIAGNOSES,
 *            REGISTRATION_DIAGNOSIS_PROCEDURES, DAILY_REVIEWS,
 *            SCHEDULED_REVIEWS, AUDIT_LOG
 * ============================================================================
 */

'use strict';

(function (global) {

// ════════════════════════════════════════════════════════════════════════════
// 1. MASTER / LOOKUP TABLES
// ════════════════════════════════════════════════════════════════════════════

var USERS = [
  { id: 'u001', full_name: 'أ. فاطمة الزهراني', username: 'fatimah.z', email: 'fatimah@wccms.sa', password: '', role: 'NURSE', is_active: true },
  { id: 'u002', full_name: 'د. أحمد عبد الرحمن', username: 'ahmad.a', email: 'ahmad@wccms.sa', password: '', role: 'ADMIN', is_active: true },
  { id: 'u003', full_name: 'أ. مريم السالم', username: 'mariam.s', email: 'mariam@wccms.sa', password: '', role: 'NURSE', is_active: true }
];

var DEPARTMENTS = [
  { id: 'dept001', name: 'العناية المركزة (ICU)', is_active: true },
  { id: 'dept002', name: 'العناية القلبية (CCU)', is_active: true },
  { id: 'dept003', name: 'الباطنية (Medical)', is_active: true },
  { id: 'dept004', name: 'الجراحة (Surgery)', is_active: true },
  { id: 'dept005', name: 'العظام (Orthopedics)', is_active: true },
  { id: 'dept006', name: 'الأعصاب (Neurology)', is_active: true },
  { id: 'dept007', name: 'الأورام (Oncology)', is_active: true },
  { id: 'dept008', name: 'عيادة العناية بالجروح', is_active: true }
];

var DIAGNOSES = [
  { id: 'dx001', name: 'قدم سكري (Diabetic Foot Ulcer)', is_active: true },
  { id: 'dx002', name: 'قرحة فراش (Pressure Ulcer)', is_active: true },
  { id: 'dx003', name: 'قرحة وريدية (Venous Leg Ulcer)', is_active: true },
  { id: 'dx004', name: 'جرح ما بعد الجراحة (Post-Op Wound)', is_active: true },
  { id: 'dx005', name: 'جرح موضع الإبرة الوريدية (IV Site Wound)', is_active: true },
  { id: 'dx006', name: 'جرح منفتح (Open Abdominal Wound)', is_active: true },
  { id: 'dx007', name: 'جرح كدمي (Contusion Wound)', is_active: true },
  { id: 'dx008', name: 'قرحة كاحل (Ankle Wound)', is_active: true },
  { id: 'dx009', name: 'جرح موضع القسطرة (Catheter Site Wound)', is_active: true },
  { id: 'dx010', name: 'جرح ما بعد استئصال الثدي (Post-Mastectomy Wound)', is_active: true },
  { id: 'dx011', name: 'جرح فغرة القولون (Post-Op Colostomy Wound)', is_active: true },
  { id: 'dx012', name: 'جرح تثبيت الكاحل الجراحي (Ankle Fixation Wound)', is_active: true },
  { id: 'dx013', name: 'جرح جراحي البطن (Abdominal Surgical Wound)', is_active: true },
  { id: 'dx014', name: 'جرح جراحي التهابي (Inflammatory Surgical Wound)', is_active: true }
];

var WOUND_TYPES = [
  { id: 'wt001', name: 'نخري (Necrotic)', is_active: true },
  { id: 'wt002', name: 'متساقط (Sloughy)', is_active: true },
  { id: 'wt003', name: 'نضحي (Exudative)', is_active: true },
  { id: 'wt004', name: 'جراحي (Surgical)', is_active: true },
  { id: 'wt005', name: 'إقفاري (Ischemic)', is_active: true },
  { id: 'wt006', name: 'رضي (Traumatic)', is_active: true },
  { id: 'wt007', name: 'منفتح (Open)', is_active: true },
  { id: 'wt008', name: 'مرحلة أولى (Stage I)', is_active: true },
  { id: 'wt009', name: 'مرحلة ثانية (Stage II)', is_active: true },
  { id: 'wt010', name: 'مرحلة ثالثة (Stage III)', is_active: true },
  { id: 'wt011', name: 'جراحي صغير (Minor Surgical)', is_active: true }
];

var DRESSINGS = [
  { id: 'drs001', name: 'رغوية (Foam)', is_active: true },
  { id: 'drs002', name: 'هيدروكولويد (Hydrocolloid)', is_active: true },
  { id: 'drs003', name: 'هيدروجيل (Hydrogel)', is_active: true },
  { id: 'drs004', name: 'ألجينات (Alginate)', is_active: true },
  { id: 'drs005', name: 'فضة مضادة للميكروبات (Silver)', is_active: true },
  { id: 'drs006', name: 'سيليكون (Silicone)', is_active: true },
  { id: 'drs007', name: 'ضغط سلبي (NPWT)', is_active: true },
  { id: 'drs008', name: 'شاش فازلين (Vaseline Gauze)', is_active: true }
];

var MEDICATIONS = [
  { id: 'med001', name: 'مرهم مضاد حيوي (Antibiotic Ointment)', is_active: true },
  { id: 'med002', name: 'غسول ملحي (Normal Saline)', is_active: true },
  { id: 'med003', name: 'بيتادين (Betadine)', is_active: true },
  { id: 'med004', name: 'عسل طبي (Manuka Honey)', is_active: true },
  { id: 'med005', name: 'كولاجين (Collagen)', is_active: true }
];

var PROCEDURES = [
  { id: 'proc001', name: 'تنضير الجرح (Debridement)', is_active: true },
  { id: 'proc002', name: 'تقييم الجرح (Wound Assessment)', is_active: true },
  { id: 'proc003', name: 'تغيير الضمادة (Dressing Change)', is_active: true },
  { id: 'proc004', name: 'مسحة مزرعة (Swab Culture)', is_active: true },
  { id: 'proc005', name: 'تطبيق الضغط السلبي (NPWT Application)', is_active: true },
  { id: 'proc006', name: 'تنظيف وتطهير الجرح (Wound Irrigation & Cleansing)', is_active: true },
  { id: 'proc007', name: 'قياس أبعاد وعمق الجرح (Wound Measurement & Staging)', is_active: true },
  { id: 'proc008', name: 'تطبيق ضمادة الكولاجين (Collagen Application)', is_active: true },
  { id: 'proc009', name: 'علاج بالضغط الضاغط (Compression Therapy)', is_active: true },
  { id: 'proc010', name: 'إزالة أنسجة ميتة حادة (Sharp Debridement)', is_active: true },
  { id: 'proc011', name: 'كي الأنسجة الزائدة (Cauterization of Hypergranulation)', is_active: true },
  { id: 'proc012', name: 'تطبيق بدائل الجلد الأحيائية (Skin Substitute Application)', is_active: true },
  { id: 'proc013', name: 'فحص التروية والتأكسج الموضعي (Vascular & Perfusion Assessment)', is_active: true }
];

var PMH_LIST = [
  { id: 'pmh001', name: 'السكري (Diabetes Mellitus)', is_active: true },
  { id: 'pmh002', name: 'ضغط الدم (Hypertension)', is_active: true },
  { id: 'pmh003', name: 'الفشل الكلوي (Renal Failure)', is_active: true },
  { id: 'pmh004', name: 'أمراض القلب (Cardiac Disease)', is_active: true },
  { id: 'pmh005', name: 'السكتة الدماغية (Stroke)', is_active: true },
  { id: 'pmh006', name: 'البدانة (Obesity)', is_active: true },
  { id: 'pmh007', name: 'السرطان (Oncology Patient)', is_active: true },
  { id: 'pmh008', name: 'كيماوي (Chemotherapy)', is_active: true },
  { id: 'pmh009', name: 'كسر الورك (Hip Fracture)', is_active: true },
  { id: 'pmh010', name: 'كسر الكاحل (Ankle Fracture)', is_active: true },
  { id: 'pmh011', name: 'احتشاء عضلة القلب (MI)', is_active: true },
  { id: 'pmh012', name: 'سرطان القولون (Colon Cancer)', is_active: true },
  { id: 'pmh013', name: 'الربو (Asthma)', is_active: true }
];


// ════════════════════════════════════════════════════════════════════════════
// 2. HOSPITAL SYSTEM SIMULATION (External — not in our DB)
// ════════════════════════════════════════════════════════════════════════════

var HOSPITAL_PATIENTS = [
  { hospital_patient_id: 'HP-10025', national_id: '1087654321', full_name: 'أحمد محمد علي', gender: 'MALE', age: 52, supervising_doctor: 'د. محمد خالد', hospital_department: 'العناية المركزة (ICU)', hospital_admission_date: '2026-08-31' },
  { hospital_patient_id: 'HP-10031', national_id: '1076543210', full_name: 'فاطمة سعد الغامدي', gender: 'FEMALE', age: 67, supervising_doctor: 'د. سارة العتيبي', hospital_department: 'الباطنية (Medical)', hospital_admission_date: '2026-09-01' },
  { hospital_patient_id: 'HP-10044', national_id: '1065432109', full_name: 'خالد عبدالله الشمري', gender: 'MALE', age: 44, supervising_doctor: 'د. أحمد الشريف', hospital_department: 'الجراحة (Surgery)', hospital_admission_date: '2026-09-01' },
  { hospital_patient_id: 'HP-10058', national_id: '1054321098', full_name: 'نورة محمد العتيبي', gender: 'FEMALE', age: 38, supervising_doctor: 'د. نورة الحربي', hospital_department: 'الأورام (Oncology)', hospital_admission_date: '2026-09-05' },
  { hospital_patient_id: 'HP-10067', national_id: '1043210987', full_name: 'عبدالرحمن يوسف القحطاني', gender: 'MALE', age: 71, supervising_doctor: 'د. عبدالله المالكي', hospital_department: 'العناية القلبية (CCU)', hospital_admission_date: '2026-08-29' },
  { hospital_patient_id: 'HP-10079', national_id: '1032109876', full_name: 'منى علي الزهراني', gender: 'FEMALE', age: 55, supervising_doctor: 'د. محمد خالد', hospital_department: 'الأعصاب (Neurology)', hospital_admission_date: '2026-09-02' },
  { hospital_patient_id: 'HP-10085', national_id: '1021098765', full_name: 'سلطان عمر الدوسري', gender: 'MALE', age: 60, supervising_doctor: 'د. سارة العتيبي', hospital_department: 'العظام (Orthopedics)', hospital_admission_date: '2026-09-03' },
  { hospital_patient_id: 'HP-10091', national_id: '1010987654', full_name: 'حنان إبراهيم الحربي', gender: 'FEMALE', age: 49, supervising_doctor: 'د. أحمد الشريف', hospital_department: 'الجراحة (Surgery)', hospital_admission_date: '2026-08-28' },
  { hospital_patient_id: 'HP-10097', national_id: '1009876543', full_name: 'بدر محمد الشهري', gender: 'MALE', age: 33, supervising_doctor: 'د. نورة الحربي', hospital_department: 'الباطنية (Medical)', hospital_admission_date: '2026-09-05' },
  { hospital_patient_id: 'HP-10103', national_id: '1098765432', full_name: 'رنا خالد السبيعي', gender: 'FEMALE', age: 41, supervising_doctor: 'د. عبدالله المالكي', hospital_department: 'العناية المركزة (ICU)', hospital_admission_date: '2026-08-30' },
  { hospital_patient_id: 'HP-10112', national_id: '1087654300', full_name: 'ماجد سعيد البقمي', gender: 'MALE', age: 63, supervising_doctor: 'د. محمد خالد', hospital_department: 'العناية القلبية (CCU)', hospital_admission_date: '2026-08-27' },
  { hospital_patient_id: 'HP-10119', national_id: '1076543198', full_name: 'سهيلة عمر الجهني', gender: 'FEMALE', age: 57, supervising_doctor: 'د. سارة العتيبي', hospital_department: 'الأورام (Oncology)', hospital_admission_date: '2026-09-02' },
  { hospital_patient_id: 'HP-10125', national_id: '1055443322', full_name: 'محمد عبدالكريم العنزي', gender: 'MALE', age: 62, supervising_doctor: 'د. محمد خالد', hospital_department: 'العناية المركزة (ICU)', hospital_admission_date: '2026-09-01' },
  { hospital_patient_id: 'HP-10130', national_id: '1044332211', full_name: 'ريم خالد الشريف', gender: 'FEMALE', age: 51, supervising_doctor: 'د. سارة العتيبي', hospital_department: 'الباطنية (Medical)', hospital_admission_date: '2026-08-30' },
  { hospital_patient_id: 'HP-10135', national_id: '1033221100', full_name: 'تركي منصور العتيبي', gender: 'MALE', age: 39, supervising_doctor: 'د. أحمد الشريف', hospital_department: 'الجراحة (Surgery)', hospital_admission_date: '2026-09-02' },
  { hospital_patient_id: 'HP-10140', national_id: '1022110099', full_name: 'عائشة صالح الغامدي', gender: 'FEMALE', age: 70, supervising_doctor: 'د. عبدالله المالكي', hospital_department: 'الأعصاب (Neurology)', hospital_admission_date: '2026-09-01' },
  { hospital_patient_id: 'HP-10145', national_id: '1011009988', full_name: 'فيصل سعد المطيري', gender: 'MALE', age: 42, supervising_doctor: 'د. نورة الحربي', hospital_department: 'العظام (Orthopedics)', hospital_admission_date: '2026-09-03' },
  { hospital_patient_id: 'HP-10999', national_id: '1099887766', full_name: 'علي حسن الزهراني', gender: 'MALE', age: 58, supervising_doctor: 'د. أحمد الشريف', hospital_department: 'الجراحة (Surgery)', hospital_admission_date: '2026-04-10' },
  // Extra patients not yet registered in wound care
  { hospital_patient_id: 'HP-20001', national_id: '987654321', full_name: 'سعود خالد المنصور', gender: 'MALE', age: 45, supervising_doctor: 'د. محمد خالد', hospital_department: 'العناية المركزة (ICU)', hospital_admission_date: '2026-09-04' },
  { hospital_patient_id: 'HP-20002', national_id: '123456789', full_name: 'هند عبدالعزيز الراشد', gender: 'FEMALE', age: 36, supervising_doctor: 'د. سارة العتيبي', hospital_department: 'الأورام (Oncology)', hospital_admission_date: '2026-09-03' },
  { hospital_patient_id: 'HP-20003', national_id: '111222333', full_name: 'ياسر محمد الحارثي', gender: 'MALE', age: 55, supervising_doctor: 'د. أحمد الشريف', hospital_department: 'الجراحة (Surgery)', hospital_admission_date: '2026-09-02' },
  { hospital_patient_id: 'HP-20004', national_id: '444555666', full_name: 'لينا أحمد العمري', gender: 'FEMALE', age: 29, supervising_doctor: 'د. نورة الحربي', hospital_department: 'العظام (Orthopedics)', hospital_admission_date: '2026-09-05' }
];


// ════════════════════════════════════════════════════════════════════════════
// 3. PATIENTS (in our system — synced from hospital)
// ════════════════════════════════════════════════════════════════════════════

var PATIENTS = [
  { id: 'pat001', hospital_patient_id: 'HP-10025', national_id: '1087654321', full_name: 'أحمد محمد علي', gender: 'MALE', age: 52, supervising_doctor: 'د. محمد خالد', hospital_department: 'العناية المركزة (ICU)', hospital_admission_date: '2026-08-31', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat002', hospital_patient_id: 'HP-10031', national_id: '1076543210', full_name: 'فاطمة سعد الغامدي', gender: 'FEMALE', age: 67, supervising_doctor: 'د. سارة العتيبي', hospital_department: 'الباطنية (Medical)', hospital_admission_date: '2026-09-01', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat003', hospital_patient_id: 'HP-10044', national_id: '1065432109', full_name: 'خالد عبدالله الشمري', gender: 'MALE', age: 44, supervising_doctor: 'د. أحمد الشريف', hospital_department: 'الجراحة (Surgery)', hospital_admission_date: '2026-09-01', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat004', hospital_patient_id: 'HP-10058', national_id: '1054321098', full_name: 'نورة محمد العتيبي', gender: 'FEMALE', age: 38, supervising_doctor: 'د. نورة الحربي', hospital_department: 'الأورام (Oncology)', hospital_admission_date: '2026-09-05', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat005', hospital_patient_id: 'HP-10067', national_id: '1043210987', full_name: 'عبدالرحمن يوسف القحطاني', gender: 'MALE', age: 71, supervising_doctor: 'د. عبدالله المالكي', hospital_department: 'العناية القلبية (CCU)', hospital_admission_date: '2026-08-29', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat006', hospital_patient_id: 'HP-10079', national_id: '1032109876', full_name: 'منى علي الزهراني', gender: 'FEMALE', age: 55, supervising_doctor: 'د. محمد خالد', hospital_department: 'الأعصاب (Neurology)', hospital_admission_date: '2026-09-02', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat007', hospital_patient_id: 'HP-10085', national_id: '1021098765', full_name: 'سلطان عمر الدوسري', gender: 'MALE', age: 60, supervising_doctor: 'د. سارة العتيبي', hospital_department: 'العظام (Orthopedics)', hospital_admission_date: '2026-09-03', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat008', hospital_patient_id: 'HP-10091', national_id: '1010987654', full_name: 'حنان إبراهيم الحربي', gender: 'FEMALE', age: 49, supervising_doctor: 'د. أحمد الشريف', hospital_department: 'الجراحة (Surgery)', hospital_admission_date: '2026-08-28', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat009', hospital_patient_id: 'HP-10097', national_id: '1009876543', full_name: 'بدر محمد الشهري', gender: 'MALE', age: 33, supervising_doctor: 'د. نورة الحربي', hospital_department: 'الباطنية (Medical)', hospital_admission_date: '2026-09-05', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat010', hospital_patient_id: 'HP-10103', national_id: '1098765432', full_name: 'رنا خالد السبيعي', gender: 'FEMALE', age: 41, supervising_doctor: 'د. عبدالله المالكي', hospital_department: 'العناية المركزة (ICU)', hospital_admission_date: '2026-08-30', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat011', hospital_patient_id: 'HP-10112', national_id: '1087654300', full_name: 'ماجد سعيد البقمي', gender: 'MALE', age: 63, supervising_doctor: 'د. محمد خالد', hospital_department: 'العناية القلبية (CCU)', hospital_admission_date: '2026-08-27', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat012', hospital_patient_id: 'HP-10119', national_id: '1076543198', full_name: 'سهيلة عمر الجهني', gender: 'FEMALE', age: 57, supervising_doctor: 'د. سارة العتيبي', hospital_department: 'الأورام (Oncology)', hospital_admission_date: '2026-09-02', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat013', hospital_patient_id: 'HP-10125', national_id: '1055443322', full_name: 'محمد عبدالكريم العنزي', gender: 'MALE', age: 62, supervising_doctor: 'د. محمد خالد', hospital_department: 'العناية المركزة (ICU)', hospital_admission_date: '2026-09-01', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat014', hospital_patient_id: 'HP-10130', national_id: '1044332211', full_name: 'ريم خالد الشريف', gender: 'FEMALE', age: 51, supervising_doctor: 'د. سارة العتيبي', hospital_department: 'الباطنية (Medical)', hospital_admission_date: '2026-08-30', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat015', hospital_patient_id: 'HP-10135', national_id: '1033221100', full_name: 'تركي منصور العتيبي', gender: 'MALE', age: 39, supervising_doctor: 'د. أحمد الشريف', hospital_department: 'الجراحة (Surgery)', hospital_admission_date: '2026-09-02', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat016', hospital_patient_id: 'HP-10140', national_id: '1022110099', full_name: 'عائشة صالح الغامدي', gender: 'FEMALE', age: 70, supervising_doctor: 'د. عبدالله المالكي', hospital_department: 'الأعصاب (Neurology)', hospital_admission_date: '2026-09-01', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat017', hospital_patient_id: 'HP-10145', national_id: '1011009988', full_name: 'فيصل سعد المطيري', gender: 'MALE', age: 42, supervising_doctor: 'د. نورة الحربي', hospital_department: 'العظام (Orthopedics)', hospital_admission_date: '2026-09-03', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat099', hospital_patient_id: 'HP-10999', national_id: '1099887766', full_name: 'علي حسن الزهراني', gender: 'MALE', age: 58, supervising_doctor: 'د. أحمد الشريف', hospital_department: 'الجراحة (Surgery)', hospital_admission_date: '2026-04-10', last_synced_at: '2026-05-10T08:00:00Z' }
];


// ════════════════════════════════════════════════════════════════════════════
// 4. REGISTRATION REQUESTS
// ════════════════════════════════════════════════════════════════════════════

var _today = (function () { var d = new Date(); return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0'); })();

var REGISTRATION_REQUESTS = [
  // APPROVED — linked to existing registrations
  { id: 'rreq001', patient_id: 'pat001', submitted_by_id: 'u001', reviewed_by_id: 'u002', requested_at: '2026-08-31T08:00:00Z', reviewed_at: '2026-08-31T08:30:00Z', status: 'APPROVED', rejection_reason: null, notes: 'مريض العناية المركزة بحاجة لعلاج جروح عاجل', hospital_patient_id: 'HP-10025', national_id: '1087654321', patient_full_name: 'أحمد محمد علي', gender: 'MALE', patient_age: 52, supervising_doctor: 'د. محمد خالد', hospital_department: 'العناية المركزة (ICU)', hospital_admission_date: '2026-08-31', hospital_data_fetched_at: '2026-08-31T07:55:00Z' },
  { id: 'rreq002', patient_id: 'pat002', submitted_by_id: 'u001', reviewed_by_id: 'u002', requested_at: '2026-09-01T09:00:00Z', reviewed_at: '2026-09-01T09:20:00Z', status: 'APPROVED', rejection_reason: null, notes: '', hospital_patient_id: 'HP-10031', national_id: '1076543210', patient_full_name: 'فاطمة سعد الغامدي', gender: 'FEMALE', patient_age: 67, supervising_doctor: 'د. سارة العتيبي', hospital_department: 'الباطنية (Medical)', hospital_admission_date: '2026-09-01', hospital_data_fetched_at: '2026-09-01T08:55:00Z' },
  { id: 'rreq003', patient_id: 'pat003', submitted_by_id: 'u001', reviewed_by_id: 'u002', requested_at: '2026-09-01T10:00:00Z', reviewed_at: '2026-09-01T10:15:00Z', status: 'APPROVED', rejection_reason: null, notes: 'جرح ما بعد عملية جراحية', hospital_patient_id: 'HP-10044', national_id: '1065432109', patient_full_name: 'خالد عبدالله الشمري', gender: 'MALE', patient_age: 44, supervising_doctor: 'د. أحمد الشريف', hospital_department: 'الجراحة (Surgery)', hospital_admission_date: '2026-09-01', hospital_data_fetched_at: '2026-09-01T09:55:00Z' },
  { id: 'rreq004', patient_id: 'pat004', submitted_by_id: 'u001', reviewed_by_id: 'u002', requested_at: _today + 'T07:00:00Z', reviewed_at: _today + 'T07:30:00Z', status: 'APPROVED', rejection_reason: null, notes: '', hospital_patient_id: 'HP-10058', national_id: '1054321098', patient_full_name: 'نورة محمد العتيبي', gender: 'FEMALE', patient_age: 38, supervising_doctor: 'د. نورة الحربي', hospital_department: 'الأورام (Oncology)', hospital_admission_date: '2026-09-05', hospital_data_fetched_at: _today + 'T06:55:00Z' },
  { id: 'rreq005', patient_id: 'pat005', submitted_by_id: 'u001', reviewed_by_id: 'u002', requested_at: '2026-08-29T08:00:00Z', reviewed_at: '2026-08-29T08:15:00Z', status: 'APPROVED', rejection_reason: null, notes: 'قدم سكري مع ضعف تروية', hospital_patient_id: 'HP-10067', national_id: '1043210987', patient_full_name: 'عبدالرحمن يوسف القحطاني', gender: 'MALE', patient_age: 71, supervising_doctor: 'د. عبدالله المالكي', hospital_department: 'العناية القلبية (CCU)', hospital_admission_date: '2026-08-29', hospital_data_fetched_at: '2026-08-29T07:55:00Z' },

  // PENDING — awaiting approval
  { id: 'rreq100', patient_id: null, submitted_by_id: 'u001', reviewed_by_id: null, requested_at: _today + 'T10:00:00Z', reviewed_at: null, status: 'PENDING', rejection_reason: null, notes: 'مريض جديد — جرح حروق', hospital_patient_id: 'HP-20001', national_id: '987654321', patient_full_name: 'سعود خالد المنصور', gender: 'MALE', patient_age: 45, supervising_doctor: 'د. محمد خالد', hospital_department: 'العناية المركزة (ICU)', hospital_admission_date: '2026-09-04', hospital_data_fetched_at: _today + 'T09:55:00Z' },
  { id: 'rreq101', patient_id: null, submitted_by_id: 'u003', reviewed_by_id: null, requested_at: _today + 'T11:30:00Z', reviewed_at: null, status: 'PENDING', rejection_reason: null, notes: 'إحالة من قسم الأورام', hospital_patient_id: 'HP-20002', national_id: '123456789', patient_full_name: 'هند عبدالعزيز الراشد', gender: 'FEMALE', patient_age: 36, supervising_doctor: 'د. سارة العتيبي', hospital_department: 'الأورام (Oncology)', hospital_admission_date: '2026-09-03', hospital_data_fetched_at: _today + 'T11:25:00Z' },
  { id: 'rreq102', patient_id: null, submitted_by_id: 'u001', reviewed_by_id: null, requested_at: _today + 'T12:00:00Z', reviewed_at: null, status: 'PENDING', rejection_reason: null, notes: '', hospital_patient_id: 'HP-20003', national_id: '111222333', patient_full_name: 'ياسر محمد الحارثي', gender: 'MALE', patient_age: 55, supervising_doctor: 'د. أحمد الشريف', hospital_department: 'الجراحة (Surgery)', hospital_admission_date: '2026-09-02', hospital_data_fetched_at: _today + 'T11:55:00Z' },

  // REJECTED
  { id: 'rreq200', patient_id: null, submitted_by_id: 'u003', reviewed_by_id: 'u002', requested_at: '2026-09-03T09:00:00Z', reviewed_at: '2026-09-03T14:00:00Z', status: 'REJECTED', rejection_reason: 'المريض ليس لديه جروح تستدعي رعاية متخصصة — يُرجى إعادة التقييم.', notes: 'طلب تحويل للعناية', hospital_patient_id: 'HP-20004', national_id: '444555666', patient_full_name: 'لينا أحمد العمري', gender: 'FEMALE', patient_age: 29, supervising_doctor: 'د. نورة الحربي', hospital_department: 'العظام (Orthopedics)', hospital_admission_date: '2026-09-05', hospital_data_fetched_at: '2026-09-03T08:55:00Z' },

  // CANCELLED
  { id: 'rreq300', patient_id: null, submitted_by_id: 'u001', reviewed_by_id: null, requested_at: '2026-09-02T08:00:00Z', reviewed_at: null, status: 'CANCELLED', rejection_reason: null, notes: 'تم إلغاء الطلب — المريض غادر المستشفى', hospital_patient_id: 'HP-20003', national_id: '111222333', patient_full_name: 'ياسر محمد الحارثي', gender: 'MALE', patient_age: 55, supervising_doctor: 'د. أحمد الشريف', hospital_department: 'الجراحة (Surgery)', hospital_admission_date: '2026-09-02', hospital_data_fetched_at: '2026-09-02T07:55:00Z' }
];


// ════════════════════════════════════════════════════════════════════════════
// 5. REGISTRATIONS
// ════════════════════════════════════════════════════════════════════════════

var REGISTRATIONS = [
  { id: 'reg001', patient_id: 'pat001', registration_request_id: 'rreq001', registering_nurse_id: 'u001', department_id: 'dept001', registration_date: '2026-08-31', status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  { id: 'reg002', patient_id: 'pat002', registration_request_id: 'rreq002', registering_nurse_id: 'u001', department_id: 'dept003', registration_date: '2026-09-01', status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  { id: 'reg003', patient_id: 'pat003', registration_request_id: 'rreq003', registering_nurse_id: 'u001', department_id: 'dept004', registration_date: '2026-09-01', status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  { id: 'reg004', patient_id: 'pat004', registration_request_id: 'rreq004', registering_nurse_id: 'u001', department_id: 'dept007', registration_date: _today, status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  { id: 'reg005', patient_id: 'pat005', registration_request_id: 'rreq005', registering_nurse_id: 'u001', department_id: 'dept002', registration_date: '2026-08-29', status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  { id: 'reg006', patient_id: 'pat006', registration_request_id: null, registering_nurse_id: 'u001', department_id: 'dept006', registration_date: '2026-09-02', status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  { id: 'reg007', patient_id: 'pat007', registration_request_id: null, registering_nurse_id: 'u001', department_id: 'dept005', registration_date: '2026-09-03', status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  { id: 'reg008', patient_id: 'pat008', registration_request_id: null, registering_nurse_id: 'u001', department_id: 'dept004', registration_date: '2026-08-28', status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  { id: 'reg009', patient_id: 'pat009', registration_request_id: null, registering_nurse_id: 'u001', department_id: 'dept003', registration_date: _today, status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  { id: 'reg010', patient_id: 'pat010', registration_request_id: null, registering_nurse_id: 'u001', department_id: 'dept001', registration_date: '2026-08-30', status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  { id: 'reg011', patient_id: 'pat011', registration_request_id: null, registering_nurse_id: 'u001', department_id: 'dept002', registration_date: '2026-08-27', status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  { id: 'reg012', patient_id: 'pat012', registration_request_id: null, registering_nurse_id: 'u001', department_id: 'dept007', registration_date: '2026-09-02', status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  { id: 'reg013', patient_id: 'pat013', registration_request_id: null, registering_nurse_id: 'u001', department_id: 'dept001', registration_date: '2026-09-01', status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  { id: 'reg014', patient_id: 'pat014', registration_request_id: null, registering_nurse_id: 'u001', department_id: 'dept003', registration_date: '2026-08-30', status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  { id: 'reg015', patient_id: 'pat015', registration_request_id: null, registering_nurse_id: 'u001', department_id: 'dept004', registration_date: '2026-09-02', status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  { id: 'reg016', patient_id: 'pat016', registration_request_id: null, registering_nurse_id: 'u001', department_id: 'dept006', registration_date: '2026-09-01', status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  { id: 'reg017', patient_id: 'pat017', registration_request_id: null, registering_nurse_id: 'u001', department_id: 'dept005', registration_date: '2026-09-03', status: 'ACTIVE', discharge_date: null, discharge_type: null, discharged_by_id: null },
  // Discharged / historical
  { id: 'reg099', patient_id: 'pat099', registration_request_id: null, registering_nurse_id: 'u003', department_id: 'dept004', registration_date: '2026-04-10', status: 'DISCHARGED', discharge_date: '2026-05-10', discharge_type: 'MANUAL', discharged_by_id: 'u001' },
  // Historical registration for pat001 (previous episode)
  { id: 'reg001old', patient_id: 'pat001', registration_request_id: null, registering_nurse_id: 'u003', department_id: 'dept001', registration_date: '2025-11-12', status: 'DISCHARGED', discharge_date: '2025-12-05', discharge_type: 'MANUAL', discharged_by_id: 'u003' },
  // Historical registration for pat005
  { id: 'reg005old', patient_id: 'pat005', registration_request_id: null, registering_nurse_id: 'u003', department_id: 'dept002', registration_date: '2026-03-10', status: 'DISCHARGED', discharge_date: '2026-04-02', discharge_type: 'MANUAL', discharged_by_id: 'u003' },
  // Historical registration for pat013
  { id: 'reg013old', patient_id: 'pat013', registration_request_id: null, registering_nurse_id: 'u003', department_id: 'dept001', registration_date: '2025-08-15', status: 'DISCHARGED', discharge_date: '2025-09-10', discharge_type: 'MANUAL', discharged_by_id: 'u003' }
];


// ════════════════════════════════════════════════════════════════════════════
// 6. REGISTRATION_PMH (Past Medical History per registration)
// ════════════════════════════════════════════════════════════════════════════

var REGISTRATION_PMH = [
  // reg001 — السكري, ضغط الدم
  { id: 'rpmh001', registration_id: 'reg001', pmh_id: 'pmh001' },
  { id: 'rpmh002', registration_id: 'reg001', pmh_id: 'pmh002' },
  // reg002 — السكري, الفشل الكلوي
  { id: 'rpmh003', registration_id: 'reg002', pmh_id: 'pmh001' },
  { id: 'rpmh004', registration_id: 'reg002', pmh_id: 'pmh003' },
  // reg003 — السكري
  { id: 'rpmh005', registration_id: 'reg003', pmh_id: 'pmh001' },
  // reg004 — السرطان, كيماوي
  { id: 'rpmh006', registration_id: 'reg004', pmh_id: 'pmh007' },
  { id: 'rpmh007', registration_id: 'reg004', pmh_id: 'pmh008' },
  // reg005 — أمراض القلب, ضغط الدم, السكري
  { id: 'rpmh008', registration_id: 'reg005', pmh_id: 'pmh004' },
  { id: 'rpmh009', registration_id: 'reg005', pmh_id: 'pmh002' },
  { id: 'rpmh010', registration_id: 'reg005', pmh_id: 'pmh001' },
  // reg006 — السكتة الدماغية, ضغط الدم
  { id: 'rpmh011', registration_id: 'reg006', pmh_id: 'pmh005' },
  { id: 'rpmh012', registration_id: 'reg006', pmh_id: 'pmh002' },
  // reg007 — كسر الورك
  { id: 'rpmh013', registration_id: 'reg007', pmh_id: 'pmh009' },
  // reg008 — البدانة, السكري
  { id: 'rpmh014', registration_id: 'reg008', pmh_id: 'pmh006' },
  { id: 'rpmh015', registration_id: 'reg008', pmh_id: 'pmh001' },
  // reg009 — السكري
  { id: 'rpmh016', registration_id: 'reg009', pmh_id: 'pmh001' },
  // reg010 — السكري, كلوي, ضغط الدم
  { id: 'rpmh017', registration_id: 'reg010', pmh_id: 'pmh001' },
  { id: 'rpmh018', registration_id: 'reg010', pmh_id: 'pmh003' },
  { id: 'rpmh019', registration_id: 'reg010', pmh_id: 'pmh002' },
  // reg011 — احتشاء, ضغط الدم
  { id: 'rpmh020', registration_id: 'reg011', pmh_id: 'pmh011' },
  { id: 'rpmh021', registration_id: 'reg011', pmh_id: 'pmh002' },
  // reg012 — سرطان القولون, كيماوي
  { id: 'rpmh022', registration_id: 'reg012', pmh_id: 'pmh012' },
  { id: 'rpmh023', registration_id: 'reg012', pmh_id: 'pmh008' },
  // reg013 — السكري, الفشل الكلوي
  { id: 'rpmh024', registration_id: 'reg013', pmh_id: 'pmh001' },
  { id: 'rpmh025', registration_id: 'reg013', pmh_id: 'pmh003' },
  // reg014 — الربو, ضغط الدم
  { id: 'rpmh026', registration_id: 'reg014', pmh_id: 'pmh013' },
  { id: 'rpmh027', registration_id: 'reg014', pmh_id: 'pmh002' },
  // reg016 — السكتة الدماغية, ضغط الدم
  { id: 'rpmh028', registration_id: 'reg016', pmh_id: 'pmh005' },
  { id: 'rpmh029', registration_id: 'reg016', pmh_id: 'pmh002' },
  // reg017 — كسر الكاحل
  { id: 'rpmh030', registration_id: 'reg017', pmh_id: 'pmh010' },
  // reg099 — السكري
  { id: 'rpmh031', registration_id: 'reg099', pmh_id: 'pmh001' }
];


// ════════════════════════════════════════════════════════════════════════════
// 7. REGISTRATION_DIAGNOSES
// ════════════════════════════════════════════════════════════════════════════

var REGISTRATION_DIAGNOSES = [
  // reg001 — 2 diagnoses
  { id: 'rdx001', registration_id: 'reg001', diagnosis_id: 'dx001', description: 'جرح عميق في الكعب الأيسر مع وجود إفرازات قيحية وتأثر بالأنسجة اللينة. أبعاد الجرح 3×2 سم.', wound_type_id: 'wt001', initial_dressing_id: 'drs001', initial_medication_id: 'med001' },
  { id: 'rdx002', registration_id: 'reg001', diagnosis_id: 'dx002', description: 'قرحة فراش في منطقة العجز — المرحلة الثانية مع وجود أنسجة صفراء وإفرازات طفيفة.', wound_type_id: 'wt002', initial_dressing_id: 'drs003', initial_medication_id: 'med003' },
  // reg002 — 1 diagnosis
  { id: 'rdx003', registration_id: 'reg002', diagnosis_id: 'dx003', description: 'قرحة وريدية في الساق اليمنى مع إفرازات غزيرة. المنطقة المحيطة متهيجة.', wound_type_id: 'wt003', initial_dressing_id: 'drs004', initial_medication_id: 'med002' },
  // reg003 — 3 diagnoses
  { id: 'rdx004', registration_id: 'reg003', diagnosis_id: 'dx004', description: 'جرح ما بعد عملية استئصال الزائدة الدودية — نظيف مع خيوط في مكانها.', wound_type_id: 'wt004', initial_dressing_id: 'drs006', initial_medication_id: 'med002' },
  { id: 'rdx005', registration_id: 'reg003', diagnosis_id: 'dx001', description: 'جرح صغير في إصبع القدم الكبير مع احمرار محيطي.', wound_type_id: 'wt005', initial_dressing_id: 'drs005', initial_medication_id: 'med001' },
  { id: 'rdx006', registration_id: 'reg003', diagnosis_id: 'dx002', description: 'قرحة المرحلة الأولى في منطقة العصعص — احمرار بدون تمزق جلدي.', wound_type_id: 'wt002', initial_dressing_id: 'drs002', initial_medication_id: 'med003' },
  // reg004 — 1 diagnosis
  { id: 'rdx007', registration_id: 'reg004', diagnosis_id: 'dx010', description: 'جرح ما بعد عملية استئصال الثدي — بداية الشفاء. لا عفونة واضحة.', wound_type_id: 'wt004', initial_dressing_id: 'drs001', initial_medication_id: 'med002' },
  // reg005 — 2 diagnoses
  { id: 'rdx008', registration_id: 'reg005', diagnosis_id: 'dx001', description: 'جرح في القدم اليسرى مع ضعف في التروية الدموية. المنطقة باردة.', wound_type_id: 'wt005', initial_dressing_id: 'drs005', initial_medication_id: 'med001' },
  { id: 'rdx009', registration_id: 'reg005', diagnosis_id: 'dx005', description: 'ضرر جلدي في موضع الإبرة الوريدية في الذراع الأيمن.', wound_type_id: 'wt003', initial_dressing_id: 'drs002', initial_medication_id: 'med002' },
  // reg006 — 1 diagnosis
  { id: 'rdx010', registration_id: 'reg006', diagnosis_id: 'dx002', description: 'قرحة فراش في منطقة الكتف الأيسر بسبب الشلل الناجم عن السكتة الدماغية.', wound_type_id: 'wt009', initial_dressing_id: 'drs002', initial_medication_id: 'med002' },
  // reg007 — 2 diagnoses
  { id: 'rdx011', registration_id: 'reg007', diagnosis_id: 'dx004', description: 'جرح الورك بعد العملية — التئام تدريجي.', wound_type_id: 'wt004', initial_dressing_id: 'drs006', initial_medication_id: 'med002' },
  { id: 'rdx012', registration_id: 'reg007', diagnosis_id: 'dx002', description: 'احمرار في منطقة الكعب — مرحلة أولى.', wound_type_id: 'wt008', initial_dressing_id: 'drs001', initial_medication_id: 'med003' },
  // reg008 — 1 diagnosis
  { id: 'rdx013', registration_id: 'reg008', diagnosis_id: 'dx006', description: 'جرح بطني منفتح بعد عملية جراحية — يتطلب العناية بالضغط السلبي.', wound_type_id: 'wt007', initial_dressing_id: 'drs007', initial_medication_id: 'med001' },
  // reg009 — 2 diagnoses
  { id: 'rdx014', registration_id: 'reg009', diagnosis_id: 'dx001', description: 'بداية نخر في إصبع القدم الصغير.', wound_type_id: 'wt001', initial_dressing_id: 'drs001', initial_medication_id: 'med001' },
  { id: 'rdx015', registration_id: 'reg009', diagnosis_id: 'dx007', description: 'كدمة جلدية في الساق اليسرى من حادث.', wound_type_id: 'wt006', initial_dressing_id: 'drs003', initial_medication_id: 'med002' },
  // reg010 — 3 diagnoses
  { id: 'rdx016', registration_id: 'reg010', diagnosis_id: 'dx001', description: 'نخر في منطقة الكعب الأيمن مع احمرار في المحيط.', wound_type_id: 'wt001', initial_dressing_id: 'drs005', initial_medication_id: 'med001' },
  { id: 'rdx017', registration_id: 'reg010', diagnosis_id: 'dx002', description: 'قرحة عميقة في منطقة العجز مع تآكل نسيجي.', wound_type_id: 'wt010', initial_dressing_id: 'drs003', initial_medication_id: 'med003' },
  { id: 'rdx018', registration_id: 'reg010', diagnosis_id: 'dx008', description: 'قرحة إقفارية في الكاحل الأيسر.', wound_type_id: 'wt005', initial_dressing_id: 'drs004', initial_medication_id: 'med002' },
  // reg011 — 1 diagnosis
  { id: 'rdx019', registration_id: 'reg011', diagnosis_id: 'dx009', description: 'جرح ما بعد إزالة قسطرة القلب — التئام جيد.', wound_type_id: 'wt011', initial_dressing_id: 'drs002', initial_medication_id: 'med002' },
  // reg012 — 1 diagnosis
  { id: 'rdx020', registration_id: 'reg012', diagnosis_id: 'dx011', description: 'جرح فغرة القولون — مستقر ونظيف.', wound_type_id: 'wt004', initial_dressing_id: 'drs001', initial_medication_id: 'med002' },
  // reg013 — 2 diagnoses
  { id: 'rdx021', registration_id: 'reg013', diagnosis_id: 'dx001', description: 'جرح نخري عميق في قدم الأصبع الأكبر مع وجود إفرازات قيحية.', wound_type_id: 'wt001', initial_dressing_id: 'drs005', initial_medication_id: 'med001' },
  { id: 'rdx022', registration_id: 'reg013', diagnosis_id: 'dx002', description: 'قرحة فراش عميقة في منطقة العجز مع وجود أنسجة متساقطة.', wound_type_id: 'wt010', initial_dressing_id: 'drs003', initial_medication_id: 'med003' },
  // reg014 — 1 diagnosis
  { id: 'rdx023', registration_id: 'reg014', diagnosis_id: 'dx003', description: 'قرحة وريدية نضحية في الساق اليسرى مع إفرازات متوسطة.', wound_type_id: 'wt003', initial_dressing_id: 'drs004', initial_medication_id: 'med002' },
  // reg015 — 1 diagnosis
  { id: 'rdx024', registration_id: 'reg015', diagnosis_id: 'dx013', description: 'جرح ما بعد جراحة استئصال المرارة — التئام جيد بدون التهاب.', wound_type_id: 'wt004', initial_dressing_id: 'drs006', initial_medication_id: 'med002' },
  // reg016 — 2 diagnoses
  { id: 'rdx025', registration_id: 'reg016', diagnosis_id: 'dx002', description: 'قرحة مرحلة ثانية في الكعب الأيمن بسبب استلقاء الفراش الطويل.', wound_type_id: 'wt009', initial_dressing_id: 'drs002', initial_medication_id: 'med002' },
  { id: 'rdx026', registration_id: 'reg016', diagnosis_id: 'dx005', description: 'تهيج جلدي نضحي في موضع الإبرة بالساعد الأيسر.', wound_type_id: 'wt003', initial_dressing_id: 'drs001', initial_medication_id: 'med001' },
  // reg017 — 1 diagnosis
  { id: 'rdx027', registration_id: 'reg017', diagnosis_id: 'dx012', description: 'جرح ما بعد عملية تثبيت شريحة ومسامير الكاحل الأيسر.', wound_type_id: 'wt004', initial_dressing_id: 'drs006', initial_medication_id: 'med002' },
  // reg099 — 1 diagnosis (discharged)
  { id: 'rdx028', registration_id: 'reg099', diagnosis_id: 'dx014', description: 'جرح جراحي سابق مع وجود إفرازات مصلية خفيفة.', wound_type_id: 'wt003', initial_dressing_id: 'drs001', initial_medication_id: 'med001' },
  // Historical registrations
  { id: 'rdx029', registration_id: 'reg001old', diagnosis_id: 'dx001', description: 'جرح قدم سكري في الكعب الأيسر — تم الشفاء بنسبة 90% والخروج.', wound_type_id: 'wt001', initial_dressing_id: 'drs001', initial_medication_id: 'med001' },
  { id: 'rdx030', registration_id: 'reg005old', diagnosis_id: 'dx001', description: 'قدم سكري مع ضعف تروية — تحسن جزئي وترخيص.', wound_type_id: 'wt005', initial_dressing_id: 'drs005', initial_medication_id: 'med001' },
  { id: 'rdx031', registration_id: 'reg013old', diagnosis_id: 'dx001', description: 'جرح قدم سكري سابق — تم الشفاء والترخيص بنجاح.', wound_type_id: 'wt001', initial_dressing_id: 'drs005', initial_medication_id: 'med001' }
];


// ════════════════════════════════════════════════════════════════════════════
// 8. REGISTRATION_DIAGNOSIS_PROCEDURES (performed at registration)
// ════════════════════════════════════════════════════════════════════════════

var REGISTRATION_DIAGNOSIS_PROCEDURES = [
  // reg001 — تنضير
  { id: 'rdxp001', registration_diagnosis_id: 'rdx001', procedure_id: 'proc001', performed_by_id: 'u001', performed_at: '2026-08-31T10:00:00Z' },
  // reg002 — تقييم
  { id: 'rdxp002', registration_diagnosis_id: 'rdx003', procedure_id: 'proc002', performed_by_id: 'u001', performed_at: '2026-09-01T11:00:00Z' },
  // reg003 — مسحة مزرعة
  { id: 'rdxp003', registration_diagnosis_id: 'rdx004', procedure_id: 'proc004', performed_by_id: 'u001', performed_at: '2026-09-01T10:30:00Z' },
  // reg004 — تغيير ضمادة
  { id: 'rdxp004', registration_diagnosis_id: 'rdx007', procedure_id: 'proc003', performed_by_id: 'u001', performed_at: '2026-09-05T08:00:00Z' },
  // reg005 — تقييم
  { id: 'rdxp005', registration_diagnosis_id: 'rdx008', procedure_id: 'proc002', performed_by_id: 'u001', performed_at: '2026-08-29T09:00:00Z' },
  // reg006 — تنضير
  { id: 'rdxp006', registration_diagnosis_id: 'rdx010', procedure_id: 'proc001', performed_by_id: 'u001', performed_at: '2026-09-02T09:00:00Z' },
  // reg007 — تغيير ضمادة
  { id: 'rdxp007', registration_diagnosis_id: 'rdx011', procedure_id: 'proc003', performed_by_id: 'u001', performed_at: '2026-09-03T10:00:00Z' },
  // reg008 — تنضير
  { id: 'rdxp008', registration_diagnosis_id: 'rdx013', procedure_id: 'proc001', performed_by_id: 'u001', performed_at: '2026-08-28T10:00:00Z' },
  // reg010 — تنضير
  { id: 'rdxp009', registration_diagnosis_id: 'rdx016', procedure_id: 'proc001', performed_by_id: 'u001', performed_at: '2026-08-30T09:00:00Z' },
  // reg013 — تنضير
  { id: 'rdxp010', registration_diagnosis_id: 'rdx021', procedure_id: 'proc001', performed_by_id: 'u001', performed_at: '2026-09-01T09:00:00Z' }
];


// ════════════════════════════════════════════════════════════════════════════
// 9. DAILY_REVIEWS
// ════════════════════════════════════════════════════════════════════════════

var DAILY_REVIEWS = [
  // reg001 — 3 daily reviews
  { id: 'drev001', registration_diagnosis_id: 'rdx001', review_date: '2026-08-31', nurse_id: 'u001', wound_description: 'جرح عميق في الكعب الأيسر، أبعاد الجرح 3×2 سم. إفرازات قيحية واضحة.', wound_changes: 'التسجيل الأولي للحالة عند الاستقبال من العناية المركزة.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'WORSENED' },
  { id: 'drev002', registration_diagnosis_id: 'rdx002', review_date: '2026-08-31', nurse_id: 'u001', wound_description: 'قرحة فراش في منطقة العجز — المرحلة الثانية.', wound_changes: 'تقييم أولي لمنطقة العجز وتنظيف الجرح.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  { id: 'drev003', registration_diagnosis_id: 'rdx001', review_date: '2026-09-02', nurse_id: 'u001', wound_description: 'تراجع ملحوظ في حجم الجرح — 2.5×1.8 سم. تقلص في الإفرازات القيحية.', wound_changes: 'تحسن تدريجي. لا عفونة حادة. تم تغيير الضمادة.', dressing_id: 'drs001', medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg002 — 1 daily review
  { id: 'drev004', registration_diagnosis_id: 'rdx003', review_date: '2026-09-05', nurse_id: 'u001', wound_description: 'قرحة وريدية في الساق اليمنى عند التسجيل — إفرازات غزيرة.', wound_changes: 'تسجيل أولي. تقييم الحالة الوريدية للمريضة.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg003 — 1 daily review
  { id: 'drev005', registration_diagnosis_id: 'rdx004', review_date: '2026-08-30', nurse_id: 'u001', wound_description: 'الجرح الجراحي نظيف وجاف في اليوم الأول بعد العملية.', wound_changes: 'تقييم أولي بعد العملية.', dressing_id: null, medication_id: null, follow_up_required: false, status: 'IMPROVED_FOLLOWUP' },
  // reg005 — 1 daily review
  { id: 'drev006', registration_diagnosis_id: 'rdx008', review_date: '2026-09-01', nurse_id: 'u001', wound_description: 'استمرار ضعف التروية. لا تغير ملحوظ في حجم الجرح.', wound_changes: 'لا تغير كبير. تم تجديد الضمادة.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'WORSENED' },
  // reg007 — 1 daily review
  { id: 'drev007', registration_diagnosis_id: 'rdx011', review_date: '2026-09-05', nurse_id: 'u001', wound_description: 'جرح الورك جاف ونظيف. لا مؤشرات للعدوى.', wound_changes: 'تقييم أولي.', dressing_id: null, medication_id: null, follow_up_required: false, status: 'IMPROVED_FOLLOWUP' },
  // reg008 — 1 daily review
  { id: 'drev008', registration_diagnosis_id: 'rdx013', review_date: '2026-09-02', nurse_id: 'u001', wound_description: 'الجرح مستقر. الضغط السلبي يعمل بشكل جيد.', wound_changes: 'لا تغيير.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg010 — 1 daily review
  { id: 'drev009', registration_diagnosis_id: 'rdx016', review_date: '2026-09-03', nurse_id: 'u001', wound_description: 'نخر مستمر في منطقة الكعب. احمرار في المحيط.', wound_changes: 'تم التنضير الأول. إفرازات قيحية تحسنت قليلاً.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'WORSENED' },
  // reg011 — 1 daily review
  { id: 'drev010', registration_diagnosis_id: 'rdx019', review_date: '2026-09-01', nurse_id: 'u001', wound_description: 'الجرح نظيف. لا مؤشرات للعدوى.', wound_changes: 'تحسن ملحوظ. تم تغيير الضمادة.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg013 — 1 daily review
  { id: 'drev011', registration_diagnosis_id: 'rdx021', review_date: '2026-09-04', nurse_id: 'u001', wound_description: 'تحسن طفيف في حواف الجرح بعد التنضير.', wound_changes: 'تقلص بسيط في الأنسجة الميتة.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg014 — 1 daily review
  { id: 'drev012', registration_diagnosis_id: 'rdx023', review_date: '2026-09-03', nurse_id: 'u001', wound_description: 'انخفاض في مستوى الإفرازات النضحية.', wound_changes: 'استمرار التئام الحواف الجانبية.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg015 — 1 daily review
  { id: 'drev013', registration_diagnosis_id: 'rdx024', review_date: '2026-09-05', nurse_id: 'u001', wound_description: 'الجرح الجراحي نظيف ومستقر.', wound_changes: 'التئام ممتاز لطبقات الجلد الأولية.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg016 — 1 daily review
  { id: 'drev014', registration_diagnosis_id: 'rdx025', review_date: '2026-09-04', nurse_id: 'u001', wound_description: 'تراجع الاحمرار المحيط بالكعب.', wound_changes: 'تحسن ملحوظ في الأنسجة الحبيبية.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg017 — 1 daily review
  { id: 'drev015', registration_diagnosis_id: 'rdx027', review_date: '2026-09-05', nurse_id: 'u001', wound_description: 'الجرح نظيف وجاف. حواف الجرح ملتئمة.', wound_changes: 'لا توجد مؤشرات عدوى.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg099 — 2 daily reviews (discharged)
  { id: 'drev016', registration_diagnosis_id: 'rdx028', review_date: '2026-04-12', nurse_id: 'u001', wound_description: 'تنظيف وتنضير الجرح الجراحي الأول.', wound_changes: 'استجابة أولية جيدة وتراجع التورم.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  { id: 'drev017', registration_diagnosis_id: 'rdx028', review_date: '2026-05-08', nurse_id: 'u001', wound_description: 'التئام تام لحواف الجرح وجفاف الإفرازات بالكامل.', wound_changes: 'تحسن ممتاز وتغطية جلدية مكتملة.', dressing_id: 'drs008', medication_id: 'med002', follow_up_required: false, status: 'IMPROVED_DISCHARGE' },
  // Historical registrations
  { id: 'drev018', registration_diagnosis_id: 'rdx029', review_date: '2025-11-12', nurse_id: 'u003', wound_description: 'التسجيل الأولي لحالة الجرح السابقة.', wound_changes: 'بدء خطة العلاج والتنضير.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  { id: 'drev019', registration_diagnosis_id: 'rdx029', review_date: '2025-12-05', nurse_id: 'u003', wound_description: 'التئام ملحوظ في الأنسجة وبداية الشفاء الكامل.', wound_changes: 'تحسن كبير في حواف الجرح وتقليل الإفرازات.', dressing_id: null, medication_id: null, follow_up_required: false, status: 'IMPROVED_DISCHARGE' },
  { id: 'drev020', registration_diagnosis_id: 'rdx030', review_date: '2026-03-10', nurse_id: 'u003', wound_description: 'التسجيل الأولي — قدم سكري مع ضعف تروية.', wound_changes: 'بدء العلاج.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'WORSENED' },
  { id: 'drev021', registration_diagnosis_id: 'rdx030', review_date: '2026-04-02', nurse_id: 'u003', wound_description: 'تحسن جزئي في التروية وحجم الجرح.', wound_changes: 'شفاء تدريجي.', dressing_id: null, medication_id: null, follow_up_required: false, status: 'IMPROVED_DISCHARGE' },
  { id: 'drev022', registration_diagnosis_id: 'rdx031', review_date: '2025-08-15', nurse_id: 'u003', wound_description: 'تسجيل أولي — قدم سكري.', wound_changes: 'بدء العلاج.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'WORSENED' },
  { id: 'drev023', registration_diagnosis_id: 'rdx031', review_date: '2025-09-10', nurse_id: 'u003', wound_description: 'شفاء تام.', wound_changes: 'التئام كامل والترخيص.', dressing_id: null, medication_id: null, follow_up_required: false, status: 'IMPROVED_DISCHARGE' }
];


// ════════════════════════════════════════════════════════════════════════════
// 10. SCHEDULED_REVIEWS
// ════════════════════════════════════════════════════════════════════════════

var SCHEDULED_REVIEWS = [
  // TODAY reviews
  { id: 'srev001', registration_diagnosis_id: 'rdx001', created_by_id: 'u001', source_daily_review_id: 'drev003', scheduled_date: '2026-09-05', status: 'SCHEDULED', notes: 'متابعة شفاء الجرح وتغيير الضمادة المستمرة بعد الترخيص من المتابعة اليومية.', created_at: '2026-08-28T10:00:00Z', completed_at: null },
  { id: 'srev002', registration_diagnosis_id: 'rdx004', created_by_id: 'u001', source_daily_review_id: 'drev005', scheduled_date: '2026-09-05', status: 'SCHEDULED', notes: 'تقييم الجرح الجراحي وإزالة خيوط العملية.', created_at: '2026-08-30T10:00:00Z', completed_at: null },
  // SCHEDULED (future)
  { id: 'srev003', registration_diagnosis_id: 'rdx008', created_by_id: 'u001', source_daily_review_id: 'drev006', scheduled_date: '2026-09-08', status: 'SCHEDULED', notes: 'متابعة تروية القدم ونسبة شفاء القرحة وتغيير الضماد الفضي.', created_at: '2026-09-01T10:00:00Z', completed_at: null },
  { id: 'srev004', registration_diagnosis_id: 'rdx011', created_by_id: 'u001', source_daily_review_id: 'drev007', scheduled_date: '2026-09-12', status: 'SCHEDULED', notes: 'فحص ندبة العمليات وتغيير الضماد الواقي.', created_at: '2026-09-03T10:00:00Z', completed_at: null },
  { id: 'srev005', registration_diagnosis_id: 'rdx002', created_by_id: 'u001', source_daily_review_id: 'drev002', scheduled_date: '2026-09-14', status: 'SCHEDULED', notes: 'متابعة مستقلة لقرحة العجز الثانوية للمريض.', created_at: '2026-08-31T10:00:00Z', completed_at: null },
  { id: 'srev006', registration_diagnosis_id: 'rdx020', created_by_id: 'u001', source_daily_review_id: null, scheduled_date: '2026-09-10', status: 'SCHEDULED', notes: 'فحص سلامة الجلد المحيط بفغرة القولون.', created_at: '2026-09-02T10:00:00Z', completed_at: null },
  { id: 'srev007', registration_diagnosis_id: 'rdx021', created_by_id: 'u001', source_daily_review_id: 'drev011', scheduled_date: '2026-09-11', status: 'SCHEDULED', notes: 'متابعة تقييم الضمادة الفضية والتنضير.', created_at: '2026-09-04T10:00:00Z', completed_at: null },
  { id: 'srev008', registration_diagnosis_id: 'rdx023', created_by_id: 'u001', source_daily_review_id: 'drev012', scheduled_date: '2026-09-09', status: 'SCHEDULED', notes: 'فحص إفرازات الساق والضماد الضاغط.', created_at: '2026-09-03T10:00:00Z', completed_at: null },
  { id: 'srev009', registration_diagnosis_id: 'rdx024', created_by_id: 'u001', source_daily_review_id: 'drev013', scheduled_date: '2026-09-14', status: 'SCHEDULED', notes: 'مراجعة ختامية للجرح الجراحي.', created_at: '2026-09-05T10:00:00Z', completed_at: null },
  { id: 'srev010', registration_diagnosis_id: 'rdx025', created_by_id: 'u001', source_daily_review_id: 'drev014', scheduled_date: '2026-09-10', status: 'SCHEDULED', notes: 'متابعة تغيرات قرحة الكعب.', created_at: '2026-09-04T10:00:00Z', completed_at: null },
  { id: 'srev011', registration_diagnosis_id: 'rdx027', created_by_id: 'u001', source_daily_review_id: 'drev015', scheduled_date: '2026-09-13', status: 'SCHEDULED', notes: 'فحص الالتئام وإزالة الغرز.', created_at: '2026-09-05T10:00:00Z', completed_at: null },
  // MISSED (overdue)
  { id: 'srev012', registration_diagnosis_id: 'rdx017', created_by_id: 'u001', source_daily_review_id: null, scheduled_date: '2026-09-01', status: 'MISSED', notes: 'متابعة تنظيف وتقييم عمق القرحة وتأخر المريضة عن الحضور.', created_at: '2026-08-25T10:00:00Z', completed_at: null },
  { id: 'srev013', registration_diagnosis_id: 'rdx003', created_by_id: 'u001', source_daily_review_id: null, scheduled_date: '2026-08-28', status: 'MISSED', notes: 'متابعة نسبة الإفرازات النضحية والضغط الضاغط على الساق.', created_at: '2026-08-20T10:00:00Z', completed_at: null },
  // COMPLETED
  { id: 'srev014', registration_diagnosis_id: 'rdx019', created_by_id: 'u001', source_daily_review_id: 'drev010', scheduled_date: '2026-09-03', status: 'COMPLETED', notes: 'مراجعة نهائية لموضع القسطرة بعد الشفاء الكامل.', created_at: '2026-08-27T10:00:00Z', completed_at: '2026-09-03T11:15:00Z' },
  { id: 'srev015', registration_diagnosis_id: 'rdx010', created_by_id: 'u001', source_daily_review_id: null, scheduled_date: '2026-09-02', status: 'COMPLETED', notes: 'متابعة التئام قرحة الكتف وتقييم الضماد.', created_at: '2026-08-26T10:00:00Z', completed_at: '2026-09-02T10:40:00Z' },
  // CANCELLED
  { id: 'srev016', registration_diagnosis_id: 'rdx013', created_by_id: 'u001', source_daily_review_id: null, scheduled_date: '2026-08-30', status: 'CANCELLED', notes: 'سفر المريضة خارج المنطقة ومتابعة الحالة في مستشفى آخر بناءً على طلب الأسرة.', created_at: '2026-08-22T10:00:00Z', completed_at: null },
  // Historical (discharged patient reg099)
  { id: 'srev017', registration_diagnosis_id: 'rdx028', created_by_id: 'u001', source_daily_review_id: 'drev017', scheduled_date: '2026-05-10', status: 'COMPLETED', notes: 'مراجعة ختامية بعد الشفاء وتأكيد الخروج.', created_at: '2026-05-01T10:00:00Z', completed_at: '2026-05-10T11:00:00Z' }
];


// ════════════════════════════════════════════════════════════════════════════
// 11. AUDIT LOG (placeholder)
// ════════════════════════════════════════════════════════════════════════════

var AUDIT_LOG = [];


// ════════════════════════════════════════════════════════════════════════════
// 12. CURRENT USER & SESSION
// ════════════════════════════════════════════════════════════════════════════

var CURRENT_USER_ID = 'u001'; // Default logged-in user


// ════════════════════════════════════════════════════════════════════════════
// 13. ID COUNTERS (for generating new IDs)
// ════════════════════════════════════════════════════════════════════════════

var _counters = {
  patient: 100, request: 400, registration: 100, rpmh: 100,
  rdx: 100, rdxp: 100, dailyReview: 100, scheduledReview: 100, auditLog: 1
};

function _genId(prefix) {
  _counters[prefix] = (_counters[prefix] || 0) + 1;
  return prefix + '-' + String(_counters[prefix]).padStart(3, '0');
}


// ════════════════════════════════════════════════════════════════════════════
// 14. HELPER UTILITIES
// ════════════════════════════════════════════════════════════════════════════

function _find(arr, id) {
  for (var i = 0; i < arr.length; i++) { if (arr[i].id === id) return arr[i]; }
  return null;
}

function _filter(arr, key, val) {
  return arr.filter(function (item) { return item[key] === val; });
}

function _today() {
  var d = new Date();
  return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
}

function _now() {
  return new Date().toISOString();
}

function _formatDate(isoStr) {
  if (!isoStr) return 'غير متوفر';
  var dateOnly = isoStr.split('T')[0];
  var parts = dateOnly.split('-');
  if (parts.length === 3) {
    return parts[2].padStart(2, '0') + '/' + parts[1].padStart(2, '0') + '/' + parts[0];
  }
  return isoStr;
}

function _formatDateTime(isoStr) {
  if (!isoStr) return 'غير متوفر';
  var parts = isoStr.split('T');
  var datePart = _formatDate(parts[0]);
  var timePart = parts[1] ? parts[1].substring(0, 5) : '';
  return timePart ? datePart + ' — ' + timePart : datePart;
}

function _genderAr(g) {
  if (g === 'MALE') return 'ذكر';
  if (g === 'FEMALE') return 'أنثى';
  return g || '';
}

function _clone(obj) {
  return JSON.parse(JSON.stringify(obj));
}


// ════════════════════════════════════════════════════════════════════════════
// 15. API — LOOKUP FUNCTIONS
// ════════════════════════════════════════════════════════════════════════════

var _tables = {
  users: USERS, departments: DEPARTMENTS, diagnoses: DIAGNOSES,
  wound_types: WOUND_TYPES, dressings: DRESSINGS, medications: MEDICATIONS,
  procedures: PROCEDURES, pmh: PMH_LIST
};

function lookup(tableName, id) {
  if (!id) return null;
  var table = _tables[tableName];
  if (!table) return null;
  var item = _find(table, id);
  return item ? item.name || item.full_name : null;
}

function getActiveLookups(tableName) {
  var table = _tables[tableName];
  if (!table) return [];
  return table.filter(function (item) { return item.is_active !== false; });
}

function addLookupItem(tableName, item) {
  var table = _tables[tableName];
  if (!table) return null;
  table.push(item);
  return item;
}

function updateLookupItem(tableName, id, updates) {
  var table = _tables[tableName];
  if (!table) return null;
  var item = _find(table, id);
  if (!item) return null;
  for (var key in updates) {
    if (updates.hasOwnProperty(key)) item[key] = updates[key];
  }
  return item;
}


// ════════════════════════════════════════════════════════════════════════════
// 16. API — HOSPITAL SYSTEM (External)
// ════════════════════════════════════════════════════════════════════════════

function searchHospitalPatient(nationalId) {
  if (!nationalId) return null;
  for (var i = 0; i < HOSPITAL_PATIENTS.length; i++) {
    if (HOSPITAL_PATIENTS[i].national_id === nationalId) return _clone(HOSPITAL_PATIENTS[i]);
  }
  return null;
}


// ════════════════════════════════════════════════════════════════════════════
// 17. API — PATIENTS
// ════════════════════════════════════════════════════════════════════════════

function getPatientById(id) {
  return _find(PATIENTS, id);
}

function getPatientByNationalId(nationalId) {
  for (var i = 0; i < PATIENTS.length; i++) {
    if (PATIENTS[i].national_id === nationalId) return PATIENTS[i];
  }
  return null;
}

function getPatientByHospitalId(hospitalId) {
  for (var i = 0; i < PATIENTS.length; i++) {
    if (PATIENTS[i].hospital_patient_id === hospitalId) return PATIENTS[i];
  }
  return null;
}

function getAllPatients() {
  return PATIENTS.slice();
}



// ════════════════════════════════════════════════════════════════════════════
// 18. API — REGISTRATION REQUESTS
// ════════════════════════════════════════════════════════════════════════════

function getRequestById(id) {
  return _find(REGISTRATION_REQUESTS, id);
}

function getAllRequests(filters) {
  var list = REGISTRATION_REQUESTS.slice();
  if (filters && filters.status) {
    list = list.filter(function (r) { return r.status === filters.status; });
  }
  // Sort newest first
  list.sort(function (a, b) { return (b.requested_at || '').localeCompare(a.requested_at || ''); });
  return list;
}

function submitRequest(data) {
  var req = {
    id: _genId('request'),
    patient_id: data.patient_id || null,
    submitted_by_id: data.submitted_by_id || CURRENT_USER_ID,
    reviewed_by_id: null,
    requested_at: _now(),
    reviewed_at: null,
    status: 'PENDING',
    rejection_reason: null,
    notes: data.notes || '',
    pmh_ids: data.pmh_ids || [],
    hospital_patient_id: data.hospital_patient_id,
    national_id: data.national_id,
    patient_full_name: data.patient_full_name,
    gender: data.gender,
    patient_age: data.patient_age,
    supervising_doctor: data.supervising_doctor,
    hospital_department: data.hospital_department,
    hospital_admission_date: data.hospital_admission_date,
    hospital_data_fetched_at: _now()
  };
  REGISTRATION_REQUESTS.push(req);
  _addAuditLog('CREATE', 'REGISTRATION_REQUEST', req.id, { status: 'PENDING' });
  return _clone(req);
}

function approveRequest(requestId, reviewedById, regData) {
  var req = _find(REGISTRATION_REQUESTS, requestId);
  if (!req || req.status !== 'PENDING') return null;

  req.status = 'APPROVED';
  req.reviewed_by_id = reviewedById || CURRENT_USER_ID;
  req.reviewed_at = _now();

  // Create or find patient
  var patient = getPatientByNationalId(req.national_id);
  if (!patient) {
    patient = {
      id: _genId('patient'),
      hospital_patient_id: req.hospital_patient_id,
      national_id: req.national_id,
      full_name: req.patient_full_name,
      gender: req.gender,
      age: req.patient_age,
      supervising_doctor: req.supervising_doctor,
      hospital_department: req.hospital_department,
      hospital_admission_date: req.hospital_admission_date,
      last_synced_at: _now()
    };
    PATIENTS.push(patient);
  }
  req.patient_id = patient.id;

  // Create registration
  var reg = {
    id: _genId('registration'),
    patient_id: patient.id,
    registration_request_id: req.id,
    registering_nurse_id: reviewedById || CURRENT_USER_ID,
    department_id: regData.department_id || null,
    registration_date: _today(),
    status: 'ACTIVE',
    discharge_date: null,
    discharge_type: null,
    discharged_by_id: null
  };
  REGISTRATIONS.push(reg);

  // Add PMH
  var pmhIds = (regData && regData.pmh_ids && regData.pmh_ids.length > 0) ? regData.pmh_ids : (req.pmh_ids || []);
  if (pmhIds.length > 0) {
    pmhIds.forEach(function (pmhId) {
      REGISTRATION_PMH.push({
        id: _genId('rpmh'),
        registration_id: reg.id,
        pmh_id: pmhId
      });
    });
  }

  // Add diagnoses
  if (regData.diagnoses && regData.diagnoses.length > 0) {
    regData.diagnoses.forEach(function (dx) {
      var rdx = {
        id: _genId('rdx'),
        registration_id: reg.id,
        diagnosis_id: dx.diagnosis_id,
        description: dx.description || '',
        wound_type_id: dx.wound_type_id,
        initial_dressing_id: dx.initial_dressing_id || null,
        initial_medication_id: dx.initial_medication_id || null
      };
      REGISTRATION_DIAGNOSES.push(rdx);

      // Add procedures per diagnosis
      if (dx.procedure_ids && dx.procedure_ids.length > 0) {
        dx.procedure_ids.forEach(function (procId) {
          REGISTRATION_DIAGNOSIS_PROCEDURES.push({
            id: _genId('rdxp'),
            registration_diagnosis_id: rdx.id,
            procedure_id: procId,
            performed_by_id: reviewedById || CURRENT_USER_ID,
            performed_at: _now()
          });
        });
      }
    });
  }

  _addAuditLog('APPROVE', 'REGISTRATION_REQUEST', req.id, { registration_id: reg.id });
  return { request: _clone(req), registration: _clone(reg), patient: _clone(patient) };
}

function rejectRequest(requestId, reviewedById, reason) {
  var req = _find(REGISTRATION_REQUESTS, requestId);
  if (!req || req.status !== 'PENDING') return null;
  req.status = 'REJECTED';
  req.reviewed_by_id = reviewedById || CURRENT_USER_ID;
  req.reviewed_at = _now();
  req.rejection_reason = reason || '';
  _addAuditLog('REJECT', 'REGISTRATION_REQUEST', req.id, { reason: reason });
  return _clone(req);
}

function cancelRequest(requestId) {
  var req = _find(REGISTRATION_REQUESTS, requestId);
  if (!req || req.status !== 'PENDING') return null;
  req.status = 'CANCELLED';
  _addAuditLog('CANCEL', 'REGISTRATION_REQUEST', req.id, {});
  return _clone(req);
}


// ════════════════════════════════════════════════════════════════════════════
// 19. API — REGISTRATIONS
// ════════════════════════════════════════════════════════════════════════════

function getRegistrationById(id) {
  return _find(REGISTRATIONS, id);
}

function getRegistrationsByPatientId(patientId) {
  return _filter(REGISTRATIONS, 'patient_id', patientId);
}

function getAllActiveRegistrations() {
  return REGISTRATIONS.filter(function (r) { return r.status === 'ACTIVE'; });
}

function getAllRegistrations() {
  return REGISTRATIONS.slice();
}

function dischargeRegistration(regId, dischargeType, dischargedById) {
  var reg = _find(REGISTRATIONS, regId);
  if (!reg || reg.status !== 'ACTIVE') return null;
  reg.status = 'DISCHARGED';
  reg.discharge_date = _today();
  reg.discharge_type = dischargeType || 'MANUAL';
  reg.discharged_by_id = dischargedById || CURRENT_USER_ID;
  _addAuditLog('DISCHARGE', 'REGISTRATION', reg.id, { discharge_type: dischargeType });
  return _clone(reg);
}


// ════════════════════════════════════════════════════════════════════════════
// 20. API — REGISTRATION DIAGNOSES
// ════════════════════════════════════════════════════════════════════════════

function getDiagnosesByRegistrationId(regId) {
  return _filter(REGISTRATION_DIAGNOSES, 'registration_id', regId);
}

function getRegistrationDiagnosisById(rdxId) {
  return _find(REGISTRATION_DIAGNOSES, rdxId);
}


// ════════════════════════════════════════════════════════════════════════════
// 21. API — REGISTRATION PMH
// ════════════════════════════════════════════════════════════════════════════

function getPMHByRegistrationId(regId) {
  return _filter(REGISTRATION_PMH, 'registration_id', regId);
}


// ════════════════════════════════════════════════════════════════════════════
// 22. API — PROCEDURES
// ════════════════════════════════════════════════════════════════════════════

function getProceduresByRegistrationDiagnosisId(rdxId) {
  return _filter(REGISTRATION_DIAGNOSIS_PROCEDURES, 'registration_diagnosis_id', rdxId);
}

function getProceduresByRegistrationId(regId) {
  var diagIds = getDiagnosesByRegistrationId(regId).map(function (d) { return d.id; });
  return REGISTRATION_DIAGNOSIS_PROCEDURES.filter(function (p) {
    return diagIds.indexOf(p.registration_diagnosis_id) !== -1;
  });
}


// ════════════════════════════════════════════════════════════════════════════
// 23. API — DAILY REVIEWS
// ════════════════════════════════════════════════════════════════════════════

function getDailyReviewsByRegistrationDiagnosisId(rdxId) {
  return _filter(DAILY_REVIEWS, 'registration_diagnosis_id', rdxId)
    .sort(function (a, b) { return (b.review_date || '').localeCompare(a.review_date || ''); });
}

function getDailyReviewsByRegistrationId(regId) {
  var diagIds = getDiagnosesByRegistrationId(regId).map(function (d) { return d.id; });
  return DAILY_REVIEWS.filter(function (r) {
    return diagIds.indexOf(r.registration_diagnosis_id) !== -1;
  }).sort(function (a, b) { return (b.review_date || '').localeCompare(a.review_date || ''); });
}

function getLatestDailyReviewDate(regId) {
  var reviews = getDailyReviewsByRegistrationId(regId);
  return reviews.length > 0 ? reviews[0].review_date : null;
}

function createDailyReview(data) {
  var review = {
    id: _genId('dailyReview'),
    registration_diagnosis_id: data.registration_diagnosis_id,
    review_date: data.review_date || _today(),
    nurse_id: data.nurse_id || CURRENT_USER_ID,
    wound_description: data.wound_description || '',
    wound_changes: data.wound_changes || '',
    dressing_id: data.dressing_id || null,
    medication_id: data.medication_id || null,
    follow_up_required: data.follow_up_required || false,
    status: data.status || 'IMPROVED_FOLLOWUP'
  };
  DAILY_REVIEWS.push(review);
  _addAuditLog('CREATE', 'DAILY_REVIEW', review.id, { status: review.status });
  return _clone(review);
}


// ════════════════════════════════════════════════════════════════════════════
// 24. API — SCHEDULED REVIEWS
// ════════════════════════════════════════════════════════════════════════════

function getScheduledReviewsByRegistrationDiagnosisId(rdxId) {
  return _filter(SCHEDULED_REVIEWS, 'registration_diagnosis_id', rdxId);
}

function getScheduledReviewsByRegistrationId(regId) {
  var diagIds = getDiagnosesByRegistrationId(regId).map(function (d) { return d.id; });
  return SCHEDULED_REVIEWS.filter(function (s) {
    return diagIds.indexOf(s.registration_diagnosis_id) !== -1;
  });
}

function getNextScheduledDate(regId) {
  var scheduled = getScheduledReviewsByRegistrationId(regId)
    .filter(function (s) { return s.status === 'SCHEDULED'; })
    .sort(function (a, b) { return (a.scheduled_date || '').localeCompare(b.scheduled_date || ''); });
  return scheduled.length > 0 ? scheduled[0].scheduled_date : null;
}

function getAllScheduledReviews(filters) {
  var list = SCHEDULED_REVIEWS.slice();
  if (filters) {
    if (filters.status) list = list.filter(function (s) { return s.status === filters.status; });
    if (filters.date) list = list.filter(function (s) { return s.scheduled_date === filters.date; });
  }
  list.sort(function (a, b) { return (a.scheduled_date || '').localeCompare(b.scheduled_date || ''); });
  return list;
}

function createScheduledReview(data) {
  var review = {
    id: _genId('scheduledReview'),
    registration_diagnosis_id: data.registration_diagnosis_id,
    created_by_id: data.created_by_id || CURRENT_USER_ID,
    source_daily_review_id: data.source_daily_review_id || null,
    scheduled_date: data.scheduled_date,
    status: 'SCHEDULED',
    notes: data.notes || '',
    created_at: _now(),
    completed_at: null
  };
  SCHEDULED_REVIEWS.push(review);
  _addAuditLog('CREATE', 'SCHEDULED_REVIEW', review.id, { scheduled_date: data.scheduled_date });
  return _clone(review);
}

function completeScheduledReview(id) {
  var review = _find(SCHEDULED_REVIEWS, id);
  if (!review || review.status !== 'SCHEDULED') return null;
  review.status = 'COMPLETED';
  review.completed_at = _now();
  _addAuditLog('COMPLETE', 'SCHEDULED_REVIEW', id, {});
  return _clone(review);
}

function cancelScheduledReview(id, reason) {
  var review = _find(SCHEDULED_REVIEWS, id);
  if (!review || review.status !== 'SCHEDULED') return null;
  review.status = 'CANCELLED';
  review.notes = (review.notes ? review.notes + ' | إلغاء: ' : 'إلغاء: ') + (reason || '');
  _addAuditLog('CANCEL', 'SCHEDULED_REVIEW', id, { reason: reason });
  return _clone(review);
}


// ════════════════════════════════════════════════════════════════════════════
// 25. API — PATIENT HISTORY (Consolidated View)
// ════════════════════════════════════════════════════════════════════════════

function getPatientHistory(searchId) {
  if (!searchId) return null;

  // Find patient by ID, national ID, or registration ID
  var patient = _find(PATIENTS, searchId) || getPatientByNationalId(searchId);

  // If searchId is a registration ID, resolve to patient
  if (!patient) {
    var reg = _find(REGISTRATIONS, searchId);
    if (reg) patient = _find(PATIENTS, reg.patient_id);
  }

  // Fallback
  if (!patient) patient = PATIENTS[0];
  if (!patient) return null;

  // Get all registrations for this patient
  var patientRegs = getRegistrationsByPatientId(patient.id);
  patientRegs.sort(function (a, b) {
    if (a.status === 'ACTIVE' && b.status !== 'ACTIVE') return -1;
    if (a.status !== 'ACTIVE' && b.status === 'ACTIVE') return 1;
    return (b.registration_date || '').localeCompare(a.registration_date || '');
  });

  var activeReg = patientRegs.find(function (r) { return r.status === 'ACTIVE'; });

  // Build consolidated registration data
  var consolidatedRegs = patientRegs.map(function (reg) {
    var diags = getDiagnosesByRegistrationId(reg.id);
    var pmhRecords = getPMHByRegistrationId(reg.id);
    var dailyRevs = getDailyReviewsByRegistrationId(reg.id);
    var schedRevs = getScheduledReviewsByRegistrationId(reg.id);
    var procs = getProceduresByRegistrationId(reg.id);
    var dept = _find(DEPARTMENTS, reg.department_id);
    var nurse = _find(USERS, reg.registering_nurse_id);

    return {
      registration_id: reg.id,
      registration_date: reg.registration_date,
      status: reg.status,
      department: dept ? dept.name : '',
      registering_nurse: nurse ? nurse.full_name : '',
      discharge_date: reg.discharge_date,
      discharge_type: reg.discharge_type,
      diagnoses: diags.map(function (dx) {
        return {
          id: dx.id,
          name: lookup('diagnoses', dx.diagnosis_id) || '',
          wound_type: lookup('wound_types', dx.wound_type_id) || '',
          dressing: lookup('dressings', dx.initial_dressing_id) || '',
          medication: lookup('medications', dx.initial_medication_id) || '',
          description: dx.description
        };
      }),
      pmh: pmhRecords.map(function (p) { return lookup('pmh', p.pmh_id) || ''; }),
      procedures: procs.map(function (p) {
        return {
          name: lookup('procedures', p.procedure_id) || '',
          performed_by: lookup('users', p.performed_by_id) || '',
          performed_at: p.performed_at
        };
      }),
      daily_reviews: dailyRevs.map(function (r) {
        var rdx = getRegistrationDiagnosisById(r.registration_diagnosis_id);
        return {
          id: r.id,
          date: r.review_date,
          nurse: lookup('users', r.nurse_id) || '',
          diagnosis_name: rdx ? lookup('diagnoses', rdx.diagnosis_id) : '',
          wound_description: r.wound_description,
          wound_changes: r.wound_changes,
          dressing: r.dressing_id ? lookup('dressings', r.dressing_id) : null,
          medication: r.medication_id ? lookup('medications', r.medication_id) : null,
          follow_up_required: r.follow_up_required,
          status: r.status
        };
      }),
      scheduled_reviews: schedRevs.map(function (s) {
        var rdx = getRegistrationDiagnosisById(s.registration_diagnosis_id);
        return {
          id: s.id,
          diagnosis_name: rdx ? lookup('diagnoses', rdx.diagnosis_id) : '',
          scheduled_date: s.scheduled_date,
          status: s.status,
          notes: s.notes,
          completed_at: s.completed_at
        };
      }),
      reviews_count: dailyRevs.length + schedRevs.length
    };
  });

  // Stats
  var totalReviews = 0;
  var latestReviewDate = null;
  consolidatedRegs.forEach(function (r) {
    totalReviews += r.reviews_count;
    r.daily_reviews.forEach(function (dr) {
      if (dr.date && (!latestReviewDate || dr.date > latestReviewDate)) latestReviewDate = dr.date;
    });
  });

  return {
    patient_id: patient.id,
    full_name: patient.full_name,
    national_id: patient.national_id,
    hospital_patient_id: patient.hospital_patient_id,
    age: patient.age,
    gender: patient.gender,
    gender_ar: _genderAr(patient.gender),
    supervising_doctor: patient.supervising_doctor,
    hospital_department: patient.hospital_department,
    has_active_registration: !!activeReg,
    active_registration_id: activeReg ? activeReg.id : null,
    stats: {
      total_registrations: consolidatedRegs.length,
      active_count: consolidatedRegs.filter(function (r) { return r.status === 'ACTIVE'; }).length,
      total_reviews: totalReviews,
      latest_review_date: latestReviewDate
    },
    registrations: consolidatedRegs
  };
}


// ════════════════════════════════════════════════════════════════════════════
// 26. API — ENRICHED REGISTRATION (for daily review page)
// ════════════════════════════════════════════════════════════════════════════

function getEnrichedRegistration(regId) {
  var reg = _find(REGISTRATIONS, regId);
  if (!reg) {
    // Fallback to first active registration
    var active = getAllActiveRegistrations();
    reg = active.length > 0 ? active[0] : REGISTRATIONS[0];
  }
  if (!reg) return null;

  var patient = _find(PATIENTS, reg.patient_id);
  var dept = _find(DEPARTMENTS, reg.department_id);
  var nurse = _find(USERS, reg.registering_nurse_id);
  var diags = getDiagnosesByRegistrationId(reg.id);
  var pmhRecords = getPMHByRegistrationId(reg.id);
  var dailyRevs = getDailyReviewsByRegistrationId(reg.id);
  var schedRevs = getScheduledReviewsByRegistrationId(reg.id);
  var procs = getProceduresByRegistrationId(reg.id);

  // Find previous registrations for this patient
  var prevRegs = getRegistrationsByPatientId(reg.patient_id).filter(function (r) {
    return r.id !== reg.id && r.status === 'DISCHARGED';
  });

  return {
    // Registration
    registration_id: reg.id,
    registration_date: reg.registration_date,
    status: reg.status,
    discharge_date: reg.discharge_date,
    discharge_type: reg.discharge_type,
    department: dept ? dept.name : '',
    department_id: reg.department_id,
    registering_nurse: nurse ? nurse.full_name : '',

    // Patient
    patient_id: patient ? patient.id : '',
    full_name: patient ? patient.full_name : '',
    national_id: patient ? patient.national_id : '',
    hospital_patient_id: patient ? patient.hospital_patient_id : '',
    age: patient ? patient.age : 0,
    gender: patient ? patient.gender : '',
    gender_ar: patient ? _genderAr(patient.gender) : '',
    supervising_doctor: patient ? patient.supervising_doctor : '',
    hospital_department: patient ? patient.hospital_department : '',

    // PMH (resolved names)
    pmh: pmhRecords.map(function (p) { return lookup('pmh', p.pmh_id) || ''; }),

    // Diagnoses (resolved with lookup names)
    diagnoses: diags.map(function (dx) {
      return {
        id: dx.id,
        diagnosis_id: dx.diagnosis_id,
        name: lookup('diagnoses', dx.diagnosis_id) || '',
        wound_type_id: dx.wound_type_id,
        wound_type: lookup('wound_types', dx.wound_type_id) || '',
        initial_dressing_id: dx.initial_dressing_id,
        dressing: lookup('dressings', dx.initial_dressing_id) || '',
        initial_medication_id: dx.initial_medication_id,
        medication: lookup('medications', dx.initial_medication_id) || '',
        description: dx.description
      };
    }),

    // Procedures (resolved)
    procedures: procs.map(function (p) {
      return {
        id: p.id,
        name: lookup('procedures', p.procedure_id) || '',
        performed_by: lookup('users', p.performed_by_id) || '',
        performed_at: p.performed_at
      };
    }),

    // Daily reviews (resolved)
    daily_reviews: dailyRevs.map(function (r) {
      var rdx = getRegistrationDiagnosisById(r.registration_diagnosis_id);
      return {
        id: r.id,
        registration_diagnosis_id: r.registration_diagnosis_id,
        date: r.review_date,
        nurse: lookup('users', r.nurse_id) || '',
        diagnosis_name: rdx ? lookup('diagnoses', rdx.diagnosis_id) : '',
        wound_type: rdx ? lookup('wound_types', rdx.wound_type_id) : '',
        wound_description: r.wound_description,
        wound_changes: r.wound_changes,
        dressing: r.dressing_id ? lookup('dressings', r.dressing_id) : null,
        medication: r.medication_id ? lookup('medications', r.medication_id) : null,
        follow_up_required: r.follow_up_required,
        status: r.status
      };
    }),

    // Scheduled reviews (resolved)
    scheduled_reviews: schedRevs.map(function (s) {
      var rdx = getRegistrationDiagnosisById(s.registration_diagnosis_id);
      return {
        id: s.id,
        diagnosis_name: rdx ? lookup('diagnoses', rdx.diagnosis_id) : '',
        scheduled_date: s.scheduled_date,
        status: s.status,
        notes: s.notes,
        created_by: lookup('users', s.created_by_id) || '',
        completed_at: s.completed_at
      };
    }),

    // Previous registrations (summary)
    previous_registrations: prevRegs.map(function (pr) {
      var prDiags = getDiagnosesByRegistrationId(pr.id);
      var prReviews = getDailyReviewsByRegistrationId(pr.id);
      return {
        registration_id: pr.id,
        registration_date: pr.registration_date,
        discharge_date: pr.discharge_date,
        diagnoses_count: prDiags.length,
        diagnoses_names: prDiags.map(function (d) { return lookup('diagnoses', d.diagnosis_id); }).join('، '),
        reviews_count: prReviews.length,
        outcome: prReviews.length > 0 ? prReviews[0].status : 'N/A'
      };
    })
  };
}


// ════════════════════════════════════════════════════════════════════════════
// 27. API — ENRICHED REGISTERED PATIENTS LIST
// ════════════════════════════════════════════════════════════════════════════

function getEnrichedActiveRegistrations() {
  var activeRegs = getAllActiveRegistrations();
  return activeRegs.map(function (reg) {
    var patient = _find(PATIENTS, reg.patient_id);
    var dept = _find(DEPARTMENTS, reg.department_id);
    var diags = getDiagnosesByRegistrationId(reg.id);
    var latestReview = getLatestDailyReviewDate(reg.id);
    var nextScheduled = getNextScheduledDate(reg.id);

    return {
      registration_id: reg.id,
      patient_id: patient ? patient.id : '',
      full_name: patient ? patient.full_name : '',
      national_id: patient ? patient.national_id : '',
      hospital_patient_id: patient ? patient.hospital_patient_id : '',
      age: patient ? patient.age : 0,
      gender: patient ? patient.gender : '',
      gender_ar: patient ? _genderAr(patient.gender) : '',
      supervising_doctor: patient ? patient.supervising_doctor : '',
      hospital_department: dept ? dept.name : '',
      department_id: reg.department_id,
      registration_date: reg.registration_date,
      status: reg.status,
      diagnoses_count: diags.length,
      latest_daily_review: latestReview,
      next_scheduled_review: nextScheduled
    };
  });
}


// ════════════════════════════════════════════════════════════════════════════
// 28. API — ENRICHED SCHEDULED REVIEWS LIST
// ════════════════════════════════════════════════════════════════════════════

function getEnrichedScheduledReviews(filters) {
  var list = getAllScheduledReviews(filters);
  return list.map(function (s) {
    var rdx = getRegistrationDiagnosisById(s.registration_diagnosis_id);
    var reg = rdx ? _find(REGISTRATIONS, rdx.registration_id) : null;
    var patient = reg ? _find(PATIENTS, reg.patient_id) : null;
    var dept = reg ? _find(DEPARTMENTS, reg.department_id) : null;

    // Get previous daily review info
    var prevReviews = rdx ? getDailyReviewsByRegistrationDiagnosisId(rdx.id) : [];
    var latestReview = prevReviews.length > 0 ? prevReviews[0] : null;

    return {
      id: s.id,
      registration_diagnosis_id: s.registration_diagnosis_id,
      registration_id: reg ? reg.id : '',
      patient_id: patient ? patient.id : '',
      patient_name: patient ? patient.full_name : '',
      national_id: patient ? patient.national_id : '',
      hospital_patient_id: patient ? patient.hospital_patient_id : '',
      age: patient ? patient.age : 0,
      gender: patient ? patient.gender : '',
      gender_ar: patient ? _genderAr(patient.gender) : '',
      supervising_doctor: patient ? patient.supervising_doctor : '',
      department: dept ? dept.name : '',
      department_id: reg ? reg.department_id : '',
      diagnosis_name: rdx ? lookup('diagnoses', rdx.diagnosis_id) : '',
      wound_type: rdx ? lookup('wound_types', rdx.wound_type_id) : '',
      current_dressing: rdx ? lookup('dressings', rdx.initial_dressing_id) : '',
      current_medication: rdx ? lookup('medications', rdx.initial_medication_id) : '',
      scheduled_date: s.scheduled_date,
      created_date: s.created_at ? s.created_at.split('T')[0] : '',
      created_by: lookup('users', s.created_by_id) || '',
      notes: s.notes,
      status: s.status,
      completed_at: s.completed_at,
      source_daily_review_id: s.source_daily_review_id,
      // Previous review data
      last_review_date: latestReview ? latestReview.review_date : null,
      previous_wound_description: latestReview ? latestReview.wound_description : '',
      previous_wound_changes: latestReview ? latestReview.wound_changes : '',
      previous_dressing: latestReview && latestReview.dressing_id ? lookup('dressings', latestReview.dressing_id) : '',
      previous_medication: latestReview && latestReview.medication_id ? lookup('medications', latestReview.medication_id) : '',
      previous_status: latestReview ? latestReview.status : ''
    };
  });
}


// ════════════════════════════════════════════════════════════════════════════
// 29. API — URL & MISC HELPERS
// ════════════════════════════════════════════════════════════════════════════

function getUrlParam(key) {
  try {
    var params = new URLSearchParams(window.location.search);
    return params.get(key) || '';
  } catch (e) {
    return '';
  }
}

function getCurrentUser() {
  return _find(USERS, CURRENT_USER_ID);
}


// ════════════════════════════════════════════════════════════════════════════
// 30. AUDIT LOG
// ════════════════════════════════════════════════════════════════════════════

function _addAuditLog(action, entityType, entityId, details) {
  AUDIT_LOG.push({
    id: _genId('auditLog'),
    user_id: CURRENT_USER_ID,
    action: action,
    entity_type: entityType,
    entity_id: entityId,
    details: details || {},
    created_at: _now()
  });
}


// ════════════════════════════════════════════════════════════════════════════
// 31. DAILY REVIEW STATUS HELPERS
// ════════════════════════════════════════════════════════════════════════════

var DAILY_REVIEW_STATUS_MAP = {
  'IMPROVED_DISCHARGE':  { label: 'تحسن — ترخيص', labelEn: 'Improved – Discharge', badgeClass: 'badge-light-success', icon: 'bi-check-circle-fill' },
  'IMPROVED_FOLLOWUP':   { label: 'تحسن — متابعة', labelEn: 'Improved – Follow-up', badgeClass: 'badge-light-primary', icon: 'bi-arrow-repeat' },
  'WORSENED':            { label: 'تفاقم', labelEn: 'Worsened', badgeClass: 'badge-light-warning', icon: 'bi-exclamation-triangle-fill' },
  'DECEASED':            { label: 'وفاة', labelEn: 'Deceased', badgeClass: 'badge-light-danger', icon: 'bi-x-circle-fill' }
};

var SCHEDULED_REVIEW_STATUS_MAP = {
  'SCHEDULED':  { label: 'مجدولة', badgeClass: 'badge-light-primary', icon: 'bi-calendar-event' },
  'COMPLETED':  { label: 'مكتملة', badgeClass: 'badge-light-success', icon: 'bi-check-circle-fill' },
  'CANCELLED':  { label: 'ملغاة', badgeClass: 'badge-light-danger', icon: 'bi-x-circle-fill' },
  'MISSED':     { label: 'فائتة', badgeClass: 'badge-light-warning', icon: 'bi-exclamation-triangle-fill' }
};

var REQUEST_STATUS_MAP = {
  'PENDING':    { label: 'قيد المراجعة', badgeClass: 'badge-light-warning', icon: 'bi-clock-fill' },
  'APPROVED':   { label: 'تمت الموافقة', badgeClass: 'badge-light-success', icon: 'bi-check-circle-fill' },
  'REJECTED':   { label: 'مرفوض', badgeClass: 'badge-light-danger', icon: 'bi-x-circle-fill' },
  'CANCELLED':  { label: 'ملغي', badgeClass: 'badge-light-secondary', icon: 'bi-slash-circle' }
};


// ════════════════════════════════════════════════════════════════════════════
// 32. GLOBAL EXPORT
// ════════════════════════════════════════════════════════════════════════════

global.WCCMS = {
  api: {
    // Hospital
    searchHospitalPatient: searchHospitalPatient,

    // Patients
    getPatientById: getPatientById,
    getPatientByNationalId: getPatientByNationalId,
    getPatientByHospitalId: getPatientByHospitalId,
    getAllPatients: getAllPatients,

    // Registration Requests
    getRequestById: getRequestById,
    getAllRequests: getAllRequests,
    submitRequest: submitRequest,
    approveRequest: approveRequest,
    rejectRequest: rejectRequest,
    cancelRequest: cancelRequest,

    // Registrations
    getRegistrationById: getRegistrationById,
    getRegistrationsByPatientId: getRegistrationsByPatientId,
    getAllActiveRegistrations: getAllActiveRegistrations,
    getAllRegistrations: getAllRegistrations,
    dischargeRegistration: dischargeRegistration,

    // Registration Diagnoses
    getDiagnosesByRegistrationId: getDiagnosesByRegistrationId,
    getRegistrationDiagnosisById: getRegistrationDiagnosisById,

    // Registration PMH
    getPMHByRegistrationId: getPMHByRegistrationId,

    // Procedures
    getProceduresByRegistrationDiagnosisId: getProceduresByRegistrationDiagnosisId,
    getProceduresByRegistrationId: getProceduresByRegistrationId,

    // Daily Reviews
    getDailyReviewsByRegistrationDiagnosisId: getDailyReviewsByRegistrationDiagnosisId,
    getDailyReviewsByRegistrationId: getDailyReviewsByRegistrationId,
    getLatestDailyReviewDate: getLatestDailyReviewDate,
    createDailyReview: createDailyReview,

    // Scheduled Reviews
    getScheduledReviewsByRegistrationDiagnosisId: getScheduledReviewsByRegistrationDiagnosisId,
    getScheduledReviewsByRegistrationId: getScheduledReviewsByRegistrationId,
    getNextScheduledDate: getNextScheduledDate,
    getAllScheduledReviews: getAllScheduledReviews,
    createScheduledReview: createScheduledReview,
    completeScheduledReview: completeScheduledReview,
    cancelScheduledReview: cancelScheduledReview,

    // Patient History
    getPatientHistory: getPatientHistory,

    // Enriched Views (pre-joined for page controllers)
    getEnrichedRegistration: getEnrichedRegistration,
    getEnrichedActiveRegistrations: getEnrichedActiveRegistrations,
    getEnrichedScheduledReviews: getEnrichedScheduledReviews,

    // Lookups
    lookup: lookup,
    getActiveLookups: getActiveLookups,
    addLookupItem: addLookupItem,
    updateLookupItem: updateLookupItem,

    // User / Session
    getCurrentUser: getCurrentUser,

    // URL helpers
    getUrlParam: getUrlParam,

    // Date helpers
    formatDate: _formatDate,
    formatDateTime: _formatDateTime,
    today: function () { return _today; },
    genderAr: _genderAr
  },

  // Status maps for UI rendering
  DAILY_REVIEW_STATUS: DAILY_REVIEW_STATUS_MAP,
  SCHEDULED_REVIEW_STATUS: SCHEDULED_REVIEW_STATUS_MAP,
  REQUEST_STATUS: REQUEST_STATUS_MAP,

  // Direct table access (for admin page)
  _tables: _tables,
  _raw: {
    USERS: USERS,
    DEPARTMENTS: DEPARTMENTS,
    PATIENTS: PATIENTS,
    REGISTRATION_REQUESTS: REGISTRATION_REQUESTS,
    REGISTRATIONS: REGISTRATIONS,
    REGISTRATION_PMH: REGISTRATION_PMH,
    REGISTRATION_DIAGNOSES: REGISTRATION_DIAGNOSES,
    REGISTRATION_DIAGNOSIS_PROCEDURES: REGISTRATION_DIAGNOSIS_PROCEDURES,
    DAILY_REVIEWS: DAILY_REVIEWS,
    SCHEDULED_REVIEWS: SCHEDULED_REVIEWS,
    AUDIT_LOG: AUDIT_LOG,
    HOSPITAL_PATIENTS: HOSPITAL_PATIENTS
  }
};

})(window);
