SET DEFINE OFF
-- ============================================================================
-- WCCMS - uninstall: removes what wccms_oracle_install.sql created, from the account you are connected with.
-- ============================================================================
--
-- متى يُستعمل: لإعادة تثبيت WCCMS من البداية، مثلاً بعد أن توقف wccms_oracle_install.sql بخطأ في منتصفه.
-- نفّذه بالحساب نفسه الذي نُفّذ به ملف التثبيت (SQL Developer: الصق الملف كاملاً ثم Run Script F5).
--
-- يحذف كائنات WCCMS بأسمائها فقط (20 جدولاً بكل بياناتها، النوع، الدوال، والـ procedures الأربعة)، ويتجاهل أي
-- اسم غير موجود. لا يلمس جداول HOS ولا أي كائن آخر في الحساب.
-- ⚠️ يحذف كل بيانات العيادة (الطلبات والتسجيلات والمراجعات). لا تشغّله على نظام يعمل فعلياً.
-- ============================================================================

WHENEVER SQLERROR EXIT SQL.SQLCODE ROLLBACK
SET SERVEROUTPUT ON

DECLARE
    -- الترتيب: الـ procedures والدوال، ثم النوع، ثم الجداول (CASCADE CONSTRAINTS يحذف المفاتيح بينها).
    v_names SYS.ODCIVARCHAR2LIST := SYS.ODCIVARCHAR2LIST(
                'SP_WC_ADD_WOUND', 'SP_WC_COMPLETE_REVIEW', 'SP_WC_ACCEPT_REGISTRATION', 'SP_WC_INSERT_ASSESSMENT',
                'FN_WC_EMPLOYEE_EXISTS', 'FN_WC_XML_CODES', 'FN_WC_XML_ITEM', 'FN_WC_XML_ITEM_COUNT',
                'FN_WC_XML_NUMBER', 'FN_WC_XML_FLAG', 'FN_WC_XML_TEXT', 'FN_WC_TO_CODE', 'FN_WC_TO_DATE',
                'FN_WC_NOW', 'FN_WC_UUID', 'WC_CODE_LIST',
                'WC_REPORT_EXPORT_TB', 'WC_WOUND_PHOTO_TB', 'WC_ASSESSMENT_FINDING_TB', 'WC_WOUND_ASSESSMENT_TB',
                'WC_REGISTRATION_PMH_TB', 'WC_ASSESSMENT_CHOICE_TB', 'WC_REVIEW_DETAIL_TB', 'WC_DAILY_REVIEW_TB',
                'WC_SCHEDULED_REVIEW_TB', 'WC_REG_DIAGNOSIS_TB', 'WC_REGISTRATION_TB', 'WC_REQUEST_REG_TB',
                'WC_PROCEDURE_TB', 'WC_MEDICATION_TB', 'WC_DRESSING_TB', 'WC_WOUND_TYPE_TB', 'WC_DIAGNOSIS_TB',
                'WC_PMH_TB', 'WC_DEPARTMENT_TB', 'WC_USERS_TB');
    v_type    VARCHAR2(30);
    v_dropped NUMBER := 0;
BEGIN
    IF USER = 'SYS' THEN
        RAISE_APPLICATION_ERROR(-20000, 'WCCMS uninstall: do not run this file as SYS.');
    END IF;

    FOR i IN 1 .. v_names.COUNT LOOP
        BEGIN
            SELECT object_type INTO v_type FROM user_objects
             WHERE object_name = v_names(i) AND object_type IN ('TABLE', 'TYPE', 'FUNCTION', 'PROCEDURE');
        EXCEPTION
            WHEN NO_DATA_FOUND THEN
                v_type := NULL;
        END;

        IF v_type = 'TABLE' THEN
            EXECUTE IMMEDIATE 'DROP TABLE "' || v_names(i) || '" CASCADE CONSTRAINTS PURGE';
        ELSIF v_type = 'TYPE' THEN
            EXECUTE IMMEDIATE 'DROP TYPE "' || v_names(i) || '" FORCE';
        ELSIF v_type IS NOT NULL THEN
            EXECUTE IMMEDIATE 'DROP ' || v_type || ' "' || v_names(i) || '"';
        END IF;

        IF v_type IS NOT NULL THEN
            v_dropped := v_dropped + 1;
            DBMS_OUTPUT.PUT_LINE('Dropped ' || v_type || ' ' || v_names(i));
        END IF;
    END LOOP;

    DBMS_OUTPUT.PUT_LINE('WCCMS uninstall: ' || v_dropped || ' object(s) dropped from ' || USER || '.');
END;
/
