<?php

/**
 * Hospital-owned tables that WCCMS reads directly (no views, no copies).
 *
 * Locally (tests, developer machines) the migration creates mr_patient_tb and per_employee_tb, so the defaults
 * below point there. In production set the env variables to the real hospital tables, schema-qualified,
 * e.g. HOSPITAL_PATIENTS_TABLE=HIS.PATIENTS.
 *
 * The column names are not configurable at runtime: they are written in the code. When the hospital names differ,
 * fill `columns` and run `php artisan wccms:map-hospital-columns` once (see MIGRATION_GUIDE.md).
 */
return [

    'tables' => [
        'patients' => env('HOSPITAL_PATIENTS_TABLE', 'mr_patient_tb'),
        'employees' => env('HOSPITAL_EMPLOYEES_TABLE', 'per_employee_tb'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Column map: name used by WCCMS => real hospital column name
    |--------------------------------------------------------------------------
    |
    | Leave a value null when the hospital column already has the WCCMS name.
    |
    | "required": used in WHERE / JOIN / ORDER BY, so it must exist in the hospital
    | table. When the hospital has no such column, map it to the closest one
    | (e.g. MR_PAT_NATIONAL_ID => the ID number column) rather than leaving it.
    | "display": only read for screens; when it does not exist, leave it null and
    | the screens show it empty.
    |
    */

    'columns' => [
        // Patients table
        'MR_PAT_CODE' => null,                    // required, primary key
        'MR_PAT_ID_NO' => null,                   // required
        'MR_PAT_FULL_NAME' => null,               // required
        'MR_PAT_NATIONAL_ID' => null,             // required
        'MR_PAT_HOSPITAL_PATIENT_ID' => null,     // required
        'MR_PAT_PHONE' => null,                   // display
        'MR_PAT_GENDER' => null,                  // display
        'MR_PAT_DOB' => null,                     // display
        'MR_PAT_SUPERVISING_DOCTOR_CD' => null,   // display, employee code of the doctor (employees table key)
        'MR_PAT_HOSPITAL_DEPARTMENT' => null,     // display
        'MR_PAT_HOSPITAL_ADMISSION_DATE' => null, // display

        // Employees table
        'PER_EMP_CODE' => null,                   // required, primary key
        'PER_EMP_FULL_NAME' => null,              // required
        'PER_EMP_JOB_TITLE' => null,              // display
        'PER_EMP_IS_ACTIVE' => null,              // display
    ],

];
