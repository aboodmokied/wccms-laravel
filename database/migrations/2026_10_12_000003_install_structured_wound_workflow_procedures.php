<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $this->dropProcedures();
        DB::unprepared($this->insertAssessmentProcedure());
        DB::unprepared($this->acceptRegistrationProcedure());
        DB::unprepared($this->addWoundProcedure());
        DB::unprepared($this->completeReviewProcedure());
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $this->dropProcedures();
        }
    }

    private function dropProcedures(): void
    {
        foreach ([
            'SP_WC_ACCEPT_REGISTRATION_WITH_ASSESSMENTS',
            'SP_WC_ADD_WOUND_WITH_ASSESSMENT',
            'SP_WC_COMPLETE_REVIEW_WITH_ASSESSMENTS',
            'SP_WC_INSERT_STRUCTURED_ASSESSMENT',
        ] as $procedure) {
            DB::unprepared("DROP PROCEDURE IF EXISTS {$procedure}");
        }
    }

    private function insertAssessmentProcedure(): string
    {
        return <<<'SQL'
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
END
SQL;
    }

    private function acceptRegistrationProcedure(): string
    {
        return <<<'SQL'
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
END
SQL;
    }

    private function addWoundProcedure(): string
    {
        return <<<'SQL'
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
END
SQL;
    }

    private function completeReviewProcedure(): string
    {
        return <<<'SQL'
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
END
SQL;
    }
};
