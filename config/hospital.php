<?php

/**
 * The hospital system's tables (Oracle schema HOS) that WCCMS reads directly. WCCMS never writes to them.
 *
 * Every assumption about the hospital database lives in this file, so a wrong guess is fixed here and nowhere else.
 * The facts come from the full DDL export of the hospital database (constraints, the view HOS.INPATIENT_VW and the
 * hospital's own PL/SQL). Locally (tests, developer machines) the migration creates copies of these tables with the
 * same names, columns and composite keys.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Hospital number (HOS_NO)
    |--------------------------------------------------------------------------
    |
    | One database serves several hospitals: every hospital table carries HOS_NO and every primary key is
    | (code, HOS_NO), so the same patient or employee code can exist in two hospitals. Every read of a
    | hospital table, and every WCCMS row that points at a patient or an employee, uses this number.
    |
    | 1 = Al-Shifa (مستشفى الشفاء). Sources in the hospital database:
    |   - trigger HOS.LAB_ORDER_TB_MREF: "--1 Shifa Hospital" next to IF P_HOS_NO=1
    |   - procedure HOS.GET_PATIENT_LOCATION: IF hos_code=1 then hos_name:='الشفاء'
    | Other numbers seen there: 2 Al-Rantisi, 3 Al-Nasr, 4 Al-Durra, 5 Nasser, 6 Eye, 7 Al-Aqsa,
    | 8 Tal al-Sultan, 9 Al-Najjar, 10 Kamal Adwan, 11 European, 12 Beit Hanoun, 16 Indonesian; 0 = the central DB.
    |
    */

    'hospital_no' => (int) env('HOSPITAL_NO', 1),

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    |
    | "schema" prefixes every table below: HOS in production (WCCMS connects as its own user WCCMS), empty locally.
    |
    */

    'schema' => env('HOSPITAL_SCHEMA'),

    'tables' => [
        'patients' => 'MR_PATIENT_TB',
        'employees' => 'EMPLOYEE_TB',
        'admissions' => 'ADMISSION_PATIENT_TB',
        'inpatient_stays' => 'INPATIENT_ADM_TB',
        'locations' => 'C_LOCATION_TB',
        'sexes' => 'C_SEX_TB',
        'jobs' => 'C_JOB_TB',
        'hospitals' => 'C_HOSPITAL_TB',
    ],

    /*
    |--------------------------------------------------------------------------
    | Foreign keys to the hospital tables
    |--------------------------------------------------------------------------
    |
    | Composite foreign keys such as (WC_REG_PATIENT_CD, WC_REG_HOS_NO) -> MR_PATIENT_TB (MRP_PATREC_CODE, HOS_NO).
    | Turn on only when WCCMS's tables are in the same database as the hospital tables (a foreign key cannot cross
    | a database link) and WCCMS has the REFERENCES privilege. Off: the application checks every link before saving
    | (App\Models\Concerns\BelongsToHospital and the Hospital*Exists validation rules), which it does either way.
    |
    | Read by the local migration; on Oracle the matching section of wccms_oracle_schema.sql is enabled by hand.
    |
    */

    'foreign_keys' => (bool) env('HOSPITAL_FOREIGN_KEYS', false),

    /*
    |--------------------------------------------------------------------------
    | Admissions
    |--------------------------------------------------------------------------
    |
    | Same rules as the hospital's view HOS.INPATIENT_VW: a patient is in hospital when an admission has no
    | ADMISSION_OUT_DATE and its stay is not cancelled (INPATIENT_CANCELED = 0).
    |
    */

    'admission' => [
        // ADMISSION_PATIENT_TB column holding the doctor (FK FK_EMP_SERIAL_ENTER_DOC_CD -> EMPLOYEE_TB.EMP_SERIAL).
        'doctor_column' => 'ADMISSION_ENTER_DOC_CD',

        // INPATIENT_ADM_TB column holding the current ward (INPATIENT_VW names it LOC_NAME / SUP_LOC_NAME).
        'ward_column' => 'INPATIENT_SUP_LOC_CD',

        // INPATIENT_CANCELED value of a stay that is not cancelled.
        'not_cancelled_value' => 0,
    ],

    'employees' => [
        // EMP_ACTIVE value of an employee at work (the hospital's code filters EMP_ACTIVE = 1).
        'active_value' => 1,

        // C_JOB_TB.JOB_CODE values (EMPLOYEE_TB.EMP_JOB_CD), comma separated, that fill the employee pick lists:
        // the doctor fields list doctors only, the nurse fields nurses only, and the wound care team field both.
        // An empty list does not filter (the field lists every active employee); wccms:check-hospital warns about it.
        'job_codes' => [
            // 2: the hospital's own doctor lists filter on it (GET_DOCTORS_PROC, GET_DOCTORS_NAMES: EMP_JOB_CD = 2).
            'doctors' => env('HOSPITAL_DOCTOR_JOB_CODES', '2'),
            // Not known yet: no nurse code appears in the hospital's code. Set it from C_JOB_TB.
            'nurses' => env('HOSPITAL_NURSE_JOB_CODES', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sex
    |--------------------------------------------------------------------------
    |
    | The hospital's own code disagrees on the codes (DECODE(MRP_SEX_CD,1,'انثى','ذكر') in 12 places, 1 = male
    | in 2), so no code is assumed: screens show C_SEX_TB.SEX_NAME_AR and reports classify by an exact match of
    | C_SEX_TB.SEX_NAME_EN. Anything else is reported as unknown and logged by wccms:check-hospital.
    |
    */

    'sex' => [
        'male_name_en' => 'Male',
        'female_name_en' => 'Female',
    ],

];
