-- ============================================================================
-- 05_fk_check.sql: optional step, only after build/04_enable_fk.sql (section 9 of wccms_oracle_install.sql).
-- Run as WCCMS. Checks that the composite foreign keys to HOS accept a code of the row's own hospital and
-- reject a code that exists only in another hospital. Exit code = number of failed checks. Rolled back at the end.
-- ============================================================================

WHENEVER SQLERROR EXIT FAILURE ROLLBACK
SET DEFINE OFF
SET SERVEROUTPUT ON SIZE 1000000
SET FEEDBACK OFF

VARIABLE failures NUMBER

DECLARE
    v_pass  NUMBER := 0;
    v_fail  NUMBER := 0;
    v_count NUMBER;

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

    /** Runs p_sql and expects it to succeed (p_expected = 0) or to fail with ORA-02291 (p_expected = -2291). */
    PROCEDURE try_sql(p_name IN VARCHAR2, p_sql IN VARCHAR2, p_expected IN NUMBER)
    IS
    BEGIN
        EXECUTE IMMEDIATE p_sql;
        assert_true(p_name, p_expected = 0);
    EXCEPTION
        WHEN OTHERS THEN
            assert_true(p_name || CASE WHEN SQLCODE <> p_expected THEN ' (got: ' || SUBSTR(SQLERRM, 1, 150) || ')' END,
                SQLCODE = p_expected);
    END try_sql;
BEGIN
    SELECT COUNT(*) INTO v_count FROM user_constraints WHERE constraint_type = 'R' AND r_owner = 'HOS';
    assert_true('14 composite foreign keys to HOS (found ' || v_count || ')', v_count = 14);

    INSERT INTO WC_USERS_TB (PER_USER_CODE, PER_USER_USERNAME, PER_USER_EMP_CD, PER_USER_HOS_NO)
    VALUES ('cccccccc-0000-4000-8000-000000000001', 'fk.nurse', 1001, 1);

    try_sql('request of hospital 1 for patient 5002 (hospital 2 only) -> ORA-02291',
        'INSERT INTO WC_REQUEST_REG_TB (WC_REQ_CODE, WC_REQ_HOS_NO, WC_REQ_PATIENT_CD, WC_REQ_PAT_NAME_SNAPSHOT, WC_REQ_PAT_ID_SNAPSHOT, '
        || 'WC_REQ_NURSE_CD, WC_REQ_ENTRY_USER_CD, WC_REQ_STATUS, WC_REQ_CREATED_AT) VALUES '
        || '(''dddddddd-0000-4000-8000-000000000001'', 1, 5002, ''x'', ''1'', 1001, ''cccccccc-0000-4000-8000-000000000001'', ''PENDING'', SYSDATE)',
        -2291);
    try_sql('request of hospital 2 for patient 5002 and nurse 2001 -> accepted',
        'INSERT INTO WC_REQUEST_REG_TB (WC_REQ_CODE, WC_REQ_HOS_NO, WC_REQ_PATIENT_CD, WC_REQ_PAT_NAME_SNAPSHOT, WC_REQ_PAT_ID_SNAPSHOT, '
        || 'WC_REQ_NURSE_CD, WC_REQ_ENTRY_USER_CD, WC_REQ_STATUS, WC_REQ_CREATED_AT) VALUES '
        || '(''dddddddd-0000-4000-8000-000000000002'', 2, 5002, ''x'', ''1'', 2001, ''cccccccc-0000-4000-8000-000000000001'', ''PENDING'', SYSDATE)',
        0);
    try_sql('request of hospital 1 with nurse 2001 (hospital 2 only) -> ORA-02291',
        'INSERT INTO WC_REQUEST_REG_TB (WC_REQ_CODE, WC_REQ_HOS_NO, WC_REQ_PATIENT_CD, WC_REQ_PAT_NAME_SNAPSHOT, WC_REQ_PAT_ID_SNAPSHOT, '
        || 'WC_REQ_NURSE_CD, WC_REQ_ENTRY_USER_CD, WC_REQ_STATUS, WC_REQ_CREATED_AT) VALUES '
        || '(''dddddddd-0000-4000-8000-000000000003'', 1, 5001, ''x'', ''1'', 2001, ''cccccccc-0000-4000-8000-000000000001'', ''PENDING'', SYSDATE)',
        -2291);
    try_sql('account linked to employee 2001 of hospital 1 -> ORA-02291',
        'INSERT INTO WC_USERS_TB (PER_USER_CODE, PER_USER_USERNAME, PER_USER_EMP_CD, PER_USER_HOS_NO) '
        || 'VALUES (''cccccccc-0000-4000-8000-000000000002'', ''fk.wrong'', 2001, 1)',
        -2291);
    try_sql('unlinked account (employee NULL) -> accepted',
        'INSERT INTO WC_USERS_TB (PER_USER_CODE, PER_USER_USERNAME, PER_USER_EMP_CD, PER_USER_HOS_NO) '
        || 'VALUES (''cccccccc-0000-4000-8000-000000000003'', ''fk.unlinked'', NULL, 1)',
        0);

    DBMS_OUTPUT.PUT_LINE('----------------------------------------');
    DBMS_OUTPUT.PUT_LINE('RESULT: ' || v_pass || ' passed, ' || v_fail || ' failed');
    DBMS_OUTPUT.PUT_LINE(CASE WHEN v_fail = 0 THEN 'FK CHECK: PASS' ELSE 'FK CHECK: FAIL' END);
    :failures := v_fail;
END;
/

ROLLBACK;
EXIT :failures
