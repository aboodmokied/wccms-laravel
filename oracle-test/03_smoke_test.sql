-- ============================================================================
-- 03_smoke_test.sql: calls the 4 WCCMS procedures with XML payloads and checks what they wrote.
-- Run as WCCMS (the application's user), after 01_hos_stub.sql and the two WCCMS scripts.
-- Prints one PASS/FAIL line per check, then "SMOKE TEST: PASS" or "SMOKE TEST: FAIL".
-- Exit code = number of failed checks (0 = all passed). Everything is rolled back at the end.
-- ============================================================================

WHENEVER SQLERROR EXIT FAILURE ROLLBACK
SET DEFINE OFF
SET SERVEROUTPUT ON SIZE 1000000
SET FEEDBACK OFF
SET LINESIZE 220
SET PAGESIZE 200

PROMPT === Compilation errors in WCCMS (expected: none) ===
COLUMN name FORMAT A28
COLUMN text FORMAT A120
SELECT name, type, line, position, text FROM user_errors ORDER BY name, sequence;

VARIABLE failures NUMBER

-- The procedures must read '12.5' from the XML whatever the session's decimal separator.
ALTER SESSION SET NLS_NUMERIC_CHARACTERS = ',.';

DECLARE
    c_user      CONSTANT VARCHAR2(36) := 'aaaaaaaa-0000-4000-8000-000000000001';
    c_req1      CONSTANT VARCHAR2(36) := 'bbbbbbbb-0000-4000-8000-000000000001';
    c_req2      CONSTANT VARCHAR2(36) := 'bbbbbbbb-0000-4000-8000-000000000002';
    -- Sent escaped in the XML, must be stored decoded
    c_allergy_xml CONSTANT VARCHAR2(400) := 'بنسلين &amp; لاتكس &lt;شديدة&gt; &apos;أ&apos; &quot;ب&quot;';
    c_allergy_txt CONSTANT VARCHAR2(400) := 'بنسلين & لاتكس <شديدة> ''أ'' "ب"';

    v_pass      NUMBER := 0;
    v_fail      NUMBER := 0;
    v_diag      VARCHAR2(36);
    v_wtype     VARCHAR2(36);
    v_dressing  VARCHAR2(36);
    v_pmh       VARCHAR2(36);
    v_reg       VARCHAR2(36);
    v_wound1    VARCHAR2(36);
    v_wound2    VARCHAR2(36);
    v_review    VARCHAR2(36);
    v_again     VARCHAR2(36);
    v_result    VARCHAR2(36);
    v_count     NUMBER;
    v_number    NUMBER;
    v_date      DATE;
    v_text      VARCHAR2(4000);
    v_xml       XMLTYPE;
    v_codes     WC_CODE_LIST;

    PROCEDURE assert_true(p_name IN VARCHAR2, p_ok IN BOOLEAN)
    IS
    BEGIN
        IF p_ok THEN
            v_pass := v_pass + 1;
            DBMS_OUTPUT.PUT_LINE('PASS  ' || p_name);
        ELSE
            v_fail := v_fail + 1;
            DBMS_OUTPUT.PUT_LINE('FAIL  ' || p_name);
        END IF;
    END assert_true;

    /** Called from an exception handler with SQLCODE and SQLERRM of the error that was raised. */
    PROCEDURE expect_error(p_name IN VARCHAR2, p_code IN VARCHAR2, p_sqlcode IN NUMBER, p_sqlerrm IN VARCHAR2)
    IS
    BEGIN
        assert_true(
            p_name || ' -> ' || p_code || CASE WHEN p_sqlcode <> -20001 OR INSTR(p_sqlerrm, p_code) = 0
                THEN ' (got: ' || SUBSTR(p_sqlerrm, 1, 150) || ')' END,
            p_sqlcode = -20001 AND INSTR(p_sqlerrm, p_code) > 0
        );
    END expect_error;

    FUNCTION ch(p_category IN VARCHAR2, p_key IN VARCHAR2) RETURN VARCHAR2
    IS
        v_code VARCHAR2(36);
    BEGIN
        SELECT WC_ACH_CODE INTO v_code FROM WC_ASSESSMENT_CHOICE_TB
        WHERE WC_ACH_CATEGORY = p_category AND WC_ACH_KEY = p_key;
        RETURN v_code;
    END ch;

    /** <name>value</name>; the value must already be XML-escaped. */
    FUNCTION x(p_name IN VARCHAR2, p_value IN VARCHAR2) RETURN VARCHAR2
    IS
    BEGIN
        RETURN '<' || p_name || '>' || p_value || '</' || p_name || '>';
    END x;

    FUNCTION initial_wound RETURN VARCHAR2
    IS
    BEGIN
        RETURN x('diagnosis_code', v_diag)
            || x('wound_type_code', v_wtype)
            || x('location_notes', 'كعب القدم الأيسر')
            || x('wound_status_choice_code', ch('WOUND_STATUS', 'FIRST_VISIT'))
            || x('exudate_amount_choice_code', ch('EXUDATE_AMOUNT', 'LOW'))
            || x('exudate_type_choice_code', ch('EXUDATE_TYPE', 'CLEAR'))
            || x('odor_choice_code', ch('ODOR', 'NONE'))
            || x('pain_severity_choice_code', ch('PAIN_SEVERITY', 'MODERATE'))
            || x('fever_choice_code', ch('FEVER', 'NONE'))
            || x('location_choice_code', ch('LOCATION', 'FOOT'))
            || x('laterality_choice_code', ch('LATERALITY', 'LEFT'))
            || x('nutrition_choice_code', ch('NUTRITION', 'WELL_NOURISHED'))
            || x('wound_bed_choice_codes', x('item', ch('WOUND_BED', 'GRANULATION')) || x('item', ch('WOUND_BED', 'SLOUGHING')))
            || x('wound_margin_choice_codes', x('item', ch('WOUND_MARGIN', 'ERYTHEMA')))
            || x('length_mm', '12.5') || x('width_mm', '8.25') || x('depth_mm', '0')
            || x('pain_at_dressing', '1') || x('swab_taken', '0')
            || x('dressing_code', v_dressing)
            || x('treatment_notes', 'تنظيف وضماد')
            || x('photos', x('item',
                   x('path', 'wounds/smoke-initial.jpg') || x('caption', 'صورة أولى') || x('taken_at', '2026-11-01T09:30')
               ));
    END initial_wound;

    FUNCTION accept_payload(p_nurse IN VARCHAR2, p_wct IN VARCHAR2 DEFAULT NULL) RETURN VARCHAR2
    IS
    BEGIN
        RETURN '<payload>'
            || x('clinic_nurse_code', p_nurse)
            || CASE WHEN p_wct IS NOT NULL THEN x('wct_employee_code', p_wct) END
            || x('entry_user_code', c_user)
            || x('assessed_at', '2026-11-01 09:30:00')
            || x('next_visit_date', '2026-11-08')
            || x('follow_up_frequency', 'أسبوعي')
            || x('follow_up_objective', 'متابعة الالتئام')
            || x('has_allergies', '1')
            || x('allergies_description', c_allergy_xml)
            || x('no_comorbidities', '0')
            || x('pmh_codes', x('item', v_pmh))
            || x('diagnoses', x('item', initial_wound))
            || '</payload>';
    END accept_payload;

    FUNCTION follow_up_item(p_wound IN VARCHAR2, p_follow_up IN VARCHAR2) RETURN VARCHAR2
    IS
    BEGIN
        RETURN x('item',
               x('registration_diagnosis_code', p_wound)
            || x('outcome_status', 'IMPROVING')
            || x('follow_up_required', p_follow_up)
            || x('wound_status_choice_code', ch('WOUND_STATUS', 'IMPROVING'))
            || x('exudate_amount_choice_code', ch('EXUDATE_AMOUNT', 'DRY'))
            || x('odor_choice_code', ch('ODOR', 'NONE'))
            || x('pain_severity_choice_code', ch('PAIN_SEVERITY', 'NONE'))
            || x('wound_bed_choice_codes', x('item', ch('WOUND_BED', 'EPITHELIALIZING')))
            || x('length_mm', '10') || x('width_mm', '6')
            || x('pain_at_dressing', '0')
            || x('dressing_code', v_dressing)
            || x('clinical_observation', 'تحسن ملحوظ')
            || x('wound_changes', 'نقص الحجم'));
    END follow_up_item;

    FUNCTION review_payload(p_key IN VARCHAR2, p_overall IN VARCHAR2, p_details IN VARCHAR2) RETURN VARCHAR2
    IS
    BEGIN
        RETURN '<payload>'
            || x('nurse_code', '1002')
            || x('entry_user_code', c_user)
            || x('assessed_at', '2026-11-08 10:00')
            || x('submission_key', p_key)
            || x('overall_status', p_overall)
            || CASE WHEN p_overall = 'IMPROVING' THEN x('next_visit_date', '2026-11-15') END
            || x('follow_up_frequency', 'أسبوعي')
            || x('details', p_details)
            || '</payload>';
    END review_payload;
BEGIN
    -- ------------------------------------------------------------------
    -- 0. The objects are there and valid
    -- ------------------------------------------------------------------
    SELECT COUNT(*) INTO v_count FROM user_tables;
    assert_true('20 WCCMS tables (found ' || v_count || ')', v_count = 20);
    SELECT COUNT(*) INTO v_count FROM user_objects WHERE status = 'INVALID';
    assert_true('no INVALID objects (found ' || v_count || ')', v_count = 0);
    SELECT COUNT(*) INTO v_count FROM user_objects WHERE object_type = 'PROCEDURE'
      AND object_name IN ('SP_WC_INSERT_ASSESSMENT', 'SP_WC_ACCEPT_REGISTRATION', 'SP_WC_COMPLETE_REVIEW', 'SP_WC_ADD_WOUND');
    assert_true('4 procedures exist', v_count = 4);
    SELECT COUNT(*) INTO v_count FROM WC_ASSESSMENT_CHOICE_TB;
    assert_true('46 assessment choices seeded', v_count = 46);

    -- ------------------------------------------------------------------
    -- 1. Helpers
    -- ------------------------------------------------------------------
    BEGIN
        v_xml := XMLTYPE('<payload><a><item>x</item><item></item><item>z</item></a><b>1</b><c>false</c><n>3.75</n></payload>');
        assert_true('FN_WC_XML_ITEM_COUNT counts <item>', FN_WC_XML_ITEM_COUNT(v_xml, 'a') = 3);
        assert_true('FN_WC_XML_ITEM_COUNT of a missing key is 0', FN_WC_XML_ITEM_COUNT(v_xml, 'missing') = 0);
        v_codes := FN_WC_XML_CODES(v_xml, 'a');
        assert_true('FN_WC_XML_CODES keeps order and empty items', v_codes.COUNT = 3 AND v_codes(1) = 'x' AND v_codes(2) IS NULL AND v_codes(3) = 'z');
        assert_true('FN_WC_XML_TEXT of a missing key is NULL', FN_WC_XML_TEXT(v_xml, 'missing') IS NULL);
        assert_true('FN_WC_XML_FLAG 1 / false', FN_WC_XML_FLAG(v_xml, 'b') = 1 AND FN_WC_XML_FLAG(v_xml, 'c') = 0);
        assert_true('FN_WC_XML_NUMBER uses ''.'' under NLS '',.''', FN_WC_XML_NUMBER(v_xml, 'n') = 3.75);
        assert_true('FN_WC_XML_TEXT decodes &amp;lt; once (literal "&lt;" survives)',
            FN_WC_XML_TEXT(XMLTYPE('<p><t>a &amp;lt; b</t></p>'), 't') = 'a &lt; b');
        assert_true('FN_WC_XML_TEXT keeps Arabic and line breaks',
            FN_WC_XML_TEXT(XMLTYPE('<p><t>سطر أول' || CHR(10) || 'سطر ثان</t></p>'), 't') = 'سطر أول' || CHR(10) || 'سطر ثان');
        assert_true('FN_WC_EMPLOYEE_EXISTS: 1001 in hospital 1', FN_WC_EMPLOYEE_EXISTS('1001', 1) = 1);
        assert_true('FN_WC_EMPLOYEE_EXISTS: 2001 not in hospital 1', FN_WC_EMPLOYEE_EXISTS('2001', 1) = 0);
        assert_true('FN_WC_EMPLOYEE_EXISTS: 2001 in hospital 2', FN_WC_EMPLOYEE_EXISTS('2001', 2) = 1);
        assert_true('FN_WC_EMPLOYEE_EXISTS: invalid numbers give 0',
            FN_WC_EMPLOYEE_EXISTS('abc', 1) = 0 AND FN_WC_EMPLOYEE_EXISTS(NULL, 1) = 0
            AND FN_WC_EMPLOYEE_EXISTS('1001.5', 1) = 0 AND FN_WC_EMPLOYEE_EXISTS('1234567890', 1) = 0);
    EXCEPTION
        WHEN OTHERS THEN
            assert_true('helpers: unexpected error ' || SUBSTR(SQLERRM, 1, 300), FALSE);
    END;

    -- ------------------------------------------------------------------
    -- 2. Reference rows: a user (employee 1001 of hospital 1) and two pending requests
    -- ------------------------------------------------------------------
    SELECT MIN(WC_DIAG_CODE) INTO v_diag FROM WC_DIAGNOSIS_TB WHERE WC_DIAG_IS_ACTIVE = 1;
    SELECT MIN(WC_WTYPE_CODE) INTO v_wtype FROM WC_WOUND_TYPE_TB WHERE WC_WTYPE_IS_ACTIVE = 1;
    SELECT MIN(WC_DRESS_CODE) INTO v_dressing FROM WC_DRESSING_TB WHERE WC_DRESS_IS_ACTIVE = 1;
    SELECT MIN(WC_PMH_CODE) INTO v_pmh FROM WC_PMH_TB WHERE WC_PMH_IS_ACTIVE = 1;

    INSERT INTO WC_USERS_TB (PER_USER_CODE, PER_USER_FULL_NAME, PER_USER_USERNAME, PER_USER_ROLE, PER_USER_IS_ACTIVE, PER_USER_EMP_CD, PER_USER_HOS_NO)
    VALUES (c_user, 'ممرضة الاختبار', 'smoke.nurse', 'NURSE', 1, 1001, 1);

    INSERT INTO WC_REQUEST_REG_TB (WC_REQ_CODE, WC_REQ_HOS_NO, WC_REQ_PATIENT_CD, WC_REQ_PAT_NAME_SNAPSHOT, WC_REQ_PAT_ID_SNAPSHOT,
        WC_REQ_NURSE_CD, WC_REQ_ENTRY_USER_CD, WC_REQ_STATUS, WC_REQ_CREATED_AT)
    VALUES (c_req1, 1, 5001, 'محمود إبراهيم سالم عياد', '400000001', 1001, c_user, 'PENDING', FN_WC_NOW());
    INSERT INTO WC_REQUEST_REG_TB (WC_REQ_CODE, WC_REQ_HOS_NO, WC_REQ_PATIENT_CD, WC_REQ_PAT_NAME_SNAPSHOT, WC_REQ_PAT_ID_SNAPSHOT,
        WC_REQ_NURSE_CD, WC_REQ_ENTRY_USER_CD, WC_REQ_STATUS, WC_REQ_CREATED_AT)
    VALUES (c_req2, 1, 5001, 'محمود إبراهيم سالم عياد', '400000001', 1001, c_user, 'PENDING', FN_WC_NOW());

    -- ------------------------------------------------------------------
    -- 3. SP_WC_ACCEPT_REGISTRATION
    -- ------------------------------------------------------------------
    BEGIN
        SP_WC_ACCEPT_REGISTRATION(c_req1, accept_payload('1002'), v_reg);
        assert_true('accept: returns a registration code', v_reg IS NOT NULL);
    EXCEPTION
        WHEN OTHERS THEN
            assert_true('accept: unexpected error ' || SUBSTR(SQLERRM, 1, 300), FALSE);
    END;

    SELECT COUNT(*) INTO v_count FROM WC_REGISTRATION_TB
    WHERE WC_REG_CODE = v_reg AND WC_REG_HOS_NO = 1 AND WC_REG_PATIENT_CD = 5001 AND WC_REG_NURSE_CD = 1002
      AND WC_REG_STATUS = 'ACTIVE' AND WC_REG_HAS_ALLERGIES = 1 AND WC_REG_NO_COMORBIDITIES = 0;
    assert_true('accept: registration row with HOS_NO 1, patient 5001, nurse 1002', v_count = 1);

    SELECT MAX(WC_REG_ALLERGIES_DESCRIPTION) INTO v_text FROM WC_REGISTRATION_TB WHERE WC_REG_CODE = v_reg;
    assert_true('accept: XML entities decoded (& < > '' ") and Arabic kept', v_text = c_allergy_txt);

    SELECT COUNT(*) INTO v_count FROM WC_REQUEST_REG_TB
    WHERE WC_REQ_CODE = c_req1 AND WC_REQ_STATUS = 'ACCEPTED' AND WC_REQ_REVIEWED_BY_CD = 1002;
    assert_true('accept: request ACCEPTED, reviewed by 1002', v_count = 1);

    SELECT MAX(WC_RDIAG_CODE), COUNT(*) INTO v_wound1, v_count FROM WC_REG_DIAGNOSIS_TB
    WHERE WC_RDIAG_REGISTRATION_CD = v_reg AND WC_RDIAG_HOS_NO = 1 AND WC_RDIAG_CLINICAL_EMP_CD = 1002;
    assert_true('accept: one wound with HOS_NO 1', v_count = 1);

    SELECT COUNT(*) INTO v_count FROM WC_WOUND_ASSESSMENT_TB
    WHERE WC_WA_REG_DIAGNOSIS_CD = v_wound1 AND WC_WA_HOS_NO = 1 AND WC_WA_TYPE = 'INITIAL'
      AND WC_WA_CLINICAL_EMP_CD = 1002 AND WC_WA_WCT_EMP_CD IS NULL
      AND WC_WA_LENGTH_MM = 12.5 AND WC_WA_WIDTH_MM = 8.25 AND WC_WA_DEPTH_MM = 0
      AND WC_WA_PAIN_AT_DRESSING = 1 AND WC_WA_SWAB_TAKEN = 0 AND WC_WA_FOLLOW_UP_REQUIRED = 1
      AND WC_WA_ASSESSED_AT = TO_DATE('2026-11-01 09:30:00', 'YYYY-MM-DD HH24:MI:SS');
    assert_true('accept: initial assessment with HOS_NO 1 and decimals 12.5 / 8.25', v_count = 1);

    SELECT COUNT(*) INTO v_count FROM WC_ASSESSMENT_FINDING_TB f
    JOIN WC_WOUND_ASSESSMENT_TB a ON a.WC_WA_CODE = f.WC_AF_ASSESSMENT_CD
    WHERE a.WC_WA_REG_DIAGNOSIS_CD = v_wound1;
    assert_true('accept: 3 findings (2 wound bed + 1 margin)', v_count = 3);

    SELECT COUNT(*) INTO v_count FROM WC_WOUND_PHOTO_TB
    WHERE WC_PHOTO_REG_DIAGNOSIS_CD = v_wound1 AND WC_PHOTO_HOS_NO = 1 AND WC_PHOTO_STAGE = 'INITIAL'
      AND WC_PHOTO_NURSE_CD = 1002 AND WC_PHOTO_PATH = 'wounds/smoke-initial.jpg' AND WC_PHOTO_CAPTION = 'صورة أولى'
      AND WC_PHOTO_TAKEN_AT = TO_DATE('2026-11-01 09:30', 'YYYY-MM-DD HH24:MI') AND WC_PHOTO_REVIEW_DETAIL_CD IS NULL;
    assert_true('accept: photo with HOS_NO 1', v_count = 1);

    SELECT COUNT(*) INTO v_count FROM WC_REGISTRATION_PMH_TB WHERE WC_RPMH_REGISTRATION_CD = v_reg AND WC_RPMH_PMH_CD = v_pmh;
    assert_true('accept: PMH row', v_count = 1);

    SELECT COUNT(*) INTO v_count FROM WC_SCHEDULED_REVIEW_TB
    WHERE WC_SREV_REGISTRATION_CD = v_reg AND WC_SREV_STATUS = 'PENDING' AND WC_SREV_VISIT_SERIAL = 1
      AND WC_SREV_SCHEDULED_DATE = DATE '2026-11-08';
    assert_true('accept: first scheduled review on 2026-11-08', v_count = 1);

    -- Errors. A procedure called from PL/SQL does not undo its own writes when the caller handles the error
    -- (only a top-level call, as PHP makes, is rolled back as a whole), hence the savepoints.
    SAVEPOINT sp_decided;
    BEGIN
        SP_WC_ACCEPT_REGISTRATION(c_req1, accept_payload('1002'), v_result);
        assert_true('accept the same request twice -> REQUEST_ALREADY_DECIDED (no error raised)', FALSE);
    EXCEPTION
        WHEN OTHERS THEN
            expect_error('accept the same request twice', 'REQUEST_ALREADY_DECIDED', SQLCODE, SQLERRM);
    END;
    ROLLBACK TO sp_decided;

    SAVEPOINT sp_other_hospital;
    BEGIN
        SP_WC_ACCEPT_REGISTRATION(c_req2, accept_payload('2001'), v_result);
        assert_true('nurse 2001 exists only in hospital 2 -> INVALID_EMPLOYEE (no error raised)', FALSE);
    EXCEPTION
        WHEN OTHERS THEN
            expect_error('nurse 2001 exists only in hospital 2', 'INVALID_EMPLOYEE', SQLCODE, SQLERRM);
    END;
    ROLLBACK TO sp_other_hospital;

    SAVEPOINT sp_wct_other_hospital;
    BEGIN
        SP_WC_ACCEPT_REGISTRATION(c_req2, accept_payload('1002', p_wct => '2001'), v_result);
        assert_true('wound care employee 2001 only in hospital 2 -> INVALID_EMPLOYEE (no error raised)', FALSE);
    EXCEPTION
        WHEN OTHERS THEN
            expect_error('wound care employee 2001 only in hospital 2', 'INVALID_EMPLOYEE', SQLCODE, SQLERRM);
    END;
    ROLLBACK TO sp_wct_other_hospital;

    SAVEPOINT sp_not_a_number;
    BEGIN
        SP_WC_ACCEPT_REGISTRATION(c_req2, accept_payload('abc'), v_result);
        assert_true('non-numeric nurse code -> INVALID_EMPLOYEE (no error raised)', FALSE);
    EXCEPTION
        WHEN OTHERS THEN
            expect_error('non-numeric nurse code', 'INVALID_EMPLOYEE', SQLCODE, SQLERRM);
    END;
    ROLLBACK TO sp_not_a_number;

    SAVEPOINT sp_not_found;
    BEGIN
        SP_WC_ACCEPT_REGISTRATION('no-such-request', accept_payload('1002'), v_result);
        assert_true('unknown request -> REQUEST_NOT_FOUND (no error raised)', FALSE);
    EXCEPTION
        WHEN OTHERS THEN
            expect_error('unknown request', 'REQUEST_NOT_FOUND', SQLCODE, SQLERRM);
    END;
    ROLLBACK TO sp_not_found;

    SELECT COUNT(*) INTO v_count FROM WC_REQUEST_REG_TB WHERE WC_REQ_CODE = c_req2 AND WC_REQ_STATUS = 'PENDING';
    assert_true('second request still PENDING after the rejected calls', v_count = 1);

    -- ------------------------------------------------------------------
    -- 4. SP_WC_ADD_WOUND (wound data inside <assessment>; employee 1001 exists in both hospitals)
    -- ------------------------------------------------------------------
    BEGIN
        SP_WC_ADD_WOUND(
            v_reg,
            '<payload>' || x('clinic_nurse_code', '1001') || x('entry_user_code', c_user)
                || x('assessed_at', '2026-11-02 08:00') || x('assessment', initial_wound) || '</payload>',
            v_wound2
        );
        assert_true('add wound: returns a wound code', v_wound2 IS NOT NULL);
    EXCEPTION
        WHEN OTHERS THEN
            assert_true('add wound: unexpected error ' || SUBSTR(SQLERRM, 1, 300), FALSE);
    END;

    SELECT COUNT(*) INTO v_count FROM WC_REG_DIAGNOSIS_TB
    WHERE WC_RDIAG_CODE = v_wound2 AND WC_RDIAG_REGISTRATION_CD = v_reg AND WC_RDIAG_HOS_NO = 1
      AND WC_RDIAG_CLINICAL_EMP_CD = 1001 AND WC_RDIAG_DESCRIPTION = 'كعب القدم الأيسر';
    assert_true('add wound: wound row with HOS_NO 1', v_count = 1);

    SELECT COUNT(*) INTO v_count FROM WC_WOUND_ASSESSMENT_TB
    WHERE WC_WA_REG_DIAGNOSIS_CD = v_wound2 AND WC_WA_HOS_NO = 1 AND WC_WA_TYPE = 'INITIAL' AND WC_WA_CLINICAL_EMP_CD = 1001;
    assert_true('add wound: initial assessment with HOS_NO 1', v_count = 1);

    SELECT COUNT(*) INTO v_count FROM WC_WOUND_PHOTO_TB WHERE WC_PHOTO_REG_DIAGNOSIS_CD = v_wound2 AND WC_PHOTO_HOS_NO = 1;
    assert_true('add wound: photo with HOS_NO 1', v_count = 1);

    -- ------------------------------------------------------------------
    -- 5. SP_WC_COMPLETE_REVIEW
    -- ------------------------------------------------------------------
    SAVEPOINT sp_coverage;
    BEGIN
        SP_WC_COMPLETE_REVIEW(v_reg, NULL, review_payload('smoke-key-0', 'IMPROVING', follow_up_item(v_wound1, '1')), v_result);
        assert_true('review of 1 of 2 wounds -> EXACT_DIAGNOSIS_COVERAGE_REQUIRED (no error raised)', FALSE);
    EXCEPTION
        WHEN OTHERS THEN
            expect_error('review of 1 of 2 wounds', 'EXACT_DIAGNOSIS_COVERAGE_REQUIRED', SQLCODE, SQLERRM);
    END;
    ROLLBACK TO sp_coverage;

    BEGIN
        SP_WC_COMPLETE_REVIEW(
            v_reg, NULL,
            review_payload('smoke-key-1', 'IMPROVING', follow_up_item(v_wound1, '1') || follow_up_item(v_wound2, '0')),
            v_review
        );
        assert_true('review: returns a review code', v_review IS NOT NULL);
    EXCEPTION
        WHEN OTHERS THEN
            assert_true('review: unexpected error ' || SUBSTR(SQLERRM, 1, 300), FALSE);
    END;

    SELECT COUNT(*) INTO v_count FROM WC_DAILY_REVIEW_TB
    WHERE WC_DREV_CODE = v_review AND WC_DREV_HOS_NO = 1 AND WC_DREV_NURSE_CD = 1002
      AND WC_DREV_REGISTRATION_CD = v_reg AND WC_DREV_SUBMISSION_KEY = 'smoke-key-1' AND WC_DREV_SCHEDULED_REVIEW_CD IS NULL;
    assert_true('review: visit row with HOS_NO 1', v_count = 1);

    SELECT COUNT(*), SUM(WC_DREVD_FOLLOW_UP_REQUIRED) INTO v_count, v_number FROM WC_REVIEW_DETAIL_TB
    WHERE WC_REVD_REVIEW_CD = v_review AND WC_REVD_REVIEW_TYPE = 'DAILY' AND WC_DREVD_STATUS = 'IMPROVING';
    assert_true('review: 2 details, one without follow-up', v_count = 2 AND v_number = 1);

    SELECT COUNT(*) INTO v_count FROM WC_WOUND_ASSESSMENT_TB a
    JOIN WC_REVIEW_DETAIL_TB d ON d.WC_REVD_CODE = a.WC_WA_REVIEW_DETAIL_CD
    WHERE d.WC_REVD_REVIEW_CD = v_review AND a.WC_WA_TYPE = 'FOLLOW_UP' AND a.WC_WA_HOS_NO = 1
      AND a.WC_WA_CLINICAL_EMP_CD = 1002 AND a.WC_WA_EXUDATE_TYPE_CD IS NULL;
    assert_true('review: 2 follow-up assessments with HOS_NO 1 (dry wound, no exudate type)', v_count = 2);

    SELECT COUNT(*), MAX(WC_SREV_VISIT_SERIAL) INTO v_count, v_number FROM WC_SCHEDULED_REVIEW_TB WHERE WC_SREV_REGISTRATION_CD = v_reg;
    SELECT MAX(WC_REG_NEXT_VISIT_DATE) INTO v_date FROM WC_REGISTRATION_TB WHERE WC_REG_CODE = v_reg;
    assert_true('review: next appointment is serial 2 on 2026-11-15', v_count = 2 AND v_number = 2 AND v_date = DATE '2026-11-15');

    -- Idempotent: the same submission_key returns the same visit and writes nothing
    BEGIN
        SP_WC_COMPLETE_REVIEW(
            v_reg, NULL,
            review_payload('smoke-key-1', 'IMPROVING', follow_up_item(v_wound1, '1') || follow_up_item(v_wound2, '0')),
            v_again
        );
        assert_true('review again with the same submission_key returns the same visit', v_again = v_review);
    EXCEPTION
        WHEN OTHERS THEN
            assert_true('review again: unexpected error ' || SUBSTR(SQLERRM, 1, 300), FALSE);
    END;
    SELECT COUNT(*) INTO v_count FROM WC_DAILY_REVIEW_TB WHERE WC_DREV_SUBMISSION_KEY = 'smoke-key-1';
    SELECT COUNT(*) INTO v_number FROM WC_WOUND_ASSESSMENT_TB WHERE WC_WA_TYPE = 'FOLLOW_UP' AND WC_WA_REG_DIAGNOSIS_CD IN (v_wound1, v_wound2);
    assert_true('review again: still one visit and 2 follow-up assessments', v_count = 1 AND v_number = 2);

    -- Discharge, then the registration accepts no new wound
    BEGIN
        SP_WC_COMPLETE_REVIEW(
            v_reg, NULL,
            review_payload('smoke-key-2', 'IMPROVED_DISCHARGE', follow_up_item(v_wound1, '0') || follow_up_item(v_wound2, '0')),
            v_result
        );
    EXCEPTION
        WHEN OTHERS THEN
            assert_true('discharge review: unexpected error ' || SUBSTR(SQLERRM, 1, 300), FALSE);
    END;
    SELECT COUNT(*) INTO v_count FROM WC_REGISTRATION_TB
    WHERE WC_REG_CODE = v_reg AND WC_REG_STATUS = 'DISCHARGED' AND WC_REG_DISCHARGED_BY_EMP_CD = 1002;
    assert_true('discharge review: registration DISCHARGED by 1002', v_count = 1);

    SAVEPOINT sp_not_active;
    BEGIN
        SP_WC_ADD_WOUND(
            v_reg,
            '<payload>' || x('clinic_nurse_code', '1002') || x('entry_user_code', c_user) || initial_wound || '</payload>',
            v_result
        );
        assert_true('add wound to a discharged registration -> REGISTRATION_NOT_ACTIVE (no error raised)', FALSE);
    EXCEPTION
        WHEN OTHERS THEN
            expect_error('add wound to a discharged registration', 'REGISTRATION_NOT_ACTIVE', SQLCODE, SQLERRM);
    END;
    ROLLBACK TO sp_not_active;

    -- ------------------------------------------------------------------
    -- 6. UX_WC_USERS_EMP: one account per (employee, hospital); unlinked accounts unlimited
    -- ------------------------------------------------------------------
    SAVEPOINT sp_users;
    BEGIN
        INSERT INTO WC_USERS_TB (PER_USER_CODE, PER_USER_USERNAME, PER_USER_EMP_CD, PER_USER_HOS_NO)
        VALUES ('aaaaaaaa-0000-4000-8000-000000000002', 'smoke.duplicate', 1001, 1);
        assert_true('UX_WC_USERS_EMP rejects a second account for employee 1001 of hospital 1', FALSE);
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            assert_true('UX_WC_USERS_EMP rejects a second account for employee 1001 of hospital 1', TRUE);
    END;
    BEGIN
        INSERT INTO WC_USERS_TB (PER_USER_CODE, PER_USER_USERNAME, PER_USER_EMP_CD, PER_USER_HOS_NO)
        VALUES ('aaaaaaaa-0000-4000-8000-000000000003', 'smoke.hospital2', 1001, 2);
        INSERT INTO WC_USERS_TB (PER_USER_CODE, PER_USER_USERNAME, PER_USER_EMP_CD, PER_USER_HOS_NO)
        VALUES ('aaaaaaaa-0000-4000-8000-000000000004', 'smoke.unlinked1', NULL, 1);
        INSERT INTO WC_USERS_TB (PER_USER_CODE, PER_USER_USERNAME, PER_USER_EMP_CD, PER_USER_HOS_NO)
        VALUES ('aaaaaaaa-0000-4000-8000-000000000005', 'smoke.unlinked2', NULL, 1);
        assert_true('UX_WC_USERS_EMP allows employee 1001 of hospital 2 and several unlinked accounts', TRUE);
    EXCEPTION
        WHEN OTHERS THEN
            assert_true('UX_WC_USERS_EMP allows ... (' || SUBSTR(SQLERRM, 1, 200) || ')', FALSE);
    END;
    ROLLBACK TO sp_users;

    DBMS_OUTPUT.PUT_LINE('----------------------------------------');
    DBMS_OUTPUT.PUT_LINE('RESULT: ' || v_pass || ' passed, ' || v_fail || ' failed');
    DBMS_OUTPUT.PUT_LINE(CASE WHEN v_fail = 0 THEN 'SMOKE TEST: PASS' ELSE 'SMOKE TEST: FAIL' END);
    :failures := v_fail;
END;
/

ROLLBACK;
EXIT :failures
