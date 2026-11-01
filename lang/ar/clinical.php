<?php

/**
 * Clinical glossary (spec 014): medical terms are displayed in English on every page and are never translated.
 * Administrative text stays in the other Arabic language files.
 */
return [
    'fields' => [
        'diagnosis' => 'Diagnosis',
        'wound_type' => 'Type of Wound',
        'wound_status' => 'Wound Status',
        'exudate_amount' => 'Exudate Amount',
        'exudate_type' => 'Exudate Type',
        'odor' => 'Odor',
        'pain_severity' => 'Pain Severity',
        'pain_at_dressing' => 'Pain at Dressing Change?',
        'fever' => 'Fever',
        'nutrition' => 'Nutrition',
        'location' => 'Location',
        'laterality' => 'Laterality',
        'location_notes' => 'Location Details',
        'length_mm' => 'Length',
        'width_mm' => 'Width',
        'depth_mm' => 'Depth',
        'measurements' => 'Measurements',
        'treatment' => 'Treatment',
        'wound_bed' => 'Wound Bed',
        'wound_margin' => 'Wound Margins',
        'swab_taken' => 'Swab Taken?',
        'dressing' => 'Type of DSG',
        'medication' => 'Medication',
        'procedure' => 'Procedure',
        'pmh' => 'PMH',
        'pmh_notes' => 'PMH Notes',
        'allergies' => 'Allergies?',
        'allergies_description' => 'Allergy Description',
        'treatment_notes' => 'Treatment Plan / Notes',
        'clinical_observation' => 'Clinical Observation',
        'wound_changes' => 'Wound Changes',
        'wound_outcome' => 'Wound Outcome',
        'overall_outcome' => 'Overall Outcome',
    ],

    /** Assessment choice category => field label key. */
    'choice_categories' => [
        'WOUND_STATUS' => 'Wound Status',
        'WOUND_BED' => 'Wound Bed',
        'WOUND_MARGIN' => 'Wound Margins',
        'EXUDATE_AMOUNT' => 'Exudate Amount',
        'EXUDATE_TYPE' => 'Exudate Type',
        'ODOR' => 'Odor',
        'PAIN_SEVERITY' => 'Pain Severity',
        'FEVER' => 'Fever',
        'NUTRITION' => 'Nutrition',
        'LOCATION' => 'Location',
        'LATERALITY' => 'Laterality',
    ],

    /** WC_DREV_OVERALL_STATUS */
    'overall_outcomes' => [
        'IMPROVED_DISCHARGE' => 'Improved – Discharged',
        'IMPROVED_SCHEDULED' => 'Improved – Follow-up Scheduled',
        'IMPROVED_STAY' => 'Improved – Continuing Treatment',
        'WORSENED_SCHEDULED' => 'Worsened – Follow-up Scheduled',
        'WORSENED_ADMISSION' => 'Worsened – Admitted',
        'WORSENED_STAY' => 'Worsened – Continuing Treatment',
        'DEATH' => 'Death',
    ],

    /** WC_DREVD_STATUS */
    'wound_outcomes' => [
        'IMPROVED_DISCHARGE' => 'Healed',
        'IMPROVED_FOLLOWUP' => 'Improved – Follow-up',
        'WORSENED' => 'Worsened',
        'DECEASED' => 'Deceased',
    ],

    /** Outcome groups of the dashboard and patient history report (ReportingService). */
    'outcome_groups' => [
        'DISCHARGED' => 'Improved – Discharged',
        'FOLLOWUP' => 'Improved – Follow-up',
        'WORSENED' => 'Worsened',
        'DECEASED' => 'Death',
    ],

    /** Monthly report Comment column */
    'case_outcomes' => [
        'ONGOING' => 'Ongoing Treatment',
        'DISCHARGED' => 'Discharged',
        'DEATH' => 'Death',
        'HOSPITAL_TRANSFER' => 'Hospital Transfer',
    ],

    'genders' => [
        'MALE' => 'Male',
        'FEMALE' => 'Female',
        'UNSPECIFIED' => 'Unspecified',
        'UNKNOWN' => 'Unknown',
    ],

    /** Past medical history recorded as "no comorbidities" (workbook value). */
    'no_comorbidities' => 'No',

    /** Procedure printed when a swab was taken (workbook value). */
    'swab_procedure' => 'swap culture',
];
