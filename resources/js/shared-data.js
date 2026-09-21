/**
 * ============================================================================
 * WCCMS â€” Wound Care Clinic Management System
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

let WCCMS;

(function (global) {

// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 1. MASTER / LOOKUP TABLES
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

var USERS = [
  { id: 'u001', full_name: 'ط£. ظپط§ط·ظ…ط© ط§ظ„ط²ظ‡ط±ط§ظ†ظٹ', username: 'fatimah.z', email: 'fatimah@wccms.sa', password: '', role: 'NURSE', is_active: true },
  { id: 'u002', full_name: 'ط¯. ط£ط­ظ…ط¯ ط¹ط¨ط¯ ط§ظ„ط±ط­ظ…ظ†', username: 'ahmad.a', email: 'ahmad@wccms.sa', password: '', role: 'ADMIN', is_active: true },
  { id: 'u003', full_name: 'ط£. ظ…ط±ظٹظ… ط§ظ„ط³ط§ظ„ظ…', username: 'mariam.s', email: 'mariam@wccms.sa', password: '', role: 'NURSE', is_active: true }
];

var DEPARTMENTS = [
  { id: 'dept001', name: 'ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ…ط±ظƒط²ط© (ICU)', is_active: true },
  { id: 'dept002', name: 'ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ‚ظ„ط¨ظٹط© (CCU)', is_active: true },
  { id: 'dept003', name: 'ط§ظ„ط¨ط§ط·ظ†ظٹط© (Medical)', is_active: true },
  { id: 'dept004', name: 'ط§ظ„ط¬ط±ط§ط­ط© (Surgery)', is_active: true },
  { id: 'dept005', name: 'ط§ظ„ط¹ط¸ط§ظ… (Orthopedics)', is_active: true },
  { id: 'dept006', name: 'ط§ظ„ط£ط¹طµط§ط¨ (Neurology)', is_active: true },
  { id: 'dept007', name: 'ط§ظ„ط£ظˆط±ط§ظ… (Oncology)', is_active: true },
  { id: 'dept008', name: 'ط¹ظٹط§ط¯ط© ط§ظ„ط¹ظ†ط§ظٹط© ط¨ط§ظ„ط¬ط±ظˆط­', is_active: true }
];

var DIAGNOSES = [
  { id: 'dx001', name: 'ظ‚ط¯ظ… ط³ظƒط±ظٹ (Diabetic Foot Ulcer)', is_active: true },
  { id: 'dx002', name: 'ظ‚ط±ط­ط© ظپط±ط§ط´ (Pressure Ulcer)', is_active: true },
  { id: 'dx003', name: 'ظ‚ط±ط­ط© ظˆط±ظٹط¯ظٹط© (Venous Leg Ulcer)', is_active: true },
  { id: 'dx004', name: 'ط¬ط±ط­ ظ…ط§ ط¨ط¹ط¯ ط§ظ„ط¬ط±ط§ط­ط© (Post-Op Wound)', is_active: true },
  { id: 'dx005', name: 'ط¬ط±ط­ ظ…ظˆط¶ط¹ ط§ظ„ط¥ط¨ط±ط© ط§ظ„ظˆط±ظٹط¯ظٹط© (IV Site Wound)', is_active: true },
  { id: 'dx006', name: 'ط¬ط±ط­ ظ…ظ†ظپطھط­ (Open Abdominal Wound)', is_active: true },
  { id: 'dx007', name: 'ط¬ط±ط­ ظƒط¯ظ…ظٹ (Contusion Wound)', is_active: true },
  { id: 'dx008', name: 'ظ‚ط±ط­ط© ظƒط§ط­ظ„ (Ankle Wound)', is_active: true },
  { id: 'dx009', name: 'ط¬ط±ط­ ظ…ظˆط¶ط¹ ط§ظ„ظ‚ط³ط·ط±ط© (Catheter Site Wound)', is_active: true },
  { id: 'dx010', name: 'ط¬ط±ط­ ظ…ط§ ط¨ط¹ط¯ ط§ط³طھط¦طµط§ظ„ ط§ظ„ط«ط¯ظٹ (Post-Mastectomy Wound)', is_active: true },
  { id: 'dx011', name: 'ط¬ط±ط­ ظپط؛ط±ط© ط§ظ„ظ‚ظˆظ„ظˆظ† (Post-Op Colostomy Wound)', is_active: true },
  { id: 'dx012', name: 'ط¬ط±ط­ طھط«ط¨ظٹطھ ط§ظ„ظƒط§ط­ظ„ ط§ظ„ط¬ط±ط§ط­ظٹ (Ankle Fixation Wound)', is_active: true },
  { id: 'dx013', name: 'ط¬ط±ط­ ط¬ط±ط§ط­ظٹ ط§ظ„ط¨ط·ظ† (Abdominal Surgical Wound)', is_active: true },
  { id: 'dx014', name: 'ط¬ط±ط­ ط¬ط±ط§ط­ظٹ ط§ظ„طھظ‡ط§ط¨ظٹ (Inflammatory Surgical Wound)', is_active: true }
];

var WOUND_TYPES = [
  { id: 'wt001', name: 'ظ†ط®ط±ظٹ (Necrotic)', is_active: true },
  { id: 'wt002', name: 'ظ…طھط³ط§ظ‚ط· (Sloughy)', is_active: true },
  { id: 'wt003', name: 'ظ†ط¶ط­ظٹ (Exudative)', is_active: true },
  { id: 'wt004', name: 'ط¬ط±ط§ط­ظٹ (Surgical)', is_active: true },
  { id: 'wt005', name: 'ط¥ظ‚ظپط§ط±ظٹ (Ischemic)', is_active: true },
  { id: 'wt006', name: 'ط±ط¶ظٹ (Traumatic)', is_active: true },
  { id: 'wt007', name: 'ظ…ظ†ظپطھط­ (Open)', is_active: true },
  { id: 'wt008', name: 'ظ…ط±ط­ظ„ط© ط£ظˆظ„ظ‰ (Stage I)', is_active: true },
  { id: 'wt009', name: 'ظ…ط±ط­ظ„ط© ط«ط§ظ†ظٹط© (Stage II)', is_active: true },
  { id: 'wt010', name: 'ظ…ط±ط­ظ„ط© ط«ط§ظ„ط«ط© (Stage III)', is_active: true },
  { id: 'wt011', name: 'ط¬ط±ط§ط­ظٹ طµط؛ظٹط± (Minor Surgical)', is_active: true }
];

var DRESSINGS = [
  { id: 'drs001', name: 'ط±ط؛ظˆظٹط© (Foam)', is_active: true },
  { id: 'drs002', name: 'ظ‡ظٹط¯ط±ظˆظƒظˆظ„ظˆظٹط¯ (Hydrocolloid)', is_active: true },
  { id: 'drs003', name: 'ظ‡ظٹط¯ط±ظˆط¬ظٹظ„ (Hydrogel)', is_active: true },
  { id: 'drs004', name: 'ط£ظ„ط¬ظٹظ†ط§طھ (Alginate)', is_active: true },
  { id: 'drs005', name: 'ظپط¶ط© ظ…ط¶ط§ط¯ط© ظ„ظ„ظ…ظٹظƒط±ظˆط¨ط§طھ (Silver)', is_active: true },
  { id: 'drs006', name: 'ط³ظٹظ„ظٹظƒظˆظ† (Silicone)', is_active: true },
  { id: 'drs007', name: 'ط¶ط؛ط· ط³ظ„ط¨ظٹ (NPWT)', is_active: true },
  { id: 'drs008', name: 'ط´ط§ط´ ظپط§ط²ظ„ظٹظ† (Vaseline Gauze)', is_active: true }
];

var MEDICATIONS = [
  { id: 'med001', name: 'ظ…ط±ظ‡ظ… ظ…ط¶ط§ط¯ ط­ظٹظˆظٹ (Antibiotic Ointment)', is_active: true },
  { id: 'med002', name: 'ط؛ط³ظˆظ„ ظ…ظ„ط­ظٹ (Normal Saline)', is_active: true },
  { id: 'med003', name: 'ط¨ظٹطھط§ط¯ظٹظ† (Betadine)', is_active: true },
  { id: 'med004', name: 'ط¹ط³ظ„ ط·ط¨ظٹ (Manuka Honey)', is_active: true },
  { id: 'med005', name: 'ظƒظˆظ„ط§ط¬ظٹظ† (Collagen)', is_active: true }
];

var PROCEDURES = [
  { id: 'proc001', name: 'طھظ†ط¶ظٹط± ط§ظ„ط¬ط±ط­ (Debridement)', is_active: true },
  { id: 'proc002', name: 'طھظ‚ظٹظٹظ… ط§ظ„ط¬ط±ط­ (Wound Assessment)', is_active: true },
  { id: 'proc003', name: 'طھط؛ظٹظٹط± ط§ظ„ط¶ظ…ط§ط¯ط© (Dressing Change)', is_active: true },
  { id: 'proc004', name: 'ظ…ط³ط­ط© ظ…ط²ط±ط¹ط© (Swab Culture)', is_active: true },
  { id: 'proc005', name: 'طھط·ط¨ظٹظ‚ ط§ظ„ط¶ط؛ط· ط§ظ„ط³ظ„ط¨ظٹ (NPWT Application)', is_active: true },
  { id: 'proc006', name: 'طھظ†ط¸ظٹظپ ظˆطھط·ظ‡ظٹط± ط§ظ„ط¬ط±ط­ (Wound Irrigation & Cleansing)', is_active: true },
  { id: 'proc007', name: 'ظ‚ظٹط§ط³ ط£ط¨ط¹ط§ط¯ ظˆط¹ظ…ظ‚ ط§ظ„ط¬ط±ط­ (Wound Measurement & Staging)', is_active: true },
  { id: 'proc008', name: 'طھط·ط¨ظٹظ‚ ط¶ظ…ط§ط¯ط© ط§ظ„ظƒظˆظ„ط§ط¬ظٹظ† (Collagen Application)', is_active: true },
  { id: 'proc009', name: 'ط¹ظ„ط§ط¬ ط¨ط§ظ„ط¶ط؛ط· ط§ظ„ط¶ط§ط؛ط· (Compression Therapy)', is_active: true },
  { id: 'proc010', name: 'ط¥ط²ط§ظ„ط© ط£ظ†ط³ط¬ط© ظ…ظٹطھط© ط­ط§ط¯ط© (Sharp Debridement)', is_active: true },
  { id: 'proc011', name: 'ظƒظٹ ط§ظ„ط£ظ†ط³ط¬ط© ط§ظ„ط²ط§ط¦ط¯ط© (Cauterization of Hypergranulation)', is_active: true },
  { id: 'proc012', name: 'طھط·ط¨ظٹظ‚ ط¨ط¯ط§ط¦ظ„ ط§ظ„ط¬ظ„ط¯ ط§ظ„ط£ط­ظٹط§ط¦ظٹط© (Skin Substitute Application)', is_active: true },
  { id: 'proc013', name: 'ظپط­طµ ط§ظ„طھط±ظˆظٹط© ظˆط§ظ„طھط£ظƒط³ط¬ ط§ظ„ظ…ظˆط¶ط¹ظٹ (Vascular & Perfusion Assessment)', is_active: true }
];

var PMH_LIST = [
  { id: 'pmh001', name: 'ط§ظ„ط³ظƒط±ظٹ (Diabetes Mellitus)', is_active: true },
  { id: 'pmh002', name: 'ط¶ط؛ط· ط§ظ„ط¯ظ… (Hypertension)', is_active: true },
  { id: 'pmh003', name: 'ط§ظ„ظپط´ظ„ ط§ظ„ظƒظ„ظˆظٹ (Renal Failure)', is_active: true },
  { id: 'pmh004', name: 'ط£ظ…ط±ط§ط¶ ط§ظ„ظ‚ظ„ط¨ (Cardiac Disease)', is_active: true },
  { id: 'pmh005', name: 'ط§ظ„ط³ظƒطھط© ط§ظ„ط¯ظ…ط§ط؛ظٹط© (Stroke)', is_active: true },
  { id: 'pmh006', name: 'ط§ظ„ط¨ط¯ط§ظ†ط© (Obesity)', is_active: true },
  { id: 'pmh007', name: 'ط§ظ„ط³ط±ط·ط§ظ† (Oncology Patient)', is_active: true },
  { id: 'pmh008', name: 'ظƒظٹظ…ط§ظˆظٹ (Chemotherapy)', is_active: true },
  { id: 'pmh009', name: 'ظƒط³ط± ط§ظ„ظˆط±ظƒ (Hip Fracture)', is_active: true },
  { id: 'pmh010', name: 'ظƒط³ط± ط§ظ„ظƒط§ط­ظ„ (Ankle Fracture)', is_active: true },
  { id: 'pmh011', name: 'ط§ط­طھط´ط§ط، ط¹ط¶ظ„ط© ط§ظ„ظ‚ظ„ط¨ (MI)', is_active: true },
  { id: 'pmh012', name: 'ط³ط±ط·ط§ظ† ط§ظ„ظ‚ظˆظ„ظˆظ† (Colon Cancer)', is_active: true },
  { id: 'pmh013', name: 'ط§ظ„ط±ط¨ظˆ (Asthma)', is_active: true }
];


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 2. HOSPITAL SYSTEM SIMULATION (External â€” not in our DB)
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

var HOSPITAL_PATIENTS = [
  { hospital_patient_id: 'HP-10025', national_id: '1087654321', full_name: 'ط£ط­ظ…ط¯ ظ…ط­ظ…ط¯ ط¹ظ„ظٹ', gender: 'MALE', age: 52, supervising_doctor: 'ط¯. ظ…ط­ظ…ط¯ ط®ط§ظ„ط¯', hospital_department: 'ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ…ط±ظƒط²ط© (ICU)', hospital_admission_date: '2026-08-31' },
  { hospital_patient_id: 'HP-10031', national_id: '1076543210', full_name: 'ظپط§ط·ظ…ط© ط³ط¹ط¯ ط§ظ„ط؛ط§ظ…ط¯ظٹ', gender: 'FEMALE', age: 67, supervising_doctor: 'ط¯. ط³ط§ط±ط© ط§ظ„ط¹طھظٹط¨ظٹ', hospital_department: 'ط§ظ„ط¨ط§ط·ظ†ظٹط© (Medical)', hospital_admission_date: '2026-09-01' },
  { hospital_patient_id: 'HP-10044', national_id: '1065432109', full_name: 'ط®ط§ظ„ط¯ ط¹ط¨ط¯ط§ظ„ظ„ظ‡ ط§ظ„ط´ظ…ط±ظٹ', gender: 'MALE', age: 44, supervising_doctor: 'ط¯. ط£ط­ظ…ط¯ ط§ظ„ط´ط±ظٹظپ', hospital_department: 'ط§ظ„ط¬ط±ط§ط­ط© (Surgery)', hospital_admission_date: '2026-09-01' },
  { hospital_patient_id: 'HP-10058', national_id: '1054321098', full_name: 'ظ†ظˆط±ط© ظ…ط­ظ…ط¯ ط§ظ„ط¹طھظٹط¨ظٹ', gender: 'FEMALE', age: 38, supervising_doctor: 'ط¯. ظ†ظˆط±ط© ط§ظ„ط­ط±ط¨ظٹ', hospital_department: 'ط§ظ„ط£ظˆط±ط§ظ… (Oncology)', hospital_admission_date: '2026-09-05' },
  { hospital_patient_id: 'HP-10067', national_id: '1043210987', full_name: 'ط¹ط¨ط¯ط§ظ„ط±ط­ظ…ظ† ظٹظˆط³ظپ ط§ظ„ظ‚ط­ط·ط§ظ†ظٹ', gender: 'MALE', age: 71, supervising_doctor: 'ط¯. ط¹ط¨ط¯ط§ظ„ظ„ظ‡ ط§ظ„ظ…ط§ظ„ظƒظٹ', hospital_department: 'ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ‚ظ„ط¨ظٹط© (CCU)', hospital_admission_date: '2026-08-29' },
  { hospital_patient_id: 'HP-10079', national_id: '1032109876', full_name: 'ظ…ظ†ظ‰ ط¹ظ„ظٹ ط§ظ„ط²ظ‡ط±ط§ظ†ظٹ', gender: 'FEMALE', age: 55, supervising_doctor: 'ط¯. ظ…ط­ظ…ط¯ ط®ط§ظ„ط¯', hospital_department: 'ط§ظ„ط£ط¹طµط§ط¨ (Neurology)', hospital_admission_date: '2026-09-02' },
  { hospital_patient_id: 'HP-10085', national_id: '1021098765', full_name: 'ط³ظ„ط·ط§ظ† ط¹ظ…ط± ط§ظ„ط¯ظˆط³ط±ظٹ', gender: 'MALE', age: 60, supervising_doctor: 'ط¯. ط³ط§ط±ط© ط§ظ„ط¹طھظٹط¨ظٹ', hospital_department: 'ط§ظ„ط¹ط¸ط§ظ… (Orthopedics)', hospital_admission_date: '2026-09-03' },
  { hospital_patient_id: 'HP-10091', national_id: '1010987654', full_name: 'ط­ظ†ط§ظ† ط¥ط¨ط±ط§ظ‡ظٹظ… ط§ظ„ط­ط±ط¨ظٹ', gender: 'FEMALE', age: 49, supervising_doctor: 'ط¯. ط£ط­ظ…ط¯ ط§ظ„ط´ط±ظٹظپ', hospital_department: 'ط§ظ„ط¬ط±ط§ط­ط© (Surgery)', hospital_admission_date: '2026-08-28' },
  { hospital_patient_id: 'HP-10097', national_id: '1009876543', full_name: 'ط¨ط¯ط± ظ…ط­ظ…ط¯ ط§ظ„ط´ظ‡ط±ظٹ', gender: 'MALE', age: 33, supervising_doctor: 'ط¯. ظ†ظˆط±ط© ط§ظ„ط­ط±ط¨ظٹ', hospital_department: 'ط§ظ„ط¨ط§ط·ظ†ظٹط© (Medical)', hospital_admission_date: '2026-09-05' },
  { hospital_patient_id: 'HP-10103', national_id: '1098765432', full_name: 'ط±ظ†ط§ ط®ط§ظ„ط¯ ط§ظ„ط³ط¨ظٹط¹ظٹ', gender: 'FEMALE', age: 41, supervising_doctor: 'ط¯. ط¹ط¨ط¯ط§ظ„ظ„ظ‡ ط§ظ„ظ…ط§ظ„ظƒظٹ', hospital_department: 'ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ…ط±ظƒط²ط© (ICU)', hospital_admission_date: '2026-08-30' },
  { hospital_patient_id: 'HP-10112', national_id: '1087654300', full_name: 'ظ…ط§ط¬ط¯ ط³ط¹ظٹط¯ ط§ظ„ط¨ظ‚ظ…ظٹ', gender: 'MALE', age: 63, supervising_doctor: 'ط¯. ظ…ط­ظ…ط¯ ط®ط§ظ„ط¯', hospital_department: 'ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ‚ظ„ط¨ظٹط© (CCU)', hospital_admission_date: '2026-08-27' },
  { hospital_patient_id: 'HP-10119', national_id: '1076543198', full_name: 'ط³ظ‡ظٹظ„ط© ط¹ظ…ط± ط§ظ„ط¬ظ‡ظ†ظٹ', gender: 'FEMALE', age: 57, supervising_doctor: 'ط¯. ط³ط§ط±ط© ط§ظ„ط¹طھظٹط¨ظٹ', hospital_department: 'ط§ظ„ط£ظˆط±ط§ظ… (Oncology)', hospital_admission_date: '2026-09-02' },
  { hospital_patient_id: 'HP-10125', national_id: '1055443322', full_name: 'ظ…ط­ظ…ط¯ ط¹ط¨ط¯ط§ظ„ظƒط±ظٹظ… ط§ظ„ط¹ظ†ط²ظٹ', gender: 'MALE', age: 62, supervising_doctor: 'ط¯. ظ…ط­ظ…ط¯ ط®ط§ظ„ط¯', hospital_department: 'ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ…ط±ظƒط²ط© (ICU)', hospital_admission_date: '2026-09-01' },
  { hospital_patient_id: 'HP-10130', national_id: '1044332211', full_name: 'ط±ظٹظ… ط®ط§ظ„ط¯ ط§ظ„ط´ط±ظٹظپ', gender: 'FEMALE', age: 51, supervising_doctor: 'ط¯. ط³ط§ط±ط© ط§ظ„ط¹طھظٹط¨ظٹ', hospital_department: 'ط§ظ„ط¨ط§ط·ظ†ظٹط© (Medical)', hospital_admission_date: '2026-08-30' },
  { hospital_patient_id: 'HP-10135', national_id: '1033221100', full_name: 'طھط±ظƒظٹ ظ…ظ†طµظˆط± ط§ظ„ط¹طھظٹط¨ظٹ', gender: 'MALE', age: 39, supervising_doctor: 'ط¯. ط£ط­ظ…ط¯ ط§ظ„ط´ط±ظٹظپ', hospital_department: 'ط§ظ„ط¬ط±ط§ط­ط© (Surgery)', hospital_admission_date: '2026-09-02' },
  { hospital_patient_id: 'HP-10140', national_id: '1022110099', full_name: 'ط¹ط§ط¦ط´ط© طµط§ظ„ط­ ط§ظ„ط؛ط§ظ…ط¯ظٹ', gender: 'FEMALE', age: 70, supervising_doctor: 'ط¯. ط¹ط¨ط¯ط§ظ„ظ„ظ‡ ط§ظ„ظ…ط§ظ„ظƒظٹ', hospital_department: 'ط§ظ„ط£ط¹طµط§ط¨ (Neurology)', hospital_admission_date: '2026-09-01' },
  { hospital_patient_id: 'HP-10145', national_id: '1011009988', full_name: 'ظپظٹطµظ„ ط³ط¹ط¯ ط§ظ„ظ…ط·ظٹط±ظٹ', gender: 'MALE', age: 42, supervising_doctor: 'ط¯. ظ†ظˆط±ط© ط§ظ„ط­ط±ط¨ظٹ', hospital_department: 'ط§ظ„ط¹ط¸ط§ظ… (Orthopedics)', hospital_admission_date: '2026-09-03' },
  { hospital_patient_id: 'HP-10999', national_id: '1099887766', full_name: 'ط¹ظ„ظٹ ط­ط³ظ† ط§ظ„ط²ظ‡ط±ط§ظ†ظٹ', gender: 'MALE', age: 58, supervising_doctor: 'ط¯. ط£ط­ظ…ط¯ ط§ظ„ط´ط±ظٹظپ', hospital_department: 'ط§ظ„ط¬ط±ط§ط­ط© (Surgery)', hospital_admission_date: '2026-04-10' },
  // Extra patients not yet registered in wound care
  { hospital_patient_id: 'HP-20001', national_id: '987654321', full_name: 'ط³ط¹ظˆط¯ ط®ط§ظ„ط¯ ط§ظ„ظ…ظ†طµظˆط±', gender: 'MALE', age: 45, supervising_doctor: 'ط¯. ظ…ط­ظ…ط¯ ط®ط§ظ„ط¯', hospital_department: 'ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ…ط±ظƒط²ط© (ICU)', hospital_admission_date: '2026-09-04' },
  { hospital_patient_id: 'HP-20002', national_id: '123456789', full_name: 'ظ‡ظ†ط¯ ط¹ط¨ط¯ط§ظ„ط¹ط²ظٹط² ط§ظ„ط±ط§ط´ط¯', gender: 'FEMALE', age: 36, supervising_doctor: 'ط¯. ط³ط§ط±ط© ط§ظ„ط¹طھظٹط¨ظٹ', hospital_department: 'ط§ظ„ط£ظˆط±ط§ظ… (Oncology)', hospital_admission_date: '2026-09-03' },
  { hospital_patient_id: 'HP-20003', national_id: '111222333', full_name: 'ظٹط§ط³ط± ظ…ط­ظ…ط¯ ط§ظ„ط­ط§ط±ط«ظٹ', gender: 'MALE', age: 55, supervising_doctor: 'ط¯. ط£ط­ظ…ط¯ ط§ظ„ط´ط±ظٹظپ', hospital_department: 'ط§ظ„ط¬ط±ط§ط­ط© (Surgery)', hospital_admission_date: '2026-09-02' },
  { hospital_patient_id: 'HP-20004', national_id: '444555666', full_name: 'ظ„ظٹظ†ط§ ط£ط­ظ…ط¯ ط§ظ„ط¹ظ…ط±ظٹ', gender: 'FEMALE', age: 29, supervising_doctor: 'ط¯. ظ†ظˆط±ط© ط§ظ„ط­ط±ط¨ظٹ', hospital_department: 'ط§ظ„ط¹ط¸ط§ظ… (Orthopedics)', hospital_admission_date: '2026-09-05' }
];


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 3. PATIENTS (in our system â€” synced from hospital)
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

var PATIENTS = [
  { id: 'pat001', hospital_patient_id: 'HP-10025', national_id: '1087654321', full_name: 'ط£ط­ظ…ط¯ ظ…ط­ظ…ط¯ ط¹ظ„ظٹ', gender: 'MALE', age: 52, supervising_doctor: 'ط¯. ظ…ط­ظ…ط¯ ط®ط§ظ„ط¯', hospital_department: 'ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ…ط±ظƒط²ط© (ICU)', hospital_admission_date: '2026-08-31', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat002', hospital_patient_id: 'HP-10031', national_id: '1076543210', full_name: 'ظپط§ط·ظ…ط© ط³ط¹ط¯ ط§ظ„ط؛ط§ظ…ط¯ظٹ', gender: 'FEMALE', age: 67, supervising_doctor: 'ط¯. ط³ط§ط±ط© ط§ظ„ط¹طھظٹط¨ظٹ', hospital_department: 'ط§ظ„ط¨ط§ط·ظ†ظٹط© (Medical)', hospital_admission_date: '2026-09-01', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat003', hospital_patient_id: 'HP-10044', national_id: '1065432109', full_name: 'ط®ط§ظ„ط¯ ط¹ط¨ط¯ط§ظ„ظ„ظ‡ ط§ظ„ط´ظ…ط±ظٹ', gender: 'MALE', age: 44, supervising_doctor: 'ط¯. ط£ط­ظ…ط¯ ط§ظ„ط´ط±ظٹظپ', hospital_department: 'ط§ظ„ط¬ط±ط§ط­ط© (Surgery)', hospital_admission_date: '2026-09-01', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat004', hospital_patient_id: 'HP-10058', national_id: '1054321098', full_name: 'ظ†ظˆط±ط© ظ…ط­ظ…ط¯ ط§ظ„ط¹طھظٹط¨ظٹ', gender: 'FEMALE', age: 38, supervising_doctor: 'ط¯. ظ†ظˆط±ط© ط§ظ„ط­ط±ط¨ظٹ', hospital_department: 'ط§ظ„ط£ظˆط±ط§ظ… (Oncology)', hospital_admission_date: '2026-09-05', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat005', hospital_patient_id: 'HP-10067', national_id: '1043210987', full_name: 'ط¹ط¨ط¯ط§ظ„ط±ط­ظ…ظ† ظٹظˆط³ظپ ط§ظ„ظ‚ط­ط·ط§ظ†ظٹ', gender: 'MALE', age: 71, supervising_doctor: 'ط¯. ط¹ط¨ط¯ط§ظ„ظ„ظ‡ ط§ظ„ظ…ط§ظ„ظƒظٹ', hospital_department: 'ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ‚ظ„ط¨ظٹط© (CCU)', hospital_admission_date: '2026-08-29', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat006', hospital_patient_id: 'HP-10079', national_id: '1032109876', full_name: 'ظ…ظ†ظ‰ ط¹ظ„ظٹ ط§ظ„ط²ظ‡ط±ط§ظ†ظٹ', gender: 'FEMALE', age: 55, supervising_doctor: 'ط¯. ظ…ط­ظ…ط¯ ط®ط§ظ„ط¯', hospital_department: 'ط§ظ„ط£ط¹طµط§ط¨ (Neurology)', hospital_admission_date: '2026-09-02', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat007', hospital_patient_id: 'HP-10085', national_id: '1021098765', full_name: 'ط³ظ„ط·ط§ظ† ط¹ظ…ط± ط§ظ„ط¯ظˆط³ط±ظٹ', gender: 'MALE', age: 60, supervising_doctor: 'ط¯. ط³ط§ط±ط© ط§ظ„ط¹طھظٹط¨ظٹ', hospital_department: 'ط§ظ„ط¹ط¸ط§ظ… (Orthopedics)', hospital_admission_date: '2026-09-03', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat008', hospital_patient_id: 'HP-10091', national_id: '1010987654', full_name: 'ط­ظ†ط§ظ† ط¥ط¨ط±ط§ظ‡ظٹظ… ط§ظ„ط­ط±ط¨ظٹ', gender: 'FEMALE', age: 49, supervising_doctor: 'ط¯. ط£ط­ظ…ط¯ ط§ظ„ط´ط±ظٹظپ', hospital_department: 'ط§ظ„ط¬ط±ط§ط­ط© (Surgery)', hospital_admission_date: '2026-08-28', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat009', hospital_patient_id: 'HP-10097', national_id: '1009876543', full_name: 'ط¨ط¯ط± ظ…ط­ظ…ط¯ ط§ظ„ط´ظ‡ط±ظٹ', gender: 'MALE', age: 33, supervising_doctor: 'ط¯. ظ†ظˆط±ط© ط§ظ„ط­ط±ط¨ظٹ', hospital_department: 'ط§ظ„ط¨ط§ط·ظ†ظٹط© (Medical)', hospital_admission_date: '2026-09-05', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat010', hospital_patient_id: 'HP-10103', national_id: '1098765432', full_name: 'ط±ظ†ط§ ط®ط§ظ„ط¯ ط§ظ„ط³ط¨ظٹط¹ظٹ', gender: 'FEMALE', age: 41, supervising_doctor: 'ط¯. ط¹ط¨ط¯ط§ظ„ظ„ظ‡ ط§ظ„ظ…ط§ظ„ظƒظٹ', hospital_department: 'ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ…ط±ظƒط²ط© (ICU)', hospital_admission_date: '2026-08-30', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat011', hospital_patient_id: 'HP-10112', national_id: '1087654300', full_name: 'ظ…ط§ط¬ط¯ ط³ط¹ظٹط¯ ط§ظ„ط¨ظ‚ظ…ظٹ', gender: 'MALE', age: 63, supervising_doctor: 'ط¯. ظ…ط­ظ…ط¯ ط®ط§ظ„ط¯', hospital_department: 'ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ‚ظ„ط¨ظٹط© (CCU)', hospital_admission_date: '2026-08-27', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat012', hospital_patient_id: 'HP-10119', national_id: '1076543198', full_name: 'ط³ظ‡ظٹظ„ط© ط¹ظ…ط± ط§ظ„ط¬ظ‡ظ†ظٹ', gender: 'FEMALE', age: 57, supervising_doctor: 'ط¯. ط³ط§ط±ط© ط§ظ„ط¹طھظٹط¨ظٹ', hospital_department: 'ط§ظ„ط£ظˆط±ط§ظ… (Oncology)', hospital_admission_date: '2026-09-02', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat013', hospital_patient_id: 'HP-10125', national_id: '1055443322', full_name: 'ظ…ط­ظ…ط¯ ط¹ط¨ط¯ط§ظ„ظƒط±ظٹظ… ط§ظ„ط¹ظ†ط²ظٹ', gender: 'MALE', age: 62, supervising_doctor: 'ط¯. ظ…ط­ظ…ط¯ ط®ط§ظ„ط¯', hospital_department: 'ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ…ط±ظƒط²ط© (ICU)', hospital_admission_date: '2026-09-01', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat014', hospital_patient_id: 'HP-10130', national_id: '1044332211', full_name: 'ط±ظٹظ… ط®ط§ظ„ط¯ ط§ظ„ط´ط±ظٹظپ', gender: 'FEMALE', age: 51, supervising_doctor: 'ط¯. ط³ط§ط±ط© ط§ظ„ط¹طھظٹط¨ظٹ', hospital_department: 'ط§ظ„ط¨ط§ط·ظ†ظٹط© (Medical)', hospital_admission_date: '2026-08-30', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat015', hospital_patient_id: 'HP-10135', national_id: '1033221100', full_name: 'طھط±ظƒظٹ ظ…ظ†طµظˆط± ط§ظ„ط¹طھظٹط¨ظٹ', gender: 'MALE', age: 39, supervising_doctor: 'ط¯. ط£ط­ظ…ط¯ ط§ظ„ط´ط±ظٹظپ', hospital_department: 'ط§ظ„ط¬ط±ط§ط­ط© (Surgery)', hospital_admission_date: '2026-09-02', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat016', hospital_patient_id: 'HP-10140', national_id: '1022110099', full_name: 'ط¹ط§ط¦ط´ط© طµط§ظ„ط­ ط§ظ„ط؛ط§ظ…ط¯ظٹ', gender: 'FEMALE', age: 70, supervising_doctor: 'ط¯. ط¹ط¨ط¯ط§ظ„ظ„ظ‡ ط§ظ„ظ…ط§ظ„ظƒظٹ', hospital_department: 'ط§ظ„ط£ط¹طµط§ط¨ (Neurology)', hospital_admission_date: '2026-09-01', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat017', hospital_patient_id: 'HP-10145', national_id: '1011009988', full_name: 'ظپظٹطµظ„ ط³ط¹ط¯ ط§ظ„ظ…ط·ظٹط±ظٹ', gender: 'MALE', age: 42, supervising_doctor: 'ط¯. ظ†ظˆط±ط© ط§ظ„ط­ط±ط¨ظٹ', hospital_department: 'ط§ظ„ط¹ط¸ط§ظ… (Orthopedics)', hospital_admission_date: '2026-09-03', last_synced_at: '2026-09-05T08:00:00Z' },
  { id: 'pat099', hospital_patient_id: 'HP-10999', national_id: '1099887766', full_name: 'ط¹ظ„ظٹ ط­ط³ظ† ط§ظ„ط²ظ‡ط±ط§ظ†ظٹ', gender: 'MALE', age: 58, supervising_doctor: 'ط¯. ط£ط­ظ…ط¯ ط§ظ„ط´ط±ظٹظپ', hospital_department: 'ط§ظ„ط¬ط±ط§ط­ط© (Surgery)', hospital_admission_date: '2026-04-10', last_synced_at: '2026-05-10T08:00:00Z' }
];


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 4. REGISTRATION REQUESTS
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

var _today = (function () { var d = new Date(); return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0'); })();

var REGISTRATION_REQUESTS = [
  // APPROVED â€” linked to existing registrations
  { id: 'rreq001', patient_id: 'pat001', submitted_by_id: 'u001', reviewed_by_id: 'u002', requested_at: '2026-08-31T08:00:00Z', reviewed_at: '2026-08-31T08:30:00Z', status: 'APPROVED', rejection_reason: null, notes: 'ظ…ط±ظٹط¶ ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ…ط±ظƒط²ط© ط¨ط­ط§ط¬ط© ظ„ط¹ظ„ط§ط¬ ط¬ط±ظˆط­ ط¹ط§ط¬ظ„', hospital_patient_id: 'HP-10025', national_id: '1087654321', patient_full_name: 'ط£ط­ظ…ط¯ ظ…ط­ظ…ط¯ ط¹ظ„ظٹ', gender: 'MALE', patient_age: 52, supervising_doctor: 'ط¯. ظ…ط­ظ…ط¯ ط®ط§ظ„ط¯', hospital_department: 'ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ…ط±ظƒط²ط© (ICU)', hospital_admission_date: '2026-08-31', hospital_data_fetched_at: '2026-08-31T07:55:00Z' },
  { id: 'rreq002', patient_id: 'pat002', submitted_by_id: 'u001', reviewed_by_id: 'u002', requested_at: '2026-09-01T09:00:00Z', reviewed_at: '2026-09-01T09:20:00Z', status: 'APPROVED', rejection_reason: null, notes: '', hospital_patient_id: 'HP-10031', national_id: '1076543210', patient_full_name: 'ظپط§ط·ظ…ط© ط³ط¹ط¯ ط§ظ„ط؛ط§ظ…ط¯ظٹ', gender: 'FEMALE', patient_age: 67, supervising_doctor: 'ط¯. ط³ط§ط±ط© ط§ظ„ط¹طھظٹط¨ظٹ', hospital_department: 'ط§ظ„ط¨ط§ط·ظ†ظٹط© (Medical)', hospital_admission_date: '2026-09-01', hospital_data_fetched_at: '2026-09-01T08:55:00Z' },
  { id: 'rreq003', patient_id: 'pat003', submitted_by_id: 'u001', reviewed_by_id: 'u002', requested_at: '2026-09-01T10:00:00Z', reviewed_at: '2026-09-01T10:15:00Z', status: 'APPROVED', rejection_reason: null, notes: 'ط¬ط±ط­ ظ…ط§ ط¨ط¹ط¯ ط¹ظ…ظ„ظٹط© ط¬ط±ط§ط­ظٹط©', hospital_patient_id: 'HP-10044', national_id: '1065432109', patient_full_name: 'ط®ط§ظ„ط¯ ط¹ط¨ط¯ط§ظ„ظ„ظ‡ ط§ظ„ط´ظ…ط±ظٹ', gender: 'MALE', patient_age: 44, supervising_doctor: 'ط¯. ط£ط­ظ…ط¯ ط§ظ„ط´ط±ظٹظپ', hospital_department: 'ط§ظ„ط¬ط±ط§ط­ط© (Surgery)', hospital_admission_date: '2026-09-01', hospital_data_fetched_at: '2026-09-01T09:55:00Z' },
  { id: 'rreq004', patient_id: 'pat004', submitted_by_id: 'u001', reviewed_by_id: 'u002', requested_at: _today + 'T07:00:00Z', reviewed_at: _today + 'T07:30:00Z', status: 'APPROVED', rejection_reason: null, notes: '', hospital_patient_id: 'HP-10058', national_id: '1054321098', patient_full_name: 'ظ†ظˆط±ط© ظ…ط­ظ…ط¯ ط§ظ„ط¹طھظٹط¨ظٹ', gender: 'FEMALE', patient_age: 38, supervising_doctor: 'ط¯. ظ†ظˆط±ط© ط§ظ„ط­ط±ط¨ظٹ', hospital_department: 'ط§ظ„ط£ظˆط±ط§ظ… (Oncology)', hospital_admission_date: '2026-09-05', hospital_data_fetched_at: _today + 'T06:55:00Z' },
  { id: 'rreq005', patient_id: 'pat005', submitted_by_id: 'u001', reviewed_by_id: 'u002', requested_at: '2026-08-29T08:00:00Z', reviewed_at: '2026-08-29T08:15:00Z', status: 'APPROVED', rejection_reason: null, notes: 'ظ‚ط¯ظ… ط³ظƒط±ظٹ ظ…ط¹ ط¶ط¹ظپ طھط±ظˆظٹط©', hospital_patient_id: 'HP-10067', national_id: '1043210987', patient_full_name: 'ط¹ط¨ط¯ط§ظ„ط±ط­ظ…ظ† ظٹظˆط³ظپ ط§ظ„ظ‚ط­ط·ط§ظ†ظٹ', gender: 'MALE', patient_age: 71, supervising_doctor: 'ط¯. ط¹ط¨ط¯ط§ظ„ظ„ظ‡ ط§ظ„ظ…ط§ظ„ظƒظٹ', hospital_department: 'ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ‚ظ„ط¨ظٹط© (CCU)', hospital_admission_date: '2026-08-29', hospital_data_fetched_at: '2026-08-29T07:55:00Z' },

  // PENDING â€” awaiting approval
  { id: 'rreq100', patient_id: null, submitted_by_id: 'u001', reviewed_by_id: null, requested_at: _today + 'T10:00:00Z', reviewed_at: null, status: 'PENDING', rejection_reason: null, notes: 'ظ…ط±ظٹط¶ ط¬ط¯ظٹط¯ â€” ط¬ط±ط­ ط­ط±ظˆظ‚', hospital_patient_id: 'HP-20001', national_id: '987654321', patient_full_name: 'ط³ط¹ظˆط¯ ط®ط§ظ„ط¯ ط§ظ„ظ…ظ†طµظˆط±', gender: 'MALE', patient_age: 45, supervising_doctor: 'ط¯. ظ…ط­ظ…ط¯ ط®ط§ظ„ط¯', hospital_department: 'ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ…ط±ظƒط²ط© (ICU)', hospital_admission_date: '2026-09-04', hospital_data_fetched_at: _today + 'T09:55:00Z' },
  { id: 'rreq101', patient_id: null, submitted_by_id: 'u003', reviewed_by_id: null, requested_at: _today + 'T11:30:00Z', reviewed_at: null, status: 'PENDING', rejection_reason: null, notes: 'ط¥ط­ط§ظ„ط© ظ…ظ† ظ‚ط³ظ… ط§ظ„ط£ظˆط±ط§ظ…', hospital_patient_id: 'HP-20002', national_id: '123456789', patient_full_name: 'ظ‡ظ†ط¯ ط¹ط¨ط¯ط§ظ„ط¹ط²ظٹط² ط§ظ„ط±ط§ط´ط¯', gender: 'FEMALE', patient_age: 36, supervising_doctor: 'ط¯. ط³ط§ط±ط© ط§ظ„ط¹طھظٹط¨ظٹ', hospital_department: 'ط§ظ„ط£ظˆط±ط§ظ… (Oncology)', hospital_admission_date: '2026-09-03', hospital_data_fetched_at: _today + 'T11:25:00Z' },
  { id: 'rreq102', patient_id: null, submitted_by_id: 'u001', reviewed_by_id: null, requested_at: _today + 'T12:00:00Z', reviewed_at: null, status: 'PENDING', rejection_reason: null, notes: '', hospital_patient_id: 'HP-20003', national_id: '111222333', patient_full_name: 'ظٹط§ط³ط± ظ…ط­ظ…ط¯ ط§ظ„ط­ط§ط±ط«ظٹ', gender: 'MALE', patient_age: 55, supervising_doctor: 'ط¯. ط£ط­ظ…ط¯ ط§ظ„ط´ط±ظٹظپ', hospital_department: 'ط§ظ„ط¬ط±ط§ط­ط© (Surgery)', hospital_admission_date: '2026-09-02', hospital_data_fetched_at: _today + 'T11:55:00Z' },

  // REJECTED
  { id: 'rreq200', patient_id: null, submitted_by_id: 'u003', reviewed_by_id: 'u002', requested_at: '2026-09-03T09:00:00Z', reviewed_at: '2026-09-03T14:00:00Z', status: 'REJECTED', rejection_reason: 'ط§ظ„ظ…ط±ظٹط¶ ظ„ظٹط³ ظ„ط¯ظٹظ‡ ط¬ط±ظˆط­ طھط³طھط¯ط¹ظٹ ط±ط¹ط§ظٹط© ظ…طھط®طµطµط© â€” ظٹظڈط±ط¬ظ‰ ط¥ط¹ط§ط¯ط© ط§ظ„طھظ‚ظٹظٹظ….', notes: 'ط·ظ„ط¨ طھط­ظˆظٹظ„ ظ„ظ„ط¹ظ†ط§ظٹط©', hospital_patient_id: 'HP-20004', national_id: '444555666', patient_full_name: 'ظ„ظٹظ†ط§ ط£ط­ظ…ط¯ ط§ظ„ط¹ظ…ط±ظٹ', gender: 'FEMALE', patient_age: 29, supervising_doctor: 'ط¯. ظ†ظˆط±ط© ط§ظ„ط­ط±ط¨ظٹ', hospital_department: 'ط§ظ„ط¹ط¸ط§ظ… (Orthopedics)', hospital_admission_date: '2026-09-05', hospital_data_fetched_at: '2026-09-03T08:55:00Z' },

  // CANCELLED
  { id: 'rreq300', patient_id: null, submitted_by_id: 'u001', reviewed_by_id: null, requested_at: '2026-09-02T08:00:00Z', reviewed_at: null, status: 'CANCELLED', rejection_reason: null, notes: 'طھظ… ط¥ظ„ط؛ط§ط، ط§ظ„ط·ظ„ط¨ â€” ط§ظ„ظ…ط±ظٹط¶ ط؛ط§ط¯ط± ط§ظ„ظ…ط³طھط´ظپظ‰', hospital_patient_id: 'HP-20003', national_id: '111222333', patient_full_name: 'ظٹط§ط³ط± ظ…ط­ظ…ط¯ ط§ظ„ط­ط§ط±ط«ظٹ', gender: 'MALE', patient_age: 55, supervising_doctor: 'ط¯. ط£ط­ظ…ط¯ ط§ظ„ط´ط±ظٹظپ', hospital_department: 'ط§ظ„ط¬ط±ط§ط­ط© (Surgery)', hospital_admission_date: '2026-09-02', hospital_data_fetched_at: '2026-09-02T07:55:00Z' }
];


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 5. REGISTRATIONS
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

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


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 6. REGISTRATION_PMH (Past Medical History per registration)
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

var REGISTRATION_PMH = [
  // reg001 â€” ط§ظ„ط³ظƒط±ظٹ, ط¶ط؛ط· ط§ظ„ط¯ظ…
  { id: 'rpmh001', registration_id: 'reg001', pmh_id: 'pmh001' },
  { id: 'rpmh002', registration_id: 'reg001', pmh_id: 'pmh002' },
  // reg002 â€” ط§ظ„ط³ظƒط±ظٹ, ط§ظ„ظپط´ظ„ ط§ظ„ظƒظ„ظˆظٹ
  { id: 'rpmh003', registration_id: 'reg002', pmh_id: 'pmh001' },
  { id: 'rpmh004', registration_id: 'reg002', pmh_id: 'pmh003' },
  // reg003 â€” ط§ظ„ط³ظƒط±ظٹ
  { id: 'rpmh005', registration_id: 'reg003', pmh_id: 'pmh001' },
  // reg004 â€” ط§ظ„ط³ط±ط·ط§ظ†, ظƒظٹظ…ط§ظˆظٹ
  { id: 'rpmh006', registration_id: 'reg004', pmh_id: 'pmh007' },
  { id: 'rpmh007', registration_id: 'reg004', pmh_id: 'pmh008' },
  // reg005 â€” ط£ظ…ط±ط§ط¶ ط§ظ„ظ‚ظ„ط¨, ط¶ط؛ط· ط§ظ„ط¯ظ…, ط§ظ„ط³ظƒط±ظٹ
  { id: 'rpmh008', registration_id: 'reg005', pmh_id: 'pmh004' },
  { id: 'rpmh009', registration_id: 'reg005', pmh_id: 'pmh002' },
  { id: 'rpmh010', registration_id: 'reg005', pmh_id: 'pmh001' },
  // reg006 â€” ط§ظ„ط³ظƒطھط© ط§ظ„ط¯ظ…ط§ط؛ظٹط©, ط¶ط؛ط· ط§ظ„ط¯ظ…
  { id: 'rpmh011', registration_id: 'reg006', pmh_id: 'pmh005' },
  { id: 'rpmh012', registration_id: 'reg006', pmh_id: 'pmh002' },
  // reg007 â€” ظƒط³ط± ط§ظ„ظˆط±ظƒ
  { id: 'rpmh013', registration_id: 'reg007', pmh_id: 'pmh009' },
  // reg008 â€” ط§ظ„ط¨ط¯ط§ظ†ط©, ط§ظ„ط³ظƒط±ظٹ
  { id: 'rpmh014', registration_id: 'reg008', pmh_id: 'pmh006' },
  { id: 'rpmh015', registration_id: 'reg008', pmh_id: 'pmh001' },
  // reg009 â€” ط§ظ„ط³ظƒط±ظٹ
  { id: 'rpmh016', registration_id: 'reg009', pmh_id: 'pmh001' },
  // reg010 â€” ط§ظ„ط³ظƒط±ظٹ, ظƒظ„ظˆظٹ, ط¶ط؛ط· ط§ظ„ط¯ظ…
  { id: 'rpmh017', registration_id: 'reg010', pmh_id: 'pmh001' },
  { id: 'rpmh018', registration_id: 'reg010', pmh_id: 'pmh003' },
  { id: 'rpmh019', registration_id: 'reg010', pmh_id: 'pmh002' },
  // reg011 â€” ط§ط­طھط´ط§ط،, ط¶ط؛ط· ط§ظ„ط¯ظ…
  { id: 'rpmh020', registration_id: 'reg011', pmh_id: 'pmh011' },
  { id: 'rpmh021', registration_id: 'reg011', pmh_id: 'pmh002' },
  // reg012 â€” ط³ط±ط·ط§ظ† ط§ظ„ظ‚ظˆظ„ظˆظ†, ظƒظٹظ…ط§ظˆظٹ
  { id: 'rpmh022', registration_id: 'reg012', pmh_id: 'pmh012' },
  { id: 'rpmh023', registration_id: 'reg012', pmh_id: 'pmh008' },
  // reg013 â€” ط§ظ„ط³ظƒط±ظٹ, ط§ظ„ظپط´ظ„ ط§ظ„ظƒظ„ظˆظٹ
  { id: 'rpmh024', registration_id: 'reg013', pmh_id: 'pmh001' },
  { id: 'rpmh025', registration_id: 'reg013', pmh_id: 'pmh003' },
  // reg014 â€” ط§ظ„ط±ط¨ظˆ, ط¶ط؛ط· ط§ظ„ط¯ظ…
  { id: 'rpmh026', registration_id: 'reg014', pmh_id: 'pmh013' },
  { id: 'rpmh027', registration_id: 'reg014', pmh_id: 'pmh002' },
  // reg016 â€” ط§ظ„ط³ظƒطھط© ط§ظ„ط¯ظ…ط§ط؛ظٹط©, ط¶ط؛ط· ط§ظ„ط¯ظ…
  { id: 'rpmh028', registration_id: 'reg016', pmh_id: 'pmh005' },
  { id: 'rpmh029', registration_id: 'reg016', pmh_id: 'pmh002' },
  // reg017 â€” ظƒط³ط± ط§ظ„ظƒط§ط­ظ„
  { id: 'rpmh030', registration_id: 'reg017', pmh_id: 'pmh010' },
  // reg099 â€” ط§ظ„ط³ظƒط±ظٹ
  { id: 'rpmh031', registration_id: 'reg099', pmh_id: 'pmh001' }
];


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 7. REGISTRATION_DIAGNOSES
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

var REGISTRATION_DIAGNOSES = [
  // reg001 â€” 2 diagnoses
  { id: 'rdx001', registration_id: 'reg001', diagnosis_id: 'dx001', description: 'ط¬ط±ط­ ط¹ظ…ظٹظ‚ ظپظٹ ط§ظ„ظƒط¹ط¨ ط§ظ„ط£ظٹط³ط± ظ…ط¹ ظˆط¬ظˆط¯ ط¥ظپط±ط§ط²ط§طھ ظ‚ظٹط­ظٹط© ظˆطھط£ط«ط± ط¨ط§ظ„ط£ظ†ط³ط¬ط© ط§ظ„ظ„ظٹظ†ط©. ط£ط¨ط¹ط§ط¯ ط§ظ„ط¬ط±ط­ 3أ—2 ط³ظ….', wound_type_id: 'wt001', initial_dressing_id: 'drs001', initial_medication_id: 'med001' },
  { id: 'rdx002', registration_id: 'reg001', diagnosis_id: 'dx002', description: 'ظ‚ط±ط­ط© ظپط±ط§ط´ ظپظٹ ظ…ظ†ط·ظ‚ط© ط§ظ„ط¹ط¬ط² â€” ط§ظ„ظ…ط±ط­ظ„ط© ط§ظ„ط«ط§ظ†ظٹط© ظ…ط¹ ظˆط¬ظˆط¯ ط£ظ†ط³ط¬ط© طµظپط±ط§ط، ظˆط¥ظپط±ط§ط²ط§طھ ط·ظپظٹظپط©.', wound_type_id: 'wt002', initial_dressing_id: 'drs003', initial_medication_id: 'med003' },
  // reg002 â€” 1 diagnosis
  { id: 'rdx003', registration_id: 'reg002', diagnosis_id: 'dx003', description: 'ظ‚ط±ط­ط© ظˆط±ظٹط¯ظٹط© ظپظٹ ط§ظ„ط³ط§ظ‚ ط§ظ„ظٹظ…ظ†ظ‰ ظ…ط¹ ط¥ظپط±ط§ط²ط§طھ ط؛ط²ظٹط±ط©. ط§ظ„ظ…ظ†ط·ظ‚ط© ط§ظ„ظ…ط­ظٹط·ط© ظ…طھظ‡ظٹط¬ط©.', wound_type_id: 'wt003', initial_dressing_id: 'drs004', initial_medication_id: 'med002' },
  // reg003 â€” 3 diagnoses
  { id: 'rdx004', registration_id: 'reg003', diagnosis_id: 'dx004', description: 'ط¬ط±ط­ ظ…ط§ ط¨ط¹ط¯ ط¹ظ…ظ„ظٹط© ط§ط³طھط¦طµط§ظ„ ط§ظ„ط²ط§ط¦ط¯ط© ط§ظ„ط¯ظˆط¯ظٹط© â€” ظ†ط¸ظٹظپ ظ…ط¹ ط®ظٹظˆط· ظپظٹ ظ…ظƒط§ظ†ظ‡ط§.', wound_type_id: 'wt004', initial_dressing_id: 'drs006', initial_medication_id: 'med002' },
  { id: 'rdx005', registration_id: 'reg003', diagnosis_id: 'dx001', description: 'ط¬ط±ط­ طµط؛ظٹط± ظپظٹ ط¥طµط¨ط¹ ط§ظ„ظ‚ط¯ظ… ط§ظ„ظƒط¨ظٹط± ظ…ط¹ ط§ط­ظ…ط±ط§ط± ظ…ط­ظٹط·ظٹ.', wound_type_id: 'wt005', initial_dressing_id: 'drs005', initial_medication_id: 'med001' },
  { id: 'rdx006', registration_id: 'reg003', diagnosis_id: 'dx002', description: 'ظ‚ط±ط­ط© ط§ظ„ظ…ط±ط­ظ„ط© ط§ظ„ط£ظˆظ„ظ‰ ظپظٹ ظ…ظ†ط·ظ‚ط© ط§ظ„ط¹طµط¹طµ â€” ط§ط­ظ…ط±ط§ط± ط¨ط¯ظˆظ† طھظ…ط²ظ‚ ط¬ظ„ط¯ظٹ.', wound_type_id: 'wt002', initial_dressing_id: 'drs002', initial_medication_id: 'med003' },
  // reg004 â€” 1 diagnosis
  { id: 'rdx007', registration_id: 'reg004', diagnosis_id: 'dx010', description: 'ط¬ط±ط­ ظ…ط§ ط¨ط¹ط¯ ط¹ظ…ظ„ظٹط© ط§ط³طھط¦طµط§ظ„ ط§ظ„ط«ط¯ظٹ â€” ط¨ط¯ط§ظٹط© ط§ظ„ط´ظپط§ط،. ظ„ط§ ط¹ظپظˆظ†ط© ظˆط§ط¶ط­ط©.', wound_type_id: 'wt004', initial_dressing_id: 'drs001', initial_medication_id: 'med002' },
  // reg005 â€” 2 diagnoses
  { id: 'rdx008', registration_id: 'reg005', diagnosis_id: 'dx001', description: 'ط¬ط±ط­ ظپظٹ ط§ظ„ظ‚ط¯ظ… ط§ظ„ظٹط³ط±ظ‰ ظ…ط¹ ط¶ط¹ظپ ظپظٹ ط§ظ„طھط±ظˆظٹط© ط§ظ„ط¯ظ…ظˆظٹط©. ط§ظ„ظ…ظ†ط·ظ‚ط© ط¨ط§ط±ط¯ط©.', wound_type_id: 'wt005', initial_dressing_id: 'drs005', initial_medication_id: 'med001' },
  { id: 'rdx009', registration_id: 'reg005', diagnosis_id: 'dx005', description: 'ط¶ط±ط± ط¬ظ„ط¯ظٹ ظپظٹ ظ…ظˆط¶ط¹ ط§ظ„ط¥ط¨ط±ط© ط§ظ„ظˆط±ظٹط¯ظٹط© ظپظٹ ط§ظ„ط°ط±ط§ط¹ ط§ظ„ط£ظٹظ…ظ†.', wound_type_id: 'wt003', initial_dressing_id: 'drs002', initial_medication_id: 'med002' },
  // reg006 â€” 1 diagnosis
  { id: 'rdx010', registration_id: 'reg006', diagnosis_id: 'dx002', description: 'ظ‚ط±ط­ط© ظپط±ط§ط´ ظپظٹ ظ…ظ†ط·ظ‚ط© ط§ظ„ظƒطھظپ ط§ظ„ط£ظٹط³ط± ط¨ط³ط¨ط¨ ط§ظ„ط´ظ„ظ„ ط§ظ„ظ†ط§ط¬ظ… ط¹ظ† ط§ظ„ط³ظƒطھط© ط§ظ„ط¯ظ…ط§ط؛ظٹط©.', wound_type_id: 'wt009', initial_dressing_id: 'drs002', initial_medication_id: 'med002' },
  // reg007 â€” 2 diagnoses
  { id: 'rdx011', registration_id: 'reg007', diagnosis_id: 'dx004', description: 'ط¬ط±ط­ ط§ظ„ظˆط±ظƒ ط¨ط¹ط¯ ط§ظ„ط¹ظ…ظ„ظٹط© â€” ط§ظ„طھط¦ط§ظ… طھط¯ط±ظٹط¬ظٹ.', wound_type_id: 'wt004', initial_dressing_id: 'drs006', initial_medication_id: 'med002' },
  { id: 'rdx012', registration_id: 'reg007', diagnosis_id: 'dx002', description: 'ط§ط­ظ…ط±ط§ط± ظپظٹ ظ…ظ†ط·ظ‚ط© ط§ظ„ظƒط¹ط¨ â€” ظ…ط±ط­ظ„ط© ط£ظˆظ„ظ‰.', wound_type_id: 'wt008', initial_dressing_id: 'drs001', initial_medication_id: 'med003' },
  // reg008 â€” 1 diagnosis
  { id: 'rdx013', registration_id: 'reg008', diagnosis_id: 'dx006', description: 'ط¬ط±ط­ ط¨ط·ظ†ظٹ ظ…ظ†ظپطھط­ ط¨ط¹ط¯ ط¹ظ…ظ„ظٹط© ط¬ط±ط§ط­ظٹط© â€” ظٹطھط·ظ„ط¨ ط§ظ„ط¹ظ†ط§ظٹط© ط¨ط§ظ„ط¶ط؛ط· ط§ظ„ط³ظ„ط¨ظٹ.', wound_type_id: 'wt007', initial_dressing_id: 'drs007', initial_medication_id: 'med001' },
  // reg009 â€” 2 diagnoses
  { id: 'rdx014', registration_id: 'reg009', diagnosis_id: 'dx001', description: 'ط¨ط¯ط§ظٹط© ظ†ط®ط± ظپظٹ ط¥طµط¨ط¹ ط§ظ„ظ‚ط¯ظ… ط§ظ„طµط؛ظٹط±.', wound_type_id: 'wt001', initial_dressing_id: 'drs001', initial_medication_id: 'med001' },
  { id: 'rdx015', registration_id: 'reg009', diagnosis_id: 'dx007', description: 'ظƒط¯ظ…ط© ط¬ظ„ط¯ظٹط© ظپظٹ ط§ظ„ط³ط§ظ‚ ط§ظ„ظٹط³ط±ظ‰ ظ…ظ† ط­ط§ط¯ط«.', wound_type_id: 'wt006', initial_dressing_id: 'drs003', initial_medication_id: 'med002' },
  // reg010 â€” 3 diagnoses
  { id: 'rdx016', registration_id: 'reg010', diagnosis_id: 'dx001', description: 'ظ†ط®ط± ظپظٹ ظ…ظ†ط·ظ‚ط© ط§ظ„ظƒط¹ط¨ ط§ظ„ط£ظٹظ…ظ† ظ…ط¹ ط§ط­ظ…ط±ط§ط± ظپظٹ ط§ظ„ظ…ط­ظٹط·.', wound_type_id: 'wt001', initial_dressing_id: 'drs005', initial_medication_id: 'med001' },
  { id: 'rdx017', registration_id: 'reg010', diagnosis_id: 'dx002', description: 'ظ‚ط±ط­ط© ط¹ظ…ظٹظ‚ط© ظپظٹ ظ…ظ†ط·ظ‚ط© ط§ظ„ط¹ط¬ط² ظ…ط¹ طھط¢ظƒظ„ ظ†ط³ظٹط¬ظٹ.', wound_type_id: 'wt010', initial_dressing_id: 'drs003', initial_medication_id: 'med003' },
  { id: 'rdx018', registration_id: 'reg010', diagnosis_id: 'dx008', description: 'ظ‚ط±ط­ط© ط¥ظ‚ظپط§ط±ظٹط© ظپظٹ ط§ظ„ظƒط§ط­ظ„ ط§ظ„ط£ظٹط³ط±.', wound_type_id: 'wt005', initial_dressing_id: 'drs004', initial_medication_id: 'med002' },
  // reg011 â€” 1 diagnosis
  { id: 'rdx019', registration_id: 'reg011', diagnosis_id: 'dx009', description: 'ط¬ط±ط­ ظ…ط§ ط¨ط¹ط¯ ط¥ط²ط§ظ„ط© ظ‚ط³ط·ط±ط© ط§ظ„ظ‚ظ„ط¨ â€” ط§ظ„طھط¦ط§ظ… ط¬ظٹط¯.', wound_type_id: 'wt011', initial_dressing_id: 'drs002', initial_medication_id: 'med002' },
  // reg012 â€” 1 diagnosis
  { id: 'rdx020', registration_id: 'reg012', diagnosis_id: 'dx011', description: 'ط¬ط±ط­ ظپط؛ط±ط© ط§ظ„ظ‚ظˆظ„ظˆظ† â€” ظ…ط³طھظ‚ط± ظˆظ†ط¸ظٹظپ.', wound_type_id: 'wt004', initial_dressing_id: 'drs001', initial_medication_id: 'med002' },
  // reg013 â€” 2 diagnoses
  { id: 'rdx021', registration_id: 'reg013', diagnosis_id: 'dx001', description: 'ط¬ط±ط­ ظ†ط®ط±ظٹ ط¹ظ…ظٹظ‚ ظپظٹ ظ‚ط¯ظ… ط§ظ„ط£طµط¨ط¹ ط§ظ„ط£ظƒط¨ط± ظ…ط¹ ظˆط¬ظˆط¯ ط¥ظپط±ط§ط²ط§طھ ظ‚ظٹط­ظٹط©.', wound_type_id: 'wt001', initial_dressing_id: 'drs005', initial_medication_id: 'med001' },
  { id: 'rdx022', registration_id: 'reg013', diagnosis_id: 'dx002', description: 'ظ‚ط±ط­ط© ظپط±ط§ط´ ط¹ظ…ظٹظ‚ط© ظپظٹ ظ…ظ†ط·ظ‚ط© ط§ظ„ط¹ط¬ط² ظ…ط¹ ظˆط¬ظˆط¯ ط£ظ†ط³ط¬ط© ظ…طھط³ط§ظ‚ط·ط©.', wound_type_id: 'wt010', initial_dressing_id: 'drs003', initial_medication_id: 'med003' },
  // reg014 â€” 1 diagnosis
  { id: 'rdx023', registration_id: 'reg014', diagnosis_id: 'dx003', description: 'ظ‚ط±ط­ط© ظˆط±ظٹط¯ظٹط© ظ†ط¶ط­ظٹط© ظپظٹ ط§ظ„ط³ط§ظ‚ ط§ظ„ظٹط³ط±ظ‰ ظ…ط¹ ط¥ظپط±ط§ط²ط§طھ ظ…طھظˆط³ط·ط©.', wound_type_id: 'wt003', initial_dressing_id: 'drs004', initial_medication_id: 'med002' },
  // reg015 â€” 1 diagnosis
  { id: 'rdx024', registration_id: 'reg015', diagnosis_id: 'dx013', description: 'ط¬ط±ط­ ظ…ط§ ط¨ط¹ط¯ ط¬ط±ط§ط­ط© ط§ط³طھط¦طµط§ظ„ ط§ظ„ظ…ط±ط§ط±ط© â€” ط§ظ„طھط¦ط§ظ… ط¬ظٹط¯ ط¨ط¯ظˆظ† ط§ظ„طھظ‡ط§ط¨.', wound_type_id: 'wt004', initial_dressing_id: 'drs006', initial_medication_id: 'med002' },
  // reg016 â€” 2 diagnoses
  { id: 'rdx025', registration_id: 'reg016', diagnosis_id: 'dx002', description: 'ظ‚ط±ط­ط© ظ…ط±ط­ظ„ط© ط«ط§ظ†ظٹط© ظپظٹ ط§ظ„ظƒط¹ط¨ ط§ظ„ط£ظٹظ…ظ† ط¨ط³ط¨ط¨ ط§ط³طھظ„ظ‚ط§ط، ط§ظ„ظپط±ط§ط´ ط§ظ„ط·ظˆظٹظ„.', wound_type_id: 'wt009', initial_dressing_id: 'drs002', initial_medication_id: 'med002' },
  { id: 'rdx026', registration_id: 'reg016', diagnosis_id: 'dx005', description: 'طھظ‡ظٹط¬ ط¬ظ„ط¯ظٹ ظ†ط¶ط­ظٹ ظپظٹ ظ…ظˆط¶ط¹ ط§ظ„ط¥ط¨ط±ط© ط¨ط§ظ„ط³ط§ط¹ط¯ ط§ظ„ط£ظٹط³ط±.', wound_type_id: 'wt003', initial_dressing_id: 'drs001', initial_medication_id: 'med001' },
  // reg017 â€” 1 diagnosis
  { id: 'rdx027', registration_id: 'reg017', diagnosis_id: 'dx012', description: 'ط¬ط±ط­ ظ…ط§ ط¨ط¹ط¯ ط¹ظ…ظ„ظٹط© طھط«ط¨ظٹطھ ط´ط±ظٹط­ط© ظˆظ…ط³ط§ظ…ظٹط± ط§ظ„ظƒط§ط­ظ„ ط§ظ„ط£ظٹط³ط±.', wound_type_id: 'wt004', initial_dressing_id: 'drs006', initial_medication_id: 'med002' },
  // reg099 â€” 1 diagnosis (discharged)
  { id: 'rdx028', registration_id: 'reg099', diagnosis_id: 'dx014', description: 'ط¬ط±ط­ ط¬ط±ط§ط­ظٹ ط³ط§ط¨ظ‚ ظ…ط¹ ظˆط¬ظˆط¯ ط¥ظپط±ط§ط²ط§طھ ظ…طµظ„ظٹط© ط®ظپظٹظپط©.', wound_type_id: 'wt003', initial_dressing_id: 'drs001', initial_medication_id: 'med001' },
  // Historical registrations
  { id: 'rdx029', registration_id: 'reg001old', diagnosis_id: 'dx001', description: 'ط¬ط±ط­ ظ‚ط¯ظ… ط³ظƒط±ظٹ ظپظٹ ط§ظ„ظƒط¹ط¨ ط§ظ„ط£ظٹط³ط± â€” طھظ… ط§ظ„ط´ظپط§ط، ط¨ظ†ط³ط¨ط© 90% ظˆط§ظ„ط®ط±ظˆط¬.', wound_type_id: 'wt001', initial_dressing_id: 'drs001', initial_medication_id: 'med001' },
  { id: 'rdx030', registration_id: 'reg005old', diagnosis_id: 'dx001', description: 'ظ‚ط¯ظ… ط³ظƒط±ظٹ ظ…ط¹ ط¶ط¹ظپ طھط±ظˆظٹط© â€” طھط­ط³ظ† ط¬ط²ط¦ظٹ ظˆطھط±ط®ظٹطµ.', wound_type_id: 'wt005', initial_dressing_id: 'drs005', initial_medication_id: 'med001' },
  { id: 'rdx031', registration_id: 'reg013old', diagnosis_id: 'dx001', description: 'ط¬ط±ط­ ظ‚ط¯ظ… ط³ظƒط±ظٹ ط³ط§ط¨ظ‚ â€” طھظ… ط§ظ„ط´ظپط§ط، ظˆط§ظ„طھط±ط®ظٹطµ ط¨ظ†ط¬ط§ط­.', wound_type_id: 'wt001', initial_dressing_id: 'drs005', initial_medication_id: 'med001' }
];


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 8. REGISTRATION_DIAGNOSIS_PROCEDURES (performed at registration)
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

var REGISTRATION_DIAGNOSIS_PROCEDURES = [
  // reg001 â€” طھظ†ط¶ظٹط±
  { id: 'rdxp001', registration_diagnosis_id: 'rdx001', procedure_id: 'proc001', performed_by_id: 'u001', performed_at: '2026-08-31T10:00:00Z' },
  // reg002 â€” طھظ‚ظٹظٹظ…
  { id: 'rdxp002', registration_diagnosis_id: 'rdx003', procedure_id: 'proc002', performed_by_id: 'u001', performed_at: '2026-09-01T11:00:00Z' },
  // reg003 â€” ظ…ط³ط­ط© ظ…ط²ط±ط¹ط©
  { id: 'rdxp003', registration_diagnosis_id: 'rdx004', procedure_id: 'proc004', performed_by_id: 'u001', performed_at: '2026-09-01T10:30:00Z' },
  // reg004 â€” طھط؛ظٹظٹط± ط¶ظ…ط§ط¯ط©
  { id: 'rdxp004', registration_diagnosis_id: 'rdx007', procedure_id: 'proc003', performed_by_id: 'u001', performed_at: '2026-09-05T08:00:00Z' },
  // reg005 â€” طھظ‚ظٹظٹظ…
  { id: 'rdxp005', registration_diagnosis_id: 'rdx008', procedure_id: 'proc002', performed_by_id: 'u001', performed_at: '2026-08-29T09:00:00Z' },
  // reg006 â€” طھظ†ط¶ظٹط±
  { id: 'rdxp006', registration_diagnosis_id: 'rdx010', procedure_id: 'proc001', performed_by_id: 'u001', performed_at: '2026-09-02T09:00:00Z' },
  // reg007 â€” طھط؛ظٹظٹط± ط¶ظ…ط§ط¯ط©
  { id: 'rdxp007', registration_diagnosis_id: 'rdx011', procedure_id: 'proc003', performed_by_id: 'u001', performed_at: '2026-09-03T10:00:00Z' },
  // reg008 â€” طھظ†ط¶ظٹط±
  { id: 'rdxp008', registration_diagnosis_id: 'rdx013', procedure_id: 'proc001', performed_by_id: 'u001', performed_at: '2026-08-28T10:00:00Z' },
  // reg010 â€” طھظ†ط¶ظٹط±
  { id: 'rdxp009', registration_diagnosis_id: 'rdx016', procedure_id: 'proc001', performed_by_id: 'u001', performed_at: '2026-08-30T09:00:00Z' },
  // reg013 â€” طھظ†ط¶ظٹط±
  { id: 'rdxp010', registration_diagnosis_id: 'rdx021', procedure_id: 'proc001', performed_by_id: 'u001', performed_at: '2026-09-01T09:00:00Z' }
];


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 9. DAILY_REVIEWS
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

var DAILY_REVIEWS = [
  // reg001 â€” 3 daily reviews
  { id: 'drev001', registration_diagnosis_id: 'rdx001', review_date: '2026-08-31', nurse_id: 'u001', wound_description: 'ط¬ط±ط­ ط¹ظ…ظٹظ‚ ظپظٹ ط§ظ„ظƒط¹ط¨ ط§ظ„ط£ظٹط³ط±طŒ ط£ط¨ط¹ط§ط¯ ط§ظ„ط¬ط±ط­ 3أ—2 ط³ظ…. ط¥ظپط±ط§ط²ط§طھ ظ‚ظٹط­ظٹط© ظˆط§ط¶ط­ط©.', wound_changes: 'ط§ظ„طھط³ط¬ظٹظ„ ط§ظ„ط£ظˆظ„ظٹ ظ„ظ„ط­ط§ظ„ط© ط¹ظ†ط¯ ط§ظ„ط§ط³طھظ‚ط¨ط§ظ„ ظ…ظ† ط§ظ„ط¹ظ†ط§ظٹط© ط§ظ„ظ…ط±ظƒط²ط©.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'WORSENED' },
  { id: 'drev002', registration_diagnosis_id: 'rdx002', review_date: '2026-08-31', nurse_id: 'u001', wound_description: 'ظ‚ط±ط­ط© ظپط±ط§ط´ ظپظٹ ظ…ظ†ط·ظ‚ط© ط§ظ„ط¹ط¬ط² â€” ط§ظ„ظ…ط±ط­ظ„ط© ط§ظ„ط«ط§ظ†ظٹط©.', wound_changes: 'طھظ‚ظٹظٹظ… ط£ظˆظ„ظٹ ظ„ظ…ظ†ط·ظ‚ط© ط§ظ„ط¹ط¬ط² ظˆطھظ†ط¸ظٹظپ ط§ظ„ط¬ط±ط­.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  { id: 'drev003', registration_diagnosis_id: 'rdx001', review_date: '2026-09-02', nurse_id: 'u001', wound_description: 'طھط±ط§ط¬ط¹ ظ…ظ„ط­ظˆط¸ ظپظٹ ط­ط¬ظ… ط§ظ„ط¬ط±ط­ â€” 2.5أ—1.8 ط³ظ…. طھظ‚ظ„طµ ظپظٹ ط§ظ„ط¥ظپط±ط§ط²ط§طھ ط§ظ„ظ‚ظٹط­ظٹط©.', wound_changes: 'طھط­ط³ظ† طھط¯ط±ظٹط¬ظٹ. ظ„ط§ ط¹ظپظˆظ†ط© ط­ط§ط¯ط©. طھظ… طھط؛ظٹظٹط± ط§ظ„ط¶ظ…ط§ط¯ط©.', dressing_id: 'drs001', medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg002 â€” 1 daily review
  { id: 'drev004', registration_diagnosis_id: 'rdx003', review_date: '2026-09-05', nurse_id: 'u001', wound_description: 'ظ‚ط±ط­ط© ظˆط±ظٹط¯ظٹط© ظپظٹ ط§ظ„ط³ط§ظ‚ ط§ظ„ظٹظ…ظ†ظ‰ ط¹ظ†ط¯ ط§ظ„طھط³ط¬ظٹظ„ â€” ط¥ظپط±ط§ط²ط§طھ ط؛ط²ظٹط±ط©.', wound_changes: 'طھط³ط¬ظٹظ„ ط£ظˆظ„ظٹ. طھظ‚ظٹظٹظ… ط§ظ„ط­ط§ظ„ط© ط§ظ„ظˆط±ظٹط¯ظٹط© ظ„ظ„ظ…ط±ظٹط¶ط©.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg003 â€” 1 daily review
  { id: 'drev005', registration_diagnosis_id: 'rdx004', review_date: '2026-08-30', nurse_id: 'u001', wound_description: 'ط§ظ„ط¬ط±ط­ ط§ظ„ط¬ط±ط§ط­ظٹ ظ†ط¸ظٹظپ ظˆط¬ط§ظپ ظپظٹ ط§ظ„ظٹظˆظ… ط§ظ„ط£ظˆظ„ ط¨ط¹ط¯ ط§ظ„ط¹ظ…ظ„ظٹط©.', wound_changes: 'طھظ‚ظٹظٹظ… ط£ظˆظ„ظٹ ط¨ط¹ط¯ ط§ظ„ط¹ظ…ظ„ظٹط©.', dressing_id: null, medication_id: null, follow_up_required: false, status: 'IMPROVED_FOLLOWUP' },
  // reg005 â€” 1 daily review
  { id: 'drev006', registration_diagnosis_id: 'rdx008', review_date: '2026-09-01', nurse_id: 'u001', wound_description: 'ط§ط³طھظ…ط±ط§ط± ط¶ط¹ظپ ط§ظ„طھط±ظˆظٹط©. ظ„ط§ طھط؛ظٹط± ظ…ظ„ط­ظˆط¸ ظپظٹ ط­ط¬ظ… ط§ظ„ط¬ط±ط­.', wound_changes: 'ظ„ط§ طھط؛ظٹط± ظƒط¨ظٹط±. طھظ… طھط¬ط¯ظٹط¯ ط§ظ„ط¶ظ…ط§ط¯ط©.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'WORSENED' },
  // reg007 â€” 1 daily review
  { id: 'drev007', registration_diagnosis_id: 'rdx011', review_date: '2026-09-05', nurse_id: 'u001', wound_description: 'ط¬ط±ط­ ط§ظ„ظˆط±ظƒ ط¬ط§ظپ ظˆظ†ط¸ظٹظپ. ظ„ط§ ظ…ط¤ط´ط±ط§طھ ظ„ظ„ط¹ط¯ظˆظ‰.', wound_changes: 'طھظ‚ظٹظٹظ… ط£ظˆظ„ظٹ.', dressing_id: null, medication_id: null, follow_up_required: false, status: 'IMPROVED_FOLLOWUP' },
  // reg008 â€” 1 daily review
  { id: 'drev008', registration_diagnosis_id: 'rdx013', review_date: '2026-09-02', nurse_id: 'u001', wound_description: 'ط§ظ„ط¬ط±ط­ ظ…ط³طھظ‚ط±. ط§ظ„ط¶ط؛ط· ط§ظ„ط³ظ„ط¨ظٹ ظٹط¹ظ…ظ„ ط¨ط´ظƒظ„ ط¬ظٹط¯.', wound_changes: 'ظ„ط§ طھط؛ظٹظٹط±.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg010 â€” 1 daily review
  { id: 'drev009', registration_diagnosis_id: 'rdx016', review_date: '2026-09-03', nurse_id: 'u001', wound_description: 'ظ†ط®ط± ظ…ط³طھظ…ط± ظپظٹ ظ…ظ†ط·ظ‚ط© ط§ظ„ظƒط¹ط¨. ط§ط­ظ…ط±ط§ط± ظپظٹ ط§ظ„ظ…ط­ظٹط·.', wound_changes: 'طھظ… ط§ظ„طھظ†ط¶ظٹط± ط§ظ„ط£ظˆظ„. ط¥ظپط±ط§ط²ط§طھ ظ‚ظٹط­ظٹط© طھط­ط³ظ†طھ ظ‚ظ„ظٹظ„ط§ظ‹.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'WORSENED' },
  // reg011 â€” 1 daily review
  { id: 'drev010', registration_diagnosis_id: 'rdx019', review_date: '2026-09-01', nurse_id: 'u001', wound_description: 'ط§ظ„ط¬ط±ط­ ظ†ط¸ظٹظپ. ظ„ط§ ظ…ط¤ط´ط±ط§طھ ظ„ظ„ط¹ط¯ظˆظ‰.', wound_changes: 'طھط­ط³ظ† ظ…ظ„ط­ظˆط¸. طھظ… طھط؛ظٹظٹط± ط§ظ„ط¶ظ…ط§ط¯ط©.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg013 â€” 1 daily review
  { id: 'drev011', registration_diagnosis_id: 'rdx021', review_date: '2026-09-04', nurse_id: 'u001', wound_description: 'طھط­ط³ظ† ط·ظپظٹظپ ظپظٹ ط­ظˆط§ظپ ط§ظ„ط¬ط±ط­ ط¨ط¹ط¯ ط§ظ„طھظ†ط¶ظٹط±.', wound_changes: 'طھظ‚ظ„طµ ط¨ط³ظٹط· ظپظٹ ط§ظ„ط£ظ†ط³ط¬ط© ط§ظ„ظ…ظٹطھط©.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg014 â€” 1 daily review
  { id: 'drev012', registration_diagnosis_id: 'rdx023', review_date: '2026-09-03', nurse_id: 'u001', wound_description: 'ط§ظ†ط®ظپط§ط¶ ظپظٹ ظ…ط³طھظˆظ‰ ط§ظ„ط¥ظپط±ط§ط²ط§طھ ط§ظ„ظ†ط¶ط­ظٹط©.', wound_changes: 'ط§ط³طھظ…ط±ط§ط± ط§ظ„طھط¦ط§ظ… ط§ظ„ط­ظˆط§ظپ ط§ظ„ط¬ط§ظ†ط¨ظٹط©.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg015 â€” 1 daily review
  { id: 'drev013', registration_diagnosis_id: 'rdx024', review_date: '2026-09-05', nurse_id: 'u001', wound_description: 'ط§ظ„ط¬ط±ط­ ط§ظ„ط¬ط±ط§ط­ظٹ ظ†ط¸ظٹظپ ظˆظ…ط³طھظ‚ط±.', wound_changes: 'ط§ظ„طھط¦ط§ظ… ظ…ظ…طھط§ط² ظ„ط·ط¨ظ‚ط§طھ ط§ظ„ط¬ظ„ط¯ ط§ظ„ط£ظˆظ„ظٹط©.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg016 â€” 1 daily review
  { id: 'drev014', registration_diagnosis_id: 'rdx025', review_date: '2026-09-04', nurse_id: 'u001', wound_description: 'طھط±ط§ط¬ط¹ ط§ظ„ط§ط­ظ…ط±ط§ط± ط§ظ„ظ…ط­ظٹط· ط¨ط§ظ„ظƒط¹ط¨.', wound_changes: 'طھط­ط³ظ† ظ…ظ„ط­ظˆط¸ ظپظٹ ط§ظ„ط£ظ†ط³ط¬ط© ط§ظ„ط­ط¨ظٹط¨ظٹط©.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg017 â€” 1 daily review
  { id: 'drev015', registration_diagnosis_id: 'rdx027', review_date: '2026-09-05', nurse_id: 'u001', wound_description: 'ط§ظ„ط¬ط±ط­ ظ†ط¸ظٹظپ ظˆط¬ط§ظپ. ط­ظˆط§ظپ ط§ظ„ط¬ط±ط­ ظ…ظ„طھط¦ظ…ط©.', wound_changes: 'ظ„ط§ طھظˆط¬ط¯ ظ…ط¤ط´ط±ط§طھ ط¹ط¯ظˆظ‰.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  // reg099 â€” 2 daily reviews (discharged)
  { id: 'drev016', registration_diagnosis_id: 'rdx028', review_date: '2026-04-12', nurse_id: 'u001', wound_description: 'طھظ†ط¸ظٹظپ ظˆطھظ†ط¶ظٹط± ط§ظ„ط¬ط±ط­ ط§ظ„ط¬ط±ط§ط­ظٹ ط§ظ„ط£ظˆظ„.', wound_changes: 'ط§ط³طھط¬ط§ط¨ط© ط£ظˆظ„ظٹط© ط¬ظٹط¯ط© ظˆطھط±ط§ط¬ط¹ ط§ظ„طھظˆط±ظ….', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  { id: 'drev017', registration_diagnosis_id: 'rdx028', review_date: '2026-05-08', nurse_id: 'u001', wound_description: 'ط§ظ„طھط¦ط§ظ… طھط§ظ… ظ„ط­ظˆط§ظپ ط§ظ„ط¬ط±ط­ ظˆط¬ظپط§ظپ ط§ظ„ط¥ظپط±ط§ط²ط§طھ ط¨ط§ظ„ظƒط§ظ…ظ„.', wound_changes: 'طھط­ط³ظ† ظ…ظ…طھط§ط² ظˆطھط؛ط·ظٹط© ط¬ظ„ط¯ظٹط© ظ…ظƒطھظ…ظ„ط©.', dressing_id: 'drs008', medication_id: 'med002', follow_up_required: false, status: 'IMPROVED_DISCHARGE' },
  // Historical registrations
  { id: 'drev018', registration_diagnosis_id: 'rdx029', review_date: '2025-11-12', nurse_id: 'u003', wound_description: 'ط§ظ„طھط³ط¬ظٹظ„ ط§ظ„ط£ظˆظ„ظٹ ظ„ط­ط§ظ„ط© ط§ظ„ط¬ط±ط­ ط§ظ„ط³ط§ط¨ظ‚ط©.', wound_changes: 'ط¨ط¯ط، ط®ط·ط© ط§ظ„ط¹ظ„ط§ط¬ ظˆط§ظ„طھظ†ط¶ظٹط±.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'IMPROVED_FOLLOWUP' },
  { id: 'drev019', registration_diagnosis_id: 'rdx029', review_date: '2025-12-05', nurse_id: 'u003', wound_description: 'ط§ظ„طھط¦ط§ظ… ظ…ظ„ط­ظˆط¸ ظپظٹ ط§ظ„ط£ظ†ط³ط¬ط© ظˆط¨ط¯ط§ظٹط© ط§ظ„ط´ظپط§ط، ط§ظ„ظƒط§ظ…ظ„.', wound_changes: 'طھط­ط³ظ† ظƒط¨ظٹط± ظپظٹ ط­ظˆط§ظپ ط§ظ„ط¬ط±ط­ ظˆطھظ‚ظ„ظٹظ„ ط§ظ„ط¥ظپط±ط§ط²ط§طھ.', dressing_id: null, medication_id: null, follow_up_required: false, status: 'IMPROVED_DISCHARGE' },
  { id: 'drev020', registration_diagnosis_id: 'rdx030', review_date: '2026-03-10', nurse_id: 'u003', wound_description: 'ط§ظ„طھط³ط¬ظٹظ„ ط§ظ„ط£ظˆظ„ظٹ â€” ظ‚ط¯ظ… ط³ظƒط±ظٹ ظ…ط¹ ط¶ط¹ظپ طھط±ظˆظٹط©.', wound_changes: 'ط¨ط¯ط، ط§ظ„ط¹ظ„ط§ط¬.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'WORSENED' },
  { id: 'drev021', registration_diagnosis_id: 'rdx030', review_date: '2026-04-02', nurse_id: 'u003', wound_description: 'طھط­ط³ظ† ط¬ط²ط¦ظٹ ظپظٹ ط§ظ„طھط±ظˆظٹط© ظˆط­ط¬ظ… ط§ظ„ط¬ط±ط­.', wound_changes: 'ط´ظپط§ط، طھط¯ط±ظٹط¬ظٹ.', dressing_id: null, medication_id: null, follow_up_required: false, status: 'IMPROVED_DISCHARGE' },
  { id: 'drev022', registration_diagnosis_id: 'rdx031', review_date: '2025-08-15', nurse_id: 'u003', wound_description: 'طھط³ط¬ظٹظ„ ط£ظˆظ„ظٹ â€” ظ‚ط¯ظ… ط³ظƒط±ظٹ.', wound_changes: 'ط¨ط¯ط، ط§ظ„ط¹ظ„ط§ط¬.', dressing_id: null, medication_id: null, follow_up_required: true, status: 'WORSENED' },
  { id: 'drev023', registration_diagnosis_id: 'rdx031', review_date: '2025-09-10', nurse_id: 'u003', wound_description: 'ط´ظپط§ط، طھط§ظ….', wound_changes: 'ط§ظ„طھط¦ط§ظ… ظƒط§ظ…ظ„ ظˆط§ظ„طھط±ط®ظٹطµ.', dressing_id: null, medication_id: null, follow_up_required: false, status: 'IMPROVED_DISCHARGE' }
];


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 10. SCHEDULED_REVIEWS
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

var SCHEDULED_REVIEWS = [
  // TODAY reviews
  { id: 'srev001', registration_diagnosis_id: 'rdx001', created_by_id: 'u001', source_daily_review_id: 'drev003', scheduled_date: '2026-09-05', status: 'SCHEDULED', notes: 'ظ…طھط§ط¨ط¹ط© ط´ظپط§ط، ط§ظ„ط¬ط±ط­ ظˆطھط؛ظٹظٹط± ط§ظ„ط¶ظ…ط§ط¯ط© ط§ظ„ظ…ط³طھظ…ط±ط© ط¨ط¹ط¯ ط§ظ„طھط±ط®ظٹطµ ظ…ظ† ط§ظ„ظ…طھط§ط¨ط¹ط© ط§ظ„ظٹظˆظ…ظٹط©.', created_at: '2026-08-28T10:00:00Z', completed_at: null },
  { id: 'srev002', registration_diagnosis_id: 'rdx004', created_by_id: 'u001', source_daily_review_id: 'drev005', scheduled_date: '2026-09-05', status: 'SCHEDULED', notes: 'طھظ‚ظٹظٹظ… ط§ظ„ط¬ط±ط­ ط§ظ„ط¬ط±ط§ط­ظٹ ظˆط¥ط²ط§ظ„ط© ط®ظٹظˆط· ط§ظ„ط¹ظ…ظ„ظٹط©.', created_at: '2026-08-30T10:00:00Z', completed_at: null },
  // SCHEDULED (future)
  { id: 'srev003', registration_diagnosis_id: 'rdx008', created_by_id: 'u001', source_daily_review_id: 'drev006', scheduled_date: '2026-09-08', status: 'SCHEDULED', notes: 'ظ…طھط§ط¨ط¹ط© طھط±ظˆظٹط© ط§ظ„ظ‚ط¯ظ… ظˆظ†ط³ط¨ط© ط´ظپط§ط، ط§ظ„ظ‚ط±ط­ط© ظˆطھط؛ظٹظٹط± ط§ظ„ط¶ظ…ط§ط¯ ط§ظ„ظپط¶ظٹ.', created_at: '2026-09-01T10:00:00Z', completed_at: null },
  { id: 'srev004', registration_diagnosis_id: 'rdx011', created_by_id: 'u001', source_daily_review_id: 'drev007', scheduled_date: '2026-09-12', status: 'SCHEDULED', notes: 'ظپط­طµ ظ†ط¯ط¨ط© ط§ظ„ط¹ظ…ظ„ظٹط§طھ ظˆطھط؛ظٹظٹط± ط§ظ„ط¶ظ…ط§ط¯ ط§ظ„ظˆط§ظ‚ظٹ.', created_at: '2026-09-03T10:00:00Z', completed_at: null },
  { id: 'srev005', registration_diagnosis_id: 'rdx002', created_by_id: 'u001', source_daily_review_id: 'drev002', scheduled_date: '2026-09-14', status: 'SCHEDULED', notes: 'ظ…طھط§ط¨ط¹ط© ظ…ط³طھظ‚ظ„ط© ظ„ظ‚ط±ط­ط© ط§ظ„ط¹ط¬ط² ط§ظ„ط«ط§ظ†ظˆظٹط© ظ„ظ„ظ…ط±ظٹط¶.', created_at: '2026-08-31T10:00:00Z', completed_at: null },
  { id: 'srev006', registration_diagnosis_id: 'rdx020', created_by_id: 'u001', source_daily_review_id: null, scheduled_date: '2026-09-10', status: 'SCHEDULED', notes: 'ظپط­طµ ط³ظ„ط§ظ…ط© ط§ظ„ط¬ظ„ط¯ ط§ظ„ظ…ط­ظٹط· ط¨ظپط؛ط±ط© ط§ظ„ظ‚ظˆظ„ظˆظ†.', created_at: '2026-09-02T10:00:00Z', completed_at: null },
  { id: 'srev007', registration_diagnosis_id: 'rdx021', created_by_id: 'u001', source_daily_review_id: 'drev011', scheduled_date: '2026-09-11', status: 'SCHEDULED', notes: 'ظ…طھط§ط¨ط¹ط© طھظ‚ظٹظٹظ… ط§ظ„ط¶ظ…ط§ط¯ط© ط§ظ„ظپط¶ظٹط© ظˆط§ظ„طھظ†ط¶ظٹط±.', created_at: '2026-09-04T10:00:00Z', completed_at: null },
  { id: 'srev008', registration_diagnosis_id: 'rdx023', created_by_id: 'u001', source_daily_review_id: 'drev012', scheduled_date: '2026-09-09', status: 'SCHEDULED', notes: 'ظپط­طµ ط¥ظپط±ط§ط²ط§طھ ط§ظ„ط³ط§ظ‚ ظˆط§ظ„ط¶ظ…ط§ط¯ ط§ظ„ط¶ط§ط؛ط·.', created_at: '2026-09-03T10:00:00Z', completed_at: null },
  { id: 'srev009', registration_diagnosis_id: 'rdx024', created_by_id: 'u001', source_daily_review_id: 'drev013', scheduled_date: '2026-09-14', status: 'SCHEDULED', notes: 'ظ…ط±ط§ط¬ط¹ط© ط®طھط§ظ…ظٹط© ظ„ظ„ط¬ط±ط­ ط§ظ„ط¬ط±ط§ط­ظٹ.', created_at: '2026-09-05T10:00:00Z', completed_at: null },
  { id: 'srev010', registration_diagnosis_id: 'rdx025', created_by_id: 'u001', source_daily_review_id: 'drev014', scheduled_date: '2026-09-10', status: 'SCHEDULED', notes: 'ظ…طھط§ط¨ط¹ط© طھط؛ظٹط±ط§طھ ظ‚ط±ط­ط© ط§ظ„ظƒط¹ط¨.', created_at: '2026-09-04T10:00:00Z', completed_at: null },
  { id: 'srev011', registration_diagnosis_id: 'rdx027', created_by_id: 'u001', source_daily_review_id: 'drev015', scheduled_date: '2026-09-13', status: 'SCHEDULED', notes: 'ظپط­طµ ط§ظ„ط§ظ„طھط¦ط§ظ… ظˆط¥ط²ط§ظ„ط© ط§ظ„ط؛ط±ط².', created_at: '2026-09-05T10:00:00Z', completed_at: null },
  // MISSED (overdue)
  { id: 'srev012', registration_diagnosis_id: 'rdx017', created_by_id: 'u001', source_daily_review_id: null, scheduled_date: '2026-09-01', status: 'MISSED', notes: 'ظ…طھط§ط¨ط¹ط© طھظ†ط¸ظٹظپ ظˆطھظ‚ظٹظٹظ… ط¹ظ…ظ‚ ط§ظ„ظ‚ط±ط­ط© ظˆطھط£ط®ط± ط§ظ„ظ…ط±ظٹط¶ط© ط¹ظ† ط§ظ„ط­ط¶ظˆط±.', created_at: '2026-08-25T10:00:00Z', completed_at: null },
  { id: 'srev013', registration_diagnosis_id: 'rdx003', created_by_id: 'u001', source_daily_review_id: null, scheduled_date: '2026-08-28', status: 'MISSED', notes: 'ظ…طھط§ط¨ط¹ط© ظ†ط³ط¨ط© ط§ظ„ط¥ظپط±ط§ط²ط§طھ ط§ظ„ظ†ط¶ط­ظٹط© ظˆط§ظ„ط¶ط؛ط· ط§ظ„ط¶ط§ط؛ط· ط¹ظ„ظ‰ ط§ظ„ط³ط§ظ‚.', created_at: '2026-08-20T10:00:00Z', completed_at: null },
  // COMPLETED
  { id: 'srev014', registration_diagnosis_id: 'rdx019', created_by_id: 'u001', source_daily_review_id: 'drev010', scheduled_date: '2026-09-03', status: 'COMPLETED', notes: 'ظ…ط±ط§ط¬ط¹ط© ظ†ظ‡ط§ط¦ظٹط© ظ„ظ…ظˆط¶ط¹ ط§ظ„ظ‚ط³ط·ط±ط© ط¨ط¹ط¯ ط§ظ„ط´ظپط§ط، ط§ظ„ظƒط§ظ…ظ„.', created_at: '2026-08-27T10:00:00Z', completed_at: '2026-09-03T11:15:00Z' },
  { id: 'srev015', registration_diagnosis_id: 'rdx010', created_by_id: 'u001', source_daily_review_id: null, scheduled_date: '2026-09-02', status: 'COMPLETED', notes: 'ظ…طھط§ط¨ط¹ط© ط§ظ„طھط¦ط§ظ… ظ‚ط±ط­ط© ط§ظ„ظƒطھظپ ظˆطھظ‚ظٹظٹظ… ط§ظ„ط¶ظ…ط§ط¯.', created_at: '2026-08-26T10:00:00Z', completed_at: '2026-09-02T10:40:00Z' },
  // CANCELLED
  { id: 'srev016', registration_diagnosis_id: 'rdx013', created_by_id: 'u001', source_daily_review_id: null, scheduled_date: '2026-08-30', status: 'CANCELLED', notes: 'ط³ظپط± ط§ظ„ظ…ط±ظٹط¶ط© ط®ط§ط±ط¬ ط§ظ„ظ…ظ†ط·ظ‚ط© ظˆظ…طھط§ط¨ط¹ط© ط§ظ„ط­ط§ظ„ط© ظپظٹ ظ…ط³طھط´ظپظ‰ ط¢ط®ط± ط¨ظ†ط§ط،ظ‹ ط¹ظ„ظ‰ ط·ظ„ط¨ ط§ظ„ط£ط³ط±ط©.', created_at: '2026-08-22T10:00:00Z', completed_at: null },
  // Historical (discharged patient reg099)
  { id: 'srev017', registration_diagnosis_id: 'rdx028', created_by_id: 'u001', source_daily_review_id: 'drev017', scheduled_date: '2026-05-10', status: 'COMPLETED', notes: 'ظ…ط±ط§ط¬ط¹ط© ط®طھط§ظ…ظٹط© ط¨ط¹ط¯ ط§ظ„ط´ظپط§ط، ظˆطھط£ظƒظٹط¯ ط§ظ„ط®ط±ظˆط¬.', created_at: '2026-05-01T10:00:00Z', completed_at: '2026-05-10T11:00:00Z' }
];


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 11. AUDIT LOG (placeholder)
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

var AUDIT_LOG = [];


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 12. CURRENT USER & SESSION
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

var CURRENT_USER_ID = 'u001'; // Default logged-in user


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 13. ID COUNTERS (for generating new IDs)
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

var _counters = {
  patient: 100, request: 400, registration: 100, rpmh: 100,
  rdx: 100, rdxp: 100, dailyReview: 100, scheduledReview: 100, auditLog: 1
};

function _genId(prefix) {
  _counters[prefix] = (_counters[prefix] || 0) + 1;
  return prefix + '-' + String(_counters[prefix]).padStart(3, '0');
}


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 14. HELPER UTILITIES
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

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
  if (!isoStr) return 'ط؛ظٹط± ظ…طھظˆظپط±';
  var dateOnly = isoStr.split('T')[0];
  var parts = dateOnly.split('-');
  if (parts.length === 3) {
    return parts[2].padStart(2, '0') + '/' + parts[1].padStart(2, '0') + '/' + parts[0];
  }
  return isoStr;
}

function _formatDateTime(isoStr) {
  if (!isoStr) return 'ط؛ظٹط± ظ…طھظˆظپط±';
  var parts = isoStr.split('T');
  var datePart = _formatDate(parts[0]);
  var timePart = parts[1] ? parts[1].substring(0, 5) : '';
  return timePart ? datePart + ' â€” ' + timePart : datePart;
}

function _genderAr(g) {
  if (g === 'MALE') return 'ط°ظƒط±';
  if (g === 'FEMALE') return 'ط£ظ†ط«ظ‰';
  return g || '';
}

function _clone(obj) {
  return JSON.parse(JSON.stringify(obj));
}


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 15. API â€” LOOKUP FUNCTIONS
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

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


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 16. API â€” HOSPITAL SYSTEM (External)
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

function searchHospitalPatient(nationalId) {
  if (!nationalId) return null;
  for (var i = 0; i < HOSPITAL_PATIENTS.length; i++) {
    if (HOSPITAL_PATIENTS[i].national_id === nationalId) return _clone(HOSPITAL_PATIENTS[i]);
  }
  return null;
}


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 17. API â€” PATIENTS
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

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



// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 18. API â€” REGISTRATION REQUESTS
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

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


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 19. API â€” REGISTRATIONS
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

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


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 20. API â€” REGISTRATION DIAGNOSES
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

function getDiagnosesByRegistrationId(regId) {
  return _filter(REGISTRATION_DIAGNOSES, 'registration_id', regId);
}

function getRegistrationDiagnosisById(rdxId) {
  return _find(REGISTRATION_DIAGNOSES, rdxId);
}


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 21. API â€” REGISTRATION PMH
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

function getPMHByRegistrationId(regId) {
  return _filter(REGISTRATION_PMH, 'registration_id', regId);
}


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 22. API â€” PROCEDURES
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

function getProceduresByRegistrationDiagnosisId(rdxId) {
  return _filter(REGISTRATION_DIAGNOSIS_PROCEDURES, 'registration_diagnosis_id', rdxId);
}

function getProceduresByRegistrationId(regId) {
  var diagIds = getDiagnosesByRegistrationId(regId).map(function (d) { return d.id; });
  return REGISTRATION_DIAGNOSIS_PROCEDURES.filter(function (p) {
    return diagIds.indexOf(p.registration_diagnosis_id) !== -1;
  });
}


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 23. API â€” DAILY REVIEWS
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

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


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 24. API â€” SCHEDULED REVIEWS
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

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
  review.notes = (review.notes ? review.notes + ' | ط¥ظ„ط؛ط§ط،: ' : 'ط¥ظ„ط؛ط§ط،: ') + (reason || '');
  _addAuditLog('CANCEL', 'SCHEDULED_REVIEW', id, { reason: reason });
  return _clone(review);
}


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 25. API â€” PATIENT HISTORY (Consolidated View)
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

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


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 26. API â€” ENRICHED REGISTRATION (for daily review page)
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

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
        diagnoses_names: prDiags.map(function (d) { return lookup('diagnoses', d.diagnosis_id); }).join('طŒ '),
        reviews_count: prReviews.length,
        outcome: prReviews.length > 0 ? prReviews[0].status : 'N/A'
      };
    })
  };
}


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 27. API â€” ENRICHED REGISTERED PATIENTS LIST
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

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


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 28. API â€” ENRICHED SCHEDULED REVIEWS LIST
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

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


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 29. API â€” URL & MISC HELPERS
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

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


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 30. AUDIT LOG
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

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


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 31. DAILY REVIEW STATUS HELPERS
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

var DAILY_REVIEW_STATUS_MAP = {
  'IMPROVED_DISCHARGE':  { label: 'طھط­ط³ظ† â€” طھط±ط®ظٹطµ', labelEn: 'Improved â€“ Discharge', badgeClass: 'badge-light-success', icon: 'bi-check-circle-fill' },
  'IMPROVED_FOLLOWUP':   { label: 'طھط­ط³ظ† â€” ظ…طھط§ط¨ط¹ط©', labelEn: 'Improved â€“ Follow-up', badgeClass: 'badge-light-primary', icon: 'bi-arrow-repeat' },
  'WORSENED':            { label: 'طھظپط§ظ‚ظ…', labelEn: 'Worsened', badgeClass: 'badge-light-warning', icon: 'bi-exclamation-triangle-fill' },
  'DECEASED':            { label: 'ظˆظپط§ط©', labelEn: 'Deceased', badgeClass: 'badge-light-danger', icon: 'bi-x-circle-fill' }
};

var SCHEDULED_REVIEW_STATUS_MAP = {
  'SCHEDULED':  { label: 'ظ…ط¬ط¯ظˆظ„ط©', badgeClass: 'badge-light-primary', icon: 'bi-calendar-event' },
  'COMPLETED':  { label: 'ظ…ظƒطھظ…ظ„ط©', badgeClass: 'badge-light-success', icon: 'bi-check-circle-fill' },
  'CANCELLED':  { label: 'ظ…ظ„ط؛ط§ط©', badgeClass: 'badge-light-danger', icon: 'bi-x-circle-fill' },
  'MISSED':     { label: 'ظپط§ط¦طھط©', badgeClass: 'badge-light-warning', icon: 'bi-exclamation-triangle-fill' }
};

var REQUEST_STATUS_MAP = {
  'PENDING':    { label: 'ظ‚ظٹط¯ ط§ظ„ظ…ط±ط§ط¬ط¹ط©', badgeClass: 'badge-light-warning', icon: 'bi-clock-fill' },
  'APPROVED':   { label: 'طھظ…طھ ط§ظ„ظ…ظˆط§ظپظ‚ط©', badgeClass: 'badge-light-success', icon: 'bi-check-circle-fill' },
  'REJECTED':   { label: 'ظ…ط±ظپظˆط¶', badgeClass: 'badge-light-danger', icon: 'bi-x-circle-fill' },
  'CANCELLED':  { label: 'ظ…ظ„ط؛ظٹ', badgeClass: 'badge-light-secondary', icon: 'bi-slash-circle' }
};


// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
// 32. GLOBAL EXPORT
// â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ

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


if (typeof window !== 'undefined') { window.WCCMS = window.WCCMS || WCCMS; }
export default WCCMS;
export { WCCMS };

