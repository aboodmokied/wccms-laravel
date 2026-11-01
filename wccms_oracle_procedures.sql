-- ============================================================================
-- WCCMS - Oracle PL/SQL: the type, the helper functions and the 4 clinical procedures
-- Target: Oracle 11g (11.2) or later. No JSON types or functions (HOS already owns a type named JSON_OBJECT_T,
-- and 11g has no JSON support): the application sends the form data as XML and the procedures read it with XMLTYPE.
-- Run after wccms_oracle_schema.sql. Safe to run again: everything is CREATE OR REPLACE (the type is created once).
-- ============================================================================
--
-- ما يحتويه الملف
--   WC_CODE_LIST               نوع: قائمة رموز، لعدّ الاختيارات المكررة داخل SQL.
--   FN_WC_UUID                 رمز فريد بشكل UUID (8-4-4-4-12).
--   FN_WC_NOW                  الوقت الحالي بتوقيت UTC (النظام يحفظ كل الأوقات UTC).
--   FN_WC_TO_DATE              تحويل نص تاريخ من البيانات المرسلة إلى DATE.
--   FN_WC_TO_CODE              تحويل نص رمز مريض أو موظف إلى NUMBER(9)، أو NULL إذا لم يكن رقماً صحيحاً.
--   FN_WC_XML_TEXT             نص عنصر ابن من XML (بعد فك &amp; وأمثالها)، أو NULL.
--   FN_WC_XML_FLAG             قيمة منطقية 1/0 من عنصر ابن، أو NULL.
--   FN_WC_XML_NUMBER           رقم من عنصر ابن بفاصلة '.' أياً كانت إعدادات الجلسة.
--   FN_WC_XML_ITEM_COUNT       عدد عناصر <item> داخل عنصر ابن (قائمة).
--   FN_WC_XML_ITEM             عنصر <item> رقم n من قائمة، كـ XMLTYPE.
--   FN_WC_XML_CODES            قائمة نصوص <item> داخل عنصر ابن، كـ WC_CODE_LIST.
--   FN_WC_EMPLOYEE_EXISTS      هل الموظف موجود في HOS.EMPLOYEE_TB في المستشفى المحدد.
--   SP_WC_INSERT_ASSESSMENT    يتحقق من تقييم جرح واحد ويحفظه مع اختياراته وصوره.
--   SP_WC_ACCEPT_REGISTRATION  قبول طلب التحويل: التسجيل والجروح وتقييماتها والموعد.
--   SP_WC_COMPLETE_REVIEW      حفظ زيارة مراجعة (يومية أو مجدولة) لكل جروح التسجيل.
--   SP_WC_ADD_WOUND            إضافة جرح جديد مع تقييمه الأولي لتسجيل نشط.
--
-- العقد مع التطبيق (app/Services/ClinicalWorkflows/OracleClinicalWorkflowGateway.php)
--   - الإجراءات الثلاثة الخارجية تأخذ البيانات XML في p_payload (CLOB) وتُرجع رمز الصف الذي أنشأته في
--     p_result (OUT).
--   - شكل الـ XML: الجذر <payload>؛ كل مفتاح عنصر ابن بالاسم نفسه (مثل <clinic_nurse_code>)؛ القائمة عناصر
--     <item> متكررة؛ القيمة NULL لا تُرسل؛ القيم المنطقية 1 أو 0؛ الأرقام بفاصلة '.'. مثال:
--       <payload><clinic_nurse_code>1001</clinic_nurse_code><pmh_codes><item>...</item></pmh_codes>
--         <diagnoses><item><diagnosis_code>...</diagnosis_code>...</item></diagnoses></payload>
--   - رقم المستشفى (HOS_NO) لا يُقرأ من الـ XML أبداً: يُؤخذ من صف الطلب أو التسجيل، ويُكتب في كل صف له عمود HOS_NO.
--   - لا يوجد COMMIT ولا ROLLBACK داخل الإجراءات: التطبيق يستدعيها داخل DB::transaction. أي خطأ يرفعه
--     RAISE_APPLICATION_ERROR(-20001, '<رمز الخطأ>') فيُلغى كل ما كتبه الاستدعاء.
--   - رموز الأخطاء هي نفسها في نسخة MySQL (مثل REQUEST_ALREADY_DECIDED)، وتظهر في سجل Laravel.
--
-- ملاحظات لمن يعدّل الكود
--   - الدوال المحلية داخل الإجراءات (is_choice وغيرها) لا تُستدعى داخل جمل SQL (PLS-00231)، لذلك تُحسب القيم في
--     متغيرات أولاً ثم تُستخدم في INSERT/UPDATE/SELECT. الدوال على مستوى المخطط (FN_WC_*) مسموحة في SQL.
--   - مسارات XPath تُبنى نصياً ('/*/' || المفتاح) وتُمرَّر إلى extract و existsNode. لا تُمرَّر إلى XMLTABLE:
--     في 11g يجب أن يكون مسار XMLTABLE نصاً ثابتاً.
--   - '/*/المفتاح' يعمل على الجذر <payload> وعلى أي <item> أو <assessment> مستخرج منه.
--
-- التشغيل: بـ SQL*Plus أو SQLcl:  @wccms_oracle_procedures.sql  (أو Run Script في SQL Developer).
-- بعد التشغيل يجب ألا تُرجع هذه الجملة أي صف:
--   SELECT object_name, object_type FROM all_objects WHERE owner = 'WCCMS' AND status = 'INVALID';
-- ============================================================================

WHENEVER SQLERROR EXIT SQL.SQLCODE ROLLBACK
SET DEFINE OFF
SET SQLBLANKLINES ON

ALTER SESSION SET CURRENT_SCHEMA = WCCMS;

-- ----------------------------------------------------------------------------
-- WC_CODE_LIST: يُنشأ مرة واحدة فقط. CREATE OR REPLACE TYPE يفشل (ORA-02303) إذا كانت دوال تعتمد عليه،
-- لذلك لا يُعاد إنشاؤه عند إعادة تشغيل الملف.
-- ----------------------------------------------------------------------------
DECLARE
    v_count NUMBER;
BEGIN
    SELECT COUNT(*) INTO v_count FROM all_types WHERE owner = 'WCCMS' AND type_name = 'WC_CODE_LIST';
    IF v_count = 0 THEN
        EXECUTE IMMEDIATE 'CREATE TYPE WCCMS.WC_CODE_LIST AS TABLE OF VARCHAR2(100 CHAR)';
    END IF;
END;
/

-- ----------------------------------------------------------------------------
-- FN_WC_UUID: 32 حرفاً سداسياً من SYS_GUID بشكل UUID بحروف صغيرة، مثل 3f2a9c1e-....
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION FN_WC_UUID RETURN VARCHAR2
IS
BEGIN
    RETURN LOWER(REGEXP_REPLACE(RAWTOHEX(SYS_GUID()), '^(.{8})(.{4})(.{4})(.{4})(.{12})$', '\1-\2-\3-\4-\5'));
END FN_WC_UUID;
/
SHOW ERRORS

-- ----------------------------------------------------------------------------
-- FN_WC_NOW: الوقت الحالي بتوقيت UTC بغض النظر عن توقيت الخادم أو الجلسة (بدل SYSDATE).
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION FN_WC_NOW RETURN DATE
IS
BEGIN
    RETURN CAST(SYS_EXTRACT_UTC(SYSTIMESTAMP) AS DATE);
END FN_WC_NOW;
/
SHOW ERRORS

-- ----------------------------------------------------------------------------
-- FN_WC_TO_DATE: يقبل 'YYYY-MM-DD' و 'YYYY-MM-DD HH24:MI' و 'YYYY-MM-DD HH24:MI:SS' وصيغة ISO بحرف T
-- (مثل حقل datetime-local)، ويتجاهل أجزاء الثانية والمنطقة الزمنية. نص فارغ => NULL.
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION FN_WC_TO_DATE(p_value IN VARCHAR2) RETURN DATE
IS
    v_value VARCHAR2(19 CHAR) := TRIM(REPLACE(SUBSTR(TRIM(p_value), 1, 19), 'T', ' '));
BEGIN
    IF v_value IS NULL OR v_value = 'null' THEN
        RETURN NULL;
    ELSIF LENGTH(v_value) = 10 THEN
        RETURN TO_DATE(v_value, 'YYYY-MM-DD');
    ELSIF LENGTH(v_value) = 16 THEN
        RETURN TO_DATE(v_value, 'YYYY-MM-DD HH24:MI');
    END IF;

    RETURN TO_DATE(v_value, 'YYYY-MM-DD HH24:MI:SS');
END FN_WC_TO_DATE;
/
SHOW ERRORS

-- ----------------------------------------------------------------------------
-- FN_WC_TO_CODE: رمز مريض أو موظف (NUMBER(9) في المستشفى). يقبل أرقاماً فقط من 1 إلى 9 خانات؛ غير ذلك => NULL.
-- لا يعتمد على NLS_NUMERIC_CHARACTERS.
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION FN_WC_TO_CODE(p_value IN VARCHAR2) RETURN NUMBER
IS
    v_value VARCHAR2(4000) := TRIM(p_value);
BEGIN
    IF v_value IS NULL OR LENGTH(v_value) > 9 OR NOT REGEXP_LIKE(v_value, '^[0-9]+$') THEN
        RETURN NULL;
    END IF;

    RETURN TO_NUMBER(v_value);
END FN_WC_TO_CODE;
/
SHOW ERRORS

-- ----------------------------------------------------------------------------
-- FN_WC_XML_TEXT: نص العنصر الابن p_key للجذر (المسار '/*/' || p_key)، بعد فك ترميز &amp; &lt; وأمثالها.
-- NULL إذا لم يوجد العنصر أو كان فارغاً. p_key قد يحتوي مساراً، مثل 'pmh_codes/item[2]'.
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION FN_WC_XML_TEXT(p_xml IN XMLTYPE, p_key IN VARCHAR2) RETURN VARCHAR2
IS
    v_node    XMLTYPE;
    v_raw     CLOB;
    v_decoded CLOB;
    v_result  VARCHAR2(32767);
BEGIN
    IF p_xml IS NULL THEN
        RETURN NULL;
    END IF;

    v_node := p_xml.extract('/*/' || p_key || '/text()');
    IF v_node IS NULL THEN
        RETURN NULL;
    END IF;

    v_raw := v_node.getClobVal();
    v_decoded := DBMS_XMLGEN.CONVERT(v_raw, DBMS_XMLGEN.ENTITY_DECODE);
    v_result := v_decoded;

    IF DBMS_LOB.ISTEMPORARY(v_raw) = 1 THEN
        DBMS_LOB.FREETEMPORARY(v_raw);
    END IF;
    IF DBMS_LOB.ISTEMPORARY(v_decoded) = 1 THEN
        DBMS_LOB.FREETEMPORARY(v_decoded);
    END IF;

    RETURN v_result;
END FN_WC_XML_TEXT;
/
SHOW ERRORS

-- ----------------------------------------------------------------------------
-- FN_WC_XML_FLAG: 1 أو true => 1، 0 أو false => 0، غير ذلك (أو غير موجود) => NULL. مثل نسخة MySQL.
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION FN_WC_XML_FLAG(p_xml IN XMLTYPE, p_key IN VARCHAR2) RETURN NUMBER
IS
    v_value VARCHAR2(400) := LOWER(TRIM(SUBSTR(FN_WC_XML_TEXT(p_xml, p_key), 1, 100)));
BEGIN
    RETURN CASE WHEN v_value IN ('true', '1') THEN 1 WHEN v_value IN ('false', '0') THEN 0 END;
END FN_WC_XML_FLAG;
/
SHOW ERRORS

-- ----------------------------------------------------------------------------
-- FN_WC_XML_NUMBER: رقم بفاصلة '.' أياً كانت NLS_NUMERIC_CHARACTERS للجلسة. NULL إذا لم يوجد.
-- نص ليس رقماً => ORA-01722 (كما في النسخة السابقة).
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION FN_WC_XML_NUMBER(p_xml IN XMLTYPE, p_key IN VARCHAR2) RETURN NUMBER
IS
    v_value VARCHAR2(400) := TRIM(SUBSTR(FN_WC_XML_TEXT(p_xml, p_key), 1, 100));
BEGIN
    IF v_value IS NULL THEN
        RETURN NULL;
    END IF;

    RETURN TO_NUMBER(v_value, '99999999999999999990D9999999999', 'NLS_NUMERIC_CHARACTERS=''.,''');
END FN_WC_XML_NUMBER;
/
SHOW ERRORS

-- ----------------------------------------------------------------------------
-- FN_WC_XML_ITEM_COUNT: عدد عناصر <item> داخل العنصر الابن p_key. 0 إذا لم يوجد.
-- (XPath count() لا يعمل مع extract، و XMLTABLE يحتاج مساراً ثابتاً، لذلك العدّ بـ existsNode.)
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION FN_WC_XML_ITEM_COUNT(p_xml IN XMLTYPE, p_key IN VARCHAR2) RETURN NUMBER
IS
    v_count NUMBER := 0;
BEGIN
    IF p_xml IS NULL THEN
        RETURN 0;
    END IF;

    WHILE p_xml.existsNode('/*/' || p_key || '/item[' || (v_count + 1) || ']') = 1 LOOP
        v_count := v_count + 1;
    END LOOP;

    RETURN v_count;
END FN_WC_XML_ITEM_COUNT;
/
SHOW ERRORS

-- ----------------------------------------------------------------------------
-- FN_WC_XML_ITEM: العنصر <item> رقم p_index (يبدأ من 1) داخل العنصر الابن p_key، أو NULL.
-- الناتج جذره <item>، فتعمل عليه FN_WC_XML_TEXT(v_item, 'diagnosis_code') وأمثالها.
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION FN_WC_XML_ITEM(p_xml IN XMLTYPE, p_key IN VARCHAR2, p_index IN PLS_INTEGER) RETURN XMLTYPE
IS
BEGIN
    IF p_xml IS NULL THEN
        RETURN NULL;
    END IF;

    RETURN p_xml.extract('/*/' || p_key || '/item[' || p_index || ']');
END FN_WC_XML_ITEM;
/
SHOW ERRORS

-- ----------------------------------------------------------------------------
-- FN_WC_XML_CODES: نصوص عناصر <item> داخل العنصر الابن p_key كقائمة. قائمة فارغة إذا لم يوجد.
-- عنصر <item> فارغ يُضاف كـ NULL (فيفشل التحقق من الاختيارات كما يجب).
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION FN_WC_XML_CODES(p_xml IN XMLTYPE, p_key IN VARCHAR2) RETURN WC_CODE_LIST
IS
    v_list  WC_CODE_LIST := WC_CODE_LIST();
    v_count NUMBER := FN_WC_XML_ITEM_COUNT(p_xml, p_key);
BEGIN
    FOR i IN 1 .. v_count LOOP
        v_list.EXTEND;
        v_list(v_list.LAST) := FN_WC_XML_TEXT(p_xml, p_key || '/item[' || i || ']');
    END LOOP;

    RETURN v_list;
END FN_WC_XML_CODES;
/
SHOW ERRORS

-- ----------------------------------------------------------------------------
-- FN_WC_EMPLOYEE_EXISTS: 1 إذا كان الموظف موجوداً في HOS.EMPLOYEE_TB في المستشفى p_hos_no، وإلا 0.
-- المفتاح في المستشفى مركّب (EMP_SERIAL, HOS_NO): الرقم نفسه قد يكون موظفاً آخر في مستشفى آخر.
-- رمز ليس رقماً صحيحاً => 0 بدل خطأ تحويل.
-- يحتاج GRANT SELECT مباشراً (لا عبر role) على HOS.EMPLOYEE_TB للمستخدم WCCMS، وهو في wccms_oracle_schema.sql.
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION FN_WC_EMPLOYEE_EXISTS(p_code IN VARCHAR2, p_hos_no IN NUMBER) RETURN NUMBER
IS
    v_code  NUMBER := FN_WC_TO_CODE(p_code);
    v_count NUMBER;
BEGIN
    IF v_code IS NULL OR p_hos_no IS NULL THEN
        RETURN 0;
    END IF;

    SELECT COUNT(*) INTO v_count
    FROM HOS.EMPLOYEE_TB
    WHERE EMP_SERIAL = v_code
      AND HOS_NO = p_hos_no
      AND ROWNUM = 1;

    RETURN v_count;
END FN_WC_EMPLOYEE_EXISTS;
/
SHOW ERRORS

-- ----------------------------------------------------------------------------
-- SP_WC_INSERT_ASSESSMENT
-- يُستدعى من الإجراءات الثلاثة الأخرى فقط (لا من PHP).
--   p_diagnosis   رمز الجرح (WC_REG_DIAGNOSIS_TB)
--   p_detail      رمز تفصيل المراجعة لتقييم المتابعة، NULL للتقييم الأولي
--   p_type        'INITIAL' أو 'FOLLOW_UP'
--   p_hos_no      رقم المستشفى (من صف الطلب أو التسجيل)، يُكتب في التقييم والصور ويُستخدم للتحقق من الموظفين
--   p_shared      الـ XML الكامل للطلب: الممرض ومُدخل البيانات ووقت التقييم
--   p_assessment  الـ XML الخاص بهذا الجرح: الاختيارات والقياسات والعلاج والصور
-- ----------------------------------------------------------------------------
CREATE OR REPLACE PROCEDURE SP_WC_INSERT_ASSESSMENT(
    p_diagnosis   IN VARCHAR2,
    p_detail      IN VARCHAR2,
    p_type        IN VARCHAR2,
    p_hos_no      IN NUMBER,
    p_shared      IN XMLTYPE,
    p_assessment  IN XMLTYPE
)
IS
    v_assessment    VARCHAR2(36 CHAR) := FN_WC_UUID();
    v_status        VARCHAR2(100 CHAR);
    v_amount        VARCHAR2(100 CHAR);
    v_exudate_type  VARCHAR2(100 CHAR);
    v_odor          VARCHAR2(100 CHAR);
    v_pain          VARCHAR2(100 CHAR);
    v_fever         VARCHAR2(100 CHAR);
    v_location      VARCHAR2(100 CHAR);
    v_laterality    VARCHAR2(100 CHAR);
    v_nutrition     VARCHAR2(100 CHAR);
    v_dressing      VARCHAR2(100 CHAR);
    v_medication    VARCHAR2(100 CHAR);
    v_procedure     VARCHAR2(100 CHAR);
    v_clinician     VARCHAR2(100 CHAR);
    v_wct           VARCHAR2(100 CHAR);
    v_doctor        VARCHAR2(100 CHAR);
    v_clinician_cd  NUMBER(9);
    v_wct_cd        NUMBER(9);
    v_doctor_cd     NUMBER(9);
    v_entry_user    VARCHAR2(100 CHAR);
    v_length        NUMBER;
    v_width         NUMBER;
    v_depth         NUMBER;
    v_assessed_at   DATE;
    v_pain_flag     NUMBER(1);
    v_swab_flag     NUMBER(1);
    v_follow_up     NUMBER(1);
    v_loc_notes     VARCHAR2(4000 CHAR);
    v_treatment     VARCHAR2(4000 CHAR);
    v_observation   VARCHAR2(32767);
    v_changes       VARCHAR2(32767);
    v_beds          WC_CODE_LIST;
    v_margins       WC_CODE_LIST;
    v_findings      WC_CODE_LIST;
    v_matched       NUMBER;
    v_distinct      NUMBER;
    v_none_margins  NUMBER;
    v_photo_count   NUMBER;
    v_photo         XMLTYPE;
    v_photo_code    VARCHAR2(36 CHAR);
    v_photo_path    VARCHAR2(255 CHAR);
    v_photo_caption VARCHAR2(1000 CHAR);
    v_photo_taken   DATE;
    v_photo_stage   VARCHAR2(20 CHAR);

    /** TRUE when the code is an active choice of that category. */
    FUNCTION is_choice(p_code IN VARCHAR2, p_category IN VARCHAR2) RETURN BOOLEAN
    IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM WC_ASSESSMENT_CHOICE_TB
        WHERE WC_ACH_CODE = p_code AND WC_ACH_CATEGORY = p_category AND WC_ACH_IS_ACTIVE = 1;
        RETURN v_count > 0;
    END is_choice;

    FUNCTION choice_key(p_code IN VARCHAR2) RETURN VARCHAR2
    IS
        v_key WC_ASSESSMENT_CHOICE_TB.WC_ACH_KEY%TYPE;
    BEGIN
        SELECT MAX(WC_ACH_KEY) INTO v_key FROM WC_ASSESSMENT_CHOICE_TB WHERE WC_ACH_CODE = p_code;
        RETURN v_key;
    END choice_key;

    FUNCTION is_active_row(p_table IN VARCHAR2, p_code IN VARCHAR2) RETURN BOOLEAN
    IS
        v_count NUMBER;
    BEGIN
        CASE p_table
            WHEN 'DRESSING' THEN
                SELECT COUNT(*) INTO v_count FROM WC_DRESSING_TB WHERE WC_DRESS_CODE = p_code AND WC_DRESS_IS_ACTIVE = 1;
            WHEN 'MEDICATION' THEN
                SELECT COUNT(*) INTO v_count FROM WC_MEDICATION_TB WHERE WC_MED_CODE = p_code AND WC_MED_IS_ACTIVE = 1;
            WHEN 'PROCEDURE' THEN
                SELECT COUNT(*) INTO v_count FROM WC_PROCEDURE_TB WHERE WC_PROC_CODE = p_code AND WC_PROC_IS_ACTIVE = 1;
        END CASE;
        RETURN v_count > 0;
    END is_active_row;

    PROCEDURE fail(p_code IN VARCHAR2)
    IS
    BEGIN
        RAISE_APPLICATION_ERROR(-20001, p_code);
    END fail;
BEGIN
    v_status       := FN_WC_XML_TEXT(p_assessment, 'wound_status_choice_code');
    v_amount       := FN_WC_XML_TEXT(p_assessment, 'exudate_amount_choice_code');
    v_exudate_type := FN_WC_XML_TEXT(p_assessment, 'exudate_type_choice_code');
    v_odor         := FN_WC_XML_TEXT(p_assessment, 'odor_choice_code');
    v_pain         := FN_WC_XML_TEXT(p_assessment, 'pain_severity_choice_code');
    v_fever        := FN_WC_XML_TEXT(p_assessment, 'fever_choice_code');
    v_location     := FN_WC_XML_TEXT(p_assessment, 'location_choice_code');
    v_laterality   := FN_WC_XML_TEXT(p_assessment, 'laterality_choice_code');
    v_nutrition    := FN_WC_XML_TEXT(p_assessment, 'nutrition_choice_code');
    v_dressing     := FN_WC_XML_TEXT(p_assessment, 'dressing_code');
    v_medication   := FN_WC_XML_TEXT(p_assessment, 'medication_code');
    v_procedure    := FN_WC_XML_TEXT(p_assessment, 'procedure_code');
    v_clinician    := NVL(FN_WC_XML_TEXT(p_shared, 'clinic_nurse_code'), FN_WC_XML_TEXT(p_shared, 'nurse_code'));
    v_wct          := FN_WC_XML_TEXT(p_shared, 'wct_employee_code');
    v_doctor       := FN_WC_XML_TEXT(p_shared, 'doctor_employee_code');
    v_entry_user   := FN_WC_XML_TEXT(p_shared, 'entry_user_code');
    v_length       := FN_WC_XML_NUMBER(p_assessment, 'length_mm');
    v_width        := FN_WC_XML_NUMBER(p_assessment, 'width_mm');
    v_depth        := FN_WC_XML_NUMBER(p_assessment, 'depth_mm');
    v_beds         := FN_WC_XML_CODES(p_assessment, 'wound_bed_choice_codes');
    v_margins      := FN_WC_XML_CODES(p_assessment, 'wound_margin_choice_codes');
    v_assessed_at  := NVL(FN_WC_TO_DATE(FN_WC_XML_TEXT(p_shared, 'assessed_at')), FN_WC_NOW());
    v_pain_flag    := FN_WC_XML_FLAG(p_assessment, 'pain_at_dressing');
    v_swab_flag    := FN_WC_XML_FLAG(p_assessment, 'swab_taken');
    v_follow_up    := NVL(FN_WC_XML_FLAG(p_assessment, 'follow_up_required'), 1);
    v_loc_notes    := FN_WC_XML_TEXT(p_assessment, 'location_notes');
    v_treatment    := FN_WC_XML_TEXT(p_assessment, 'treatment_notes');
    v_observation  := FN_WC_XML_TEXT(p_assessment, 'clinical_observation');
    v_changes      := FN_WC_XML_TEXT(p_assessment, 'wound_changes');

    -- Clinicians must exist in the hospital employees table, in this hospital
    IF FN_WC_EMPLOYEE_EXISTS(v_clinician, p_hos_no) = 0
       OR (v_wct IS NOT NULL AND FN_WC_EMPLOYEE_EXISTS(v_wct, p_hos_no) = 0)
       OR (v_doctor IS NOT NULL AND FN_WC_EMPLOYEE_EXISTS(v_doctor, p_hos_no) = 0) THEN
        fail('INVALID_EMPLOYEE');
    END IF;
    v_clinician_cd := FN_WC_TO_CODE(v_clinician);
    v_wct_cd       := FN_WC_TO_CODE(v_wct);
    v_doctor_cd    := FN_WC_TO_CODE(v_doctor);

    -- Required single choices
    IF NOT is_choice(v_status, 'WOUND_STATUS') OR NOT is_choice(v_amount, 'EXUDATE_AMOUNT')
       OR NOT is_choice(v_odor, 'ODOR') OR NOT is_choice(v_pain, 'PAIN_SEVERITY') THEN
        fail('INVALID_REQUIRED_ASSESSMENT_CHOICE');
    END IF;
    IF p_type = 'INITIAL' AND NVL(choice_key(v_status), '-') <> 'FIRST_VISIT' THEN
        fail('INITIAL_STATUS_MUST_BE_FIRST_VISIT');
    END IF;
    IF p_type = 'FOLLOW_UP' AND choice_key(v_status) = 'FIRST_VISIT' THEN
        fail('FOLLOW_UP_STATUS_CANNOT_BE_FIRST_VISIT');
    END IF;
    IF p_type = 'INITIAL' AND (
        NOT is_choice(v_fever, 'FEVER') OR NOT is_choice(v_location, 'LOCATION') OR NOT is_choice(v_nutrition, 'NUTRITION')
    ) THEN
        fail('INVALID_INITIAL_ASSESSMENT_CHOICE');
    END IF;
    IF v_laterality IS NOT NULL AND NOT is_choice(v_laterality, 'LATERALITY') THEN
        fail('INVALID_LATERALITY_CHOICE');
    END IF;

    -- Wound bed: at least one, all valid, no duplicates
    SELECT COUNT(*), COUNT(DISTINCT t.COLUMN_VALUE) INTO v_matched, v_distinct
    FROM TABLE(v_beds) t
    JOIN WC_ASSESSMENT_CHOICE_TB c
      ON c.WC_ACH_CODE = t.COLUMN_VALUE AND c.WC_ACH_CATEGORY = 'WOUND_BED' AND c.WC_ACH_IS_ACTIVE = 1;
    IF v_matched = 0 OR v_matched <> v_beds.COUNT OR v_matched <> v_distinct THEN
        fail('INVALID_WOUND_BED_CHOICES');
    END IF;

    -- Wound margin: required for the initial assessment, all valid, no duplicates, "none" is exclusive
    SELECT COUNT(*), COUNT(DISTINCT t.COLUMN_VALUE), COUNT(CASE WHEN c.WC_ACH_KEY = 'NONE_OBSERVED' THEN 1 END)
    INTO v_matched, v_distinct, v_none_margins
    FROM TABLE(v_margins) t
    JOIN WC_ASSESSMENT_CHOICE_TB c
      ON c.WC_ACH_CODE = t.COLUMN_VALUE AND c.WC_ACH_CATEGORY = 'WOUND_MARGIN' AND c.WC_ACH_IS_ACTIVE = 1;
    IF (p_type = 'INITIAL' AND v_matched = 0) OR v_matched <> v_margins.COUNT OR v_matched <> v_distinct THEN
        fail('INVALID_WOUND_MARGIN_CHOICES');
    END IF;
    IF v_matched > 1 AND v_none_margins > 0 THEN
        fail('NONE_MARGIN_IS_EXCLUSIVE');
    END IF;

    -- Exudate type: forbidden for a dry wound, required otherwise
    IF choice_key(v_amount) = 'DRY' AND v_exudate_type IS NOT NULL THEN
        fail('DRY_WOUND_FORBIDS_EXUDATE_TYPE');
    END IF;
    IF NVL(choice_key(v_amount), '-') <> 'DRY' AND (v_exudate_type IS NULL OR NOT is_choice(v_exudate_type, 'EXUDATE_TYPE')) THEN
        fail('EXUDATE_TYPE_REQUIRED');
    END IF;

    -- Treatment: at least one of dressing / medication / procedure / notes, each code active
    IF v_dressing IS NULL AND v_medication IS NULL AND v_procedure IS NULL
       AND TRIM(v_treatment) IS NULL THEN
        fail('TREATMENT_REQUIRED');
    END IF;
    IF v_dressing IS NOT NULL AND NOT is_active_row('DRESSING', v_dressing) THEN
        fail('INVALID_DRESSING');
    END IF;
    IF v_medication IS NOT NULL AND NOT is_active_row('MEDICATION', v_medication) THEN
        fail('INVALID_MEDICATION');
    END IF;
    IF v_procedure IS NOT NULL AND NOT is_active_row('PROCEDURE', v_procedure) THEN
        fail('INVALID_PROCEDURE');
    END IF;

    -- Measurements in millimetres
    IF (p_type = 'INITIAL' AND (v_length <= 0 OR v_width <= 0 OR v_depth < 0))
       OR NVL(v_length, 0) > 1000 OR NVL(v_width, 0) > 1000 OR NVL(v_depth, 0) > 1000 THEN
        fail('INVALID_MEASUREMENTS');
    END IF;

    INSERT INTO WC_WOUND_ASSESSMENT_TB (
        WC_WA_CODE, WC_WA_HOS_NO, WC_WA_REG_DIAGNOSIS_CD, WC_WA_REVIEW_DETAIL_CD, WC_WA_TYPE,
        WC_WA_CLINICAL_EMP_CD, WC_WA_WCT_EMP_CD, WC_WA_DOCTOR_EMP_CD, WC_WA_ENTRY_USER_CD, WC_WA_ASSESSED_AT,
        WC_WA_WOUND_STATUS_CD, WC_WA_EXUDATE_AMOUNT_CD, WC_WA_EXUDATE_TYPE_CD, WC_WA_ODOR_CD, WC_WA_PAIN_SEVERITY_CD,
        WC_WA_PAIN_AT_DRESSING, WC_WA_FEVER_CD, WC_WA_LENGTH_MM, WC_WA_WIDTH_MM, WC_WA_DEPTH_MM,
        WC_WA_LOCATION_CD, WC_WA_LATERALITY_CD, WC_WA_LOCATION_NOTES, WC_WA_SWAB_TAKEN, WC_WA_NUTRITION_CD,
        WC_WA_DRESSING_CD, WC_WA_MEDICATION_CD, WC_WA_PROCEDURE_CD,
        WC_WA_TREATMENT_NOTES, WC_WA_CLINICAL_OBSERVATION, WC_WA_WOUND_CHANGES, WC_WA_FOLLOW_UP_REQUIRED
    ) VALUES (
        v_assessment, p_hos_no, p_diagnosis, p_detail, p_type,
        v_clinician_cd, v_wct_cd, v_doctor_cd, v_entry_user, v_assessed_at,
        v_status, v_amount, v_exudate_type, v_odor, v_pain,
        v_pain_flag, v_fever, v_length, v_width, v_depth,
        v_location, v_laterality, v_loc_notes, v_swab_flag, v_nutrition,
        v_dressing, v_medication, v_procedure,
        v_treatment, v_observation, v_changes, v_follow_up
    );

    -- Multi-select findings: wound bed + wound margin
    v_findings := v_beds MULTISET UNION ALL v_margins;
    INSERT INTO WC_ASSESSMENT_FINDING_TB (WC_AF_CODE, WC_AF_ASSESSMENT_CD, WC_AF_CHOICE_CD)
    SELECT FN_WC_UUID(), v_assessment, t.COLUMN_VALUE
    FROM TABLE(v_findings) t;

    -- Photos already stored on disk by the application
    v_photo_stage := CASE WHEN p_type = 'INITIAL' THEN 'INITIAL' ELSE 'REVIEW' END;
    v_photo_count := FN_WC_XML_ITEM_COUNT(p_assessment, 'photos');
    FOR i IN 1 .. v_photo_count LOOP
        v_photo         := FN_WC_XML_ITEM(p_assessment, 'photos', i);
        v_photo_code    := NVL(FN_WC_XML_TEXT(v_photo, 'code'), FN_WC_UUID());
        v_photo_path    := FN_WC_XML_TEXT(v_photo, 'path');
        v_photo_caption := FN_WC_XML_TEXT(v_photo, 'caption');
        v_photo_taken   := NVL(FN_WC_TO_DATE(FN_WC_XML_TEXT(v_photo, 'taken_at')), FN_WC_NOW());

        INSERT INTO WC_WOUND_PHOTO_TB (
            WC_PHOTO_CODE, WC_PHOTO_HOS_NO, WC_PHOTO_REG_DIAGNOSIS_CD, WC_PHOTO_REVIEW_DETAIL_CD, WC_PHOTO_STAGE,
            WC_PHOTO_PATH, WC_PHOTO_CAPTION, WC_PHOTO_TAKEN_AT, WC_PHOTO_NURSE_CD, WC_PHOTO_ENTRY_USER_CD,
            WC_PHOTO_ASSESSMENT_CD
        ) VALUES (
            v_photo_code, p_hos_no, p_diagnosis, p_detail, v_photo_stage,
            v_photo_path, v_photo_caption, v_photo_taken, v_clinician_cd, v_entry_user,
            v_assessment
        );
    END LOOP;
END SP_WC_INSERT_ASSESSMENT;
/
SHOW ERRORS

-- ----------------------------------------------------------------------------
-- SP_WC_ACCEPT_REGISTRATION
--   p_request  رمز طلب التحويل
--   p_payload  XML نموذج القبول (الحقول المتحقق منها في AcceptRegistrationRequest)
--   p_result   OUT: رمز التسجيل الجديد
-- رقم المستشفى من WC_REQ_HOS_NO لصف الطلب.
-- ----------------------------------------------------------------------------
CREATE OR REPLACE PROCEDURE SP_WC_ACCEPT_REGISTRATION(
    p_request  IN  VARCHAR2,
    p_payload  IN  CLOB,
    p_result   OUT VARCHAR2
)
IS
    v_payload       XMLTYPE := XMLTYPE(p_payload);
    v_registration  VARCHAR2(36 CHAR) := FN_WC_UUID();
    v_patient       WC_REQUEST_REG_TB.WC_REQ_PATIENT_CD%TYPE;
    v_hos_no        WC_REQUEST_REG_TB.WC_REQ_HOS_NO%TYPE;
    v_status        WC_REQUEST_REG_TB.WC_REQ_STATUS%TYPE;
    v_decided       NUMBER;
    v_diag_count    NUMBER;
    v_pmh_codes     WC_CODE_LIST;
    v_item          XMLTYPE;
    v_diagnosis     VARCHAR2(36 CHAR);
    v_valid_master  NUMBER;
    v_nurse         VARCHAR2(100 CHAR);
    v_nurse_cd      NUMBER(9);
    v_entry_user    VARCHAR2(100 CHAR);
    v_next_visit    DATE;
    v_assessed_at   DATE;
    v_department    VARCHAR2(100 CHAR);
    v_procedure     VARCHAR2(100 CHAR);
    v_pmh_notes     VARCHAR2(4000 CHAR);
    v_has_allergies NUMBER(1);
    v_allergies     VARCHAR2(4000 CHAR);
    v_no_comorbid   NUMBER(1);
    v_frequency     VARCHAR2(100 CHAR);
    v_objective     VARCHAR2(4000 CHAR);
    v_pmh_code      VARCHAR2(100 CHAR);
    v_diag_code     VARCHAR2(100 CHAR);
    v_wtype_code    VARCHAR2(100 CHAR);
    v_loc_notes     VARCHAR2(4000 CHAR);
BEGIN
    v_nurse         := FN_WC_XML_TEXT(v_payload, 'clinic_nurse_code');
    v_entry_user    := FN_WC_XML_TEXT(v_payload, 'entry_user_code');
    v_next_visit    := FN_WC_TO_DATE(FN_WC_XML_TEXT(v_payload, 'next_visit_date'));
    v_diag_count    := FN_WC_XML_ITEM_COUNT(v_payload, 'diagnoses');
    v_pmh_codes     := FN_WC_XML_CODES(v_payload, 'pmh_codes');
    v_assessed_at   := NVL(FN_WC_TO_DATE(FN_WC_XML_TEXT(v_payload, 'assessed_at')), FN_WC_NOW());
    v_department    := FN_WC_XML_TEXT(v_payload, 'department_code');
    v_procedure     := FN_WC_XML_TEXT(v_payload, 'procedure_code');
    v_pmh_notes     := FN_WC_XML_TEXT(v_payload, 'pmh_notes');
    v_has_allergies := FN_WC_XML_FLAG(v_payload, 'has_allergies');
    v_allergies     := FN_WC_XML_TEXT(v_payload, 'allergies_description');
    v_no_comorbid   := FN_WC_XML_FLAG(v_payload, 'no_comorbidities');
    v_frequency     := FN_WC_XML_TEXT(v_payload, 'follow_up_frequency');
    v_objective     := FN_WC_XML_TEXT(v_payload, 'follow_up_objective');

    BEGIN
        SELECT WC_REQ_PATIENT_CD, WC_REQ_HOS_NO, WC_REQ_STATUS INTO v_patient, v_hos_no, v_status
        FROM WC_REQUEST_REG_TB WHERE WC_REQ_CODE = p_request
        FOR UPDATE;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            RAISE_APPLICATION_ERROR(-20001, 'REQUEST_NOT_FOUND');
    END;

    SELECT COUNT(*) INTO v_decided FROM WC_REGISTRATION_TB WHERE WC_REG_REQUEST_CD = p_request;
    IF v_status NOT IN ('PENDING', 'VISIT_IN_PROGRESS') OR v_decided > 0 THEN
        RAISE_APPLICATION_ERROR(-20001, 'REQUEST_ALREADY_DECIDED');
    END IF;
    IF v_diag_count = 0 THEN
        RAISE_APPLICATION_ERROR(-20001, 'AT_LEAST_ONE_WOUND_REQUIRED');
    END IF;
    IF v_has_allergies = 1 AND TRIM(v_allergies) IS NULL THEN
        RAISE_APPLICATION_ERROR(-20001, 'ALLERGY_DESCRIPTION_REQUIRED');
    END IF;
    IF v_no_comorbid = 1 AND v_pmh_codes.COUNT > 0 THEN
        RAISE_APPLICATION_ERROR(-20001, 'PMH_NONE_IS_EXCLUSIVE');
    END IF;
    IF v_next_visit IS NULL AND TRIM(v_frequency) IS NULL THEN
        RAISE_APPLICATION_ERROR(-20001, 'FOLLOW_UP_PLAN_REQUIRED');
    END IF;
    IF FN_WC_EMPLOYEE_EXISTS(v_nurse, v_hos_no) = 0 THEN
        RAISE_APPLICATION_ERROR(-20001, 'INVALID_EMPLOYEE');
    END IF;
    v_nurse_cd := FN_WC_TO_CODE(v_nurse);

    UPDATE WC_REQUEST_REG_TB
    SET WC_REQ_STATUS = 'ACCEPTED', WC_REQ_REVIEWED_BY_CD = v_nurse_cd, WC_REQ_REVIEWED_ON = FN_WC_NOW()
    WHERE WC_REQ_CODE = p_request;

    INSERT INTO WC_REGISTRATION_TB (
        WC_REG_CODE, WC_REG_HOS_NO, WC_REG_REQUEST_CD, WC_REG_PATIENT_CD, WC_REG_DEPARTMENT_CD, WC_REG_PROCEDURE_CD,
        WC_REG_PMH_NOTES, WC_REG_DATE, WC_REG_NURSE_CD, WC_REG_ENTRY_USER_CD, WC_REG_STATUS,
        WC_REG_HAS_ALLERGIES, WC_REG_ALLERGIES_DESCRIPTION, WC_REG_NO_COMORBIDITIES,
        WC_REG_NEXT_VISIT_DATE, WC_REG_FOLLOW_UP_FREQUENCY, WC_REG_FOLLOW_UP_OBJECTIVE
    ) VALUES (
        v_registration, v_hos_no, p_request, v_patient, v_department, v_procedure,
        v_pmh_notes, v_assessed_at, v_nurse_cd, v_entry_user, 'ACTIVE',
        v_has_allergies, v_allergies, v_no_comorbid,
        v_next_visit, v_frequency, v_objective
    );

    -- Past medical history: only active conditions are kept (same as the MySQL JOIN)
    FOR i IN 1 .. v_pmh_codes.COUNT LOOP
        v_pmh_code := v_pmh_codes(i);
        INSERT INTO WC_REGISTRATION_PMH_TB (WC_RPMH_CODE, WC_RPMH_REGISTRATION_CD, WC_RPMH_PMH_CD)
        SELECT FN_WC_UUID(), v_registration, WC_PMH_CODE
        FROM WC_PMH_TB
        WHERE WC_PMH_CODE = v_pmh_code AND WC_PMH_IS_ACTIVE = 1;
    END LOOP;

    -- Each wound with its initial structured assessment
    FOR i IN 1 .. v_diag_count LOOP
        v_item       := FN_WC_XML_ITEM(v_payload, 'diagnoses', i);
        v_diagnosis  := FN_WC_UUID();
        v_diag_code  := FN_WC_XML_TEXT(v_item, 'diagnosis_code');
        v_wtype_code := FN_WC_XML_TEXT(v_item, 'wound_type_code');
        v_loc_notes  := FN_WC_XML_TEXT(v_item, 'location_notes');

        SELECT (SELECT COUNT(*) FROM WC_WOUND_TYPE_TB WHERE WC_WTYPE_CODE = v_wtype_code AND WC_WTYPE_IS_ACTIVE = 1)
             * (SELECT COUNT(*) FROM WC_DIAGNOSIS_TB WHERE WC_DIAG_CODE = v_diag_code AND WC_DIAG_IS_ACTIVE = 1)
        INTO v_valid_master FROM DUAL;
        IF v_valid_master = 0 THEN
            RAISE_APPLICATION_ERROR(-20001, 'INVALID_WOUND_MASTER_DATA');
        END IF;

        INSERT INTO WC_REG_DIAGNOSIS_TB (
            WC_RDIAG_CODE, WC_RDIAG_HOS_NO, WC_RDIAG_REGISTRATION_CD, WC_RDIAG_DIAGNOSIS_CD, WC_RDIAG_WOUND_TYPE_CD,
            WC_RDIAG_DESCRIPTION, WC_RDIAG_CLINICAL_EMP_CD, WC_RDIAG_ENTRY_USER_CD
        ) VALUES (
            v_diagnosis, v_hos_no, v_registration, v_diag_code, v_wtype_code,
            v_loc_notes, v_nurse_cd, v_entry_user
        );

        SP_WC_INSERT_ASSESSMENT(v_diagnosis, NULL, 'INITIAL', v_hos_no, v_payload, v_item);
    END LOOP;

    IF v_next_visit IS NOT NULL THEN
        INSERT INTO WC_SCHEDULED_REVIEW_TB (
            WC_SREV_CODE, WC_SREV_REGISTRATION_CD, WC_SREV_CREATED_BY_CD, WC_SREV_SCHEDULED_DATE, WC_SREV_VISIT_SERIAL,
            WC_SREV_STATUS, WC_SREV_NOTES, WC_SREV_CREATED_ON, WC_SREV_COMPLETED_ON
        ) VALUES (
            FN_WC_UUID(), v_registration, v_entry_user, v_next_visit, 1,
            'PENDING', v_objective, FN_WC_NOW(), NULL
        );
    END IF;

    -- Pending appointments of the patient's older registrations (same hospital) are superseded by this one
    DELETE FROM WC_SCHEDULED_REVIEW_TB s
    WHERE s.WC_SREV_STATUS = 'PENDING'
      AND s.WC_SREV_REGISTRATION_CD IN (
          SELECT r.WC_REG_CODE FROM WC_REGISTRATION_TB r
          WHERE r.WC_REG_PATIENT_CD = v_patient
            AND r.WC_REG_HOS_NO = v_hos_no
            AND r.WC_REG_CODE <> v_registration
      );

    p_result := v_registration;
END SP_WC_ACCEPT_REGISTRATION;
/
SHOW ERRORS

-- ----------------------------------------------------------------------------
-- SP_WC_COMPLETE_REVIEW
--   p_registration  رمز التسجيل
--   p_scheduled     رمز الموعد المجدول، أو NULL لمراجعة يومية
--   p_payload       XML نموذج المراجعة (StoreDailyReviewRequest)، فيه details لكل جروح التسجيل
--   p_result        OUT: رمز زيارة المراجعة
-- رقم المستشفى من WC_REG_HOS_NO لصف التسجيل.
-- إعادة إرسال النموذج نفسه (submission_key نفسه) تُرجع الزيارة المحفوظة سابقاً ولا تنشئ صفاً جديداً.
-- ----------------------------------------------------------------------------
CREATE OR REPLACE PROCEDURE SP_WC_COMPLETE_REVIEW(
    p_registration  IN  VARCHAR2,
    p_scheduled     IN  VARCHAR2,
    p_payload       IN  CLOB,
    p_result        OUT VARCHAR2
)
IS
    v_payload          XMLTYPE := XMLTYPE(p_payload);
    v_review           VARCHAR2(36 CHAR);
    v_submission_key   VARCHAR2(100 CHAR);
    v_status           WC_REGISTRATION_TB.WC_REG_STATUS%TYPE;
    v_hos_no           WC_REGISTRATION_TB.WC_REG_HOS_NO%TYPE;
    v_schedule_status  WC_SCHEDULED_REVIEW_TB.WC_SREV_STATUS%TYPE;
    v_detail_count     NUMBER;
    v_item             XMLTYPE;
    v_detail_codes     WC_CODE_LIST := WC_CODE_LIST();
    v_expected         NUMBER;
    v_covered          NUMBER;
    v_serial           NUMBER;
    v_diagnosis        VARCHAR2(100 CHAR);
    v_detail           VARCHAR2(36 CHAR);
    v_nurse            VARCHAR2(100 CHAR);
    v_nurse_cd         NUMBER(9);
    v_entry_user       VARCHAR2(100 CHAR);
    v_overall_status   VARCHAR2(30 CHAR);
    v_next_visit       DATE;
    v_follow_up        NUMBER(1);
    v_assessed_at      DATE;
    v_general_notes    VARCHAR2(32767);
    v_frequency        VARCHAR2(100 CHAR);
    v_objective        VARCHAR2(4000 CHAR);
    v_review_type      VARCHAR2(20 CHAR);
    v_observation      VARCHAR2(32767);
    v_changes          VARCHAR2(32767);
    v_dressing         VARCHAR2(100 CHAR);
    v_medication       VARCHAR2(100 CHAR);
    v_outcome          VARCHAR2(30 CHAR);

    FUNCTION existing_review RETURN VARCHAR2
    IS
        v_code VARCHAR2(36 CHAR);
    BEGIN
        IF v_submission_key IS NULL THEN
            RETURN NULL;
        END IF;
        SELECT MAX(WC_DREV_CODE) INTO v_code FROM WC_DAILY_REVIEW_TB WHERE WC_DREV_SUBMISSION_KEY = v_submission_key;
        RETURN v_code;
    END existing_review;
BEGIN
    v_submission_key := FN_WC_XML_TEXT(v_payload, 'submission_key');
    v_nurse          := FN_WC_XML_TEXT(v_payload, 'nurse_code');
    v_entry_user     := FN_WC_XML_TEXT(v_payload, 'entry_user_code');
    v_overall_status := FN_WC_XML_TEXT(v_payload, 'overall_status');
    v_next_visit     := FN_WC_TO_DATE(FN_WC_XML_TEXT(v_payload, 'next_visit_date'));
    v_assessed_at    := NVL(FN_WC_TO_DATE(FN_WC_XML_TEXT(v_payload, 'assessed_at')), FN_WC_NOW());
    v_general_notes  := FN_WC_XML_TEXT(v_payload, 'general_notes');
    v_frequency      := FN_WC_XML_TEXT(v_payload, 'follow_up_frequency');
    v_objective      := FN_WC_XML_TEXT(v_payload, 'follow_up_objective');
    v_detail_count   := FN_WC_XML_ITEM_COUNT(v_payload, 'details');
    v_review_type    := CASE WHEN p_scheduled IS NULL THEN 'DAILY' ELSE 'SCHEDULED' END;

    -- Same form submitted twice: return the visit already saved
    v_review := existing_review();
    IF v_review IS NOT NULL THEN
        p_result := v_review;
        RETURN;
    END IF;

    BEGIN
        SELECT WC_REG_STATUS, WC_REG_HOS_NO INTO v_status, v_hos_no
        FROM WC_REGISTRATION_TB WHERE WC_REG_CODE = p_registration
        FOR UPDATE;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            RAISE_APPLICATION_ERROR(-20001, 'REGISTRATION_NOT_FOUND');
    END;
    IF p_scheduled IS NULL AND v_status <> 'ACTIVE' THEN
        RAISE_APPLICATION_ERROR(-20001, 'REGISTRATION_NOT_ACTIVE');
    END IF;

    IF p_scheduled IS NOT NULL THEN
        BEGIN
            SELECT WC_SREV_STATUS INTO v_schedule_status
            FROM WC_SCHEDULED_REVIEW_TB
            WHERE WC_SREV_CODE = p_scheduled AND WC_SREV_REGISTRATION_CD = p_registration
            FOR UPDATE;
        EXCEPTION
            WHEN NO_DATA_FOUND THEN
                v_schedule_status := NULL;
        END;
        IF v_schedule_status IS NULL OR v_schedule_status <> 'PENDING' THEN
            RAISE_APPLICATION_ERROR(-20001, 'SCHEDULE_NOT_PENDING_OR_OWNED');
        END IF;
    END IF;

    -- Every wound of the registration exactly once
    FOR i IN 1 .. v_detail_count LOOP
        v_item := FN_WC_XML_ITEM(v_payload, 'details', i);
        v_detail_codes.EXTEND;
        v_detail_codes(v_detail_codes.LAST) := FN_WC_XML_TEXT(v_item, 'registration_diagnosis_code');
    END LOOP;

    SELECT COUNT(*) INTO v_expected FROM WC_REG_DIAGNOSIS_TB WHERE WC_RDIAG_REGISTRATION_CD = p_registration;
    SELECT COUNT(DISTINCT t.COLUMN_VALUE) INTO v_covered
    FROM TABLE(v_detail_codes) t
    JOIN WC_REG_DIAGNOSIS_TB d ON d.WC_RDIAG_CODE = t.COLUMN_VALUE AND d.WC_RDIAG_REGISTRATION_CD = p_registration;
    IF v_detail_count <> v_expected OR v_covered <> v_expected THEN
        RAISE_APPLICATION_ERROR(-20001, 'EXACT_DIAGNOSIS_COVERAGE_REQUIRED');
    END IF;

    IF FN_WC_EMPLOYEE_EXISTS(v_nurse, v_hos_no) = 0 THEN
        RAISE_APPLICATION_ERROR(-20001, 'INVALID_EMPLOYEE');
    END IF;
    v_nurse_cd := FN_WC_TO_CODE(v_nurse);

    v_review := FN_WC_UUID();
    BEGIN
        INSERT INTO WC_DAILY_REVIEW_TB (
            WC_DREV_CODE, WC_DREV_HOS_NO, WC_DREV_REGISTRATION_CD, WC_DREV_SCHEDULED_REVIEW_CD, WC_DREV_VISIT_DATE,
            WC_DREV_NURSE_CD, WC_DREV_ENTRY_USER_CD, WC_DREV_OVERALL_STATUS, WC_DREV_GENERAL_NOTES,
            WC_DREV_SUBMISSION_KEY
        ) VALUES (
            v_review, v_hos_no, p_registration, p_scheduled, v_assessed_at,
            v_nurse_cd, v_entry_user, v_overall_status, v_general_notes,
            v_submission_key
        );
    EXCEPTION
        -- A parallel request with the same submission_key won the race: return its visit
        WHEN DUP_VAL_ON_INDEX THEN
            v_review := existing_review();
            IF v_review IS NULL THEN
                RAISE;
            END IF;
            p_result := v_review;
            RETURN;
    END;

    FOR i IN 1 .. v_detail_count LOOP
        v_item        := FN_WC_XML_ITEM(v_payload, 'details', i);
        v_diagnosis   := FN_WC_XML_TEXT(v_item, 'registration_diagnosis_code');
        v_detail      := FN_WC_UUID();
        v_follow_up   := CASE WHEN FN_WC_XML_FLAG(v_item, 'follow_up_required') = 0 THEN 0 ELSE 1 END;
        v_observation := FN_WC_XML_TEXT(v_item, 'clinical_observation');
        v_changes     := FN_WC_XML_TEXT(v_item, 'wound_changes');
        v_dressing    := FN_WC_XML_TEXT(v_item, 'dressing_code');
        v_medication  := FN_WC_XML_TEXT(v_item, 'medication_code');
        v_outcome     := FN_WC_XML_TEXT(v_item, 'outcome_status');

        INSERT INTO WC_REVIEW_DETAIL_TB (
            WC_REVD_CODE, WC_REVD_REVIEW_CD, WC_REVD_REVIEW_TYPE, WC_DREVD_REG_DIAGNOSIS_CD,
            WC_DREVD_WOUND_DESCRIPTION, WC_DREVD_WOUND_CHANGES, WC_DREVD_DRESSING_CD, WC_DREVD_MEDICATION_CD,
            WC_DREVD_STATUS, WC_DREVD_FOLLOW_UP_REQUIRED
        ) VALUES (
            v_detail, v_review, v_review_type, v_diagnosis,
            v_observation, v_changes, v_dressing, v_medication,
            v_outcome, v_follow_up
        );

        SP_WC_INSERT_ASSESSMENT(v_diagnosis, v_detail, 'FOLLOW_UP', v_hos_no, v_payload, v_item);
    END LOOP;

    IF p_scheduled IS NOT NULL THEN
        UPDATE WC_SCHEDULED_REVIEW_TB
        SET WC_SREV_STATUS = 'COMPLETED', WC_SREV_COMPLETED_ON = FN_WC_NOW()
        WHERE WC_SREV_CODE = p_scheduled;
    END IF;

    UPDATE WC_REGISTRATION_TB
    SET WC_REG_NEXT_VISIT_DATE = v_next_visit,
        WC_REG_FOLLOW_UP_FREQUENCY = v_frequency,
        WC_REG_FOLLOW_UP_OBJECTIVE = v_objective
    WHERE WC_REG_CODE = p_registration;

    IF v_next_visit IS NOT NULL THEN
        SELECT NVL(MAX(WC_SREV_VISIT_SERIAL), 0) + 1 INTO v_serial
        FROM WC_SCHEDULED_REVIEW_TB WHERE WC_SREV_REGISTRATION_CD = p_registration;

        INSERT INTO WC_SCHEDULED_REVIEW_TB (
            WC_SREV_CODE, WC_SREV_REGISTRATION_CD, WC_SREV_CREATED_BY_CD, WC_SREV_SCHEDULED_DATE, WC_SREV_VISIT_SERIAL,
            WC_SREV_STATUS, WC_SREV_NOTES, WC_SREV_CREATED_ON, WC_SREV_COMPLETED_ON
        ) VALUES (
            FN_WC_UUID(), p_registration, v_entry_user, v_next_visit, v_serial,
            'PENDING', v_objective, FN_WC_NOW(), NULL
        );
    END IF;

    IF v_overall_status IN ('IMPROVED_DISCHARGE', 'WORSENED_ADMISSION', 'DEATH') THEN
        UPDATE WC_REGISTRATION_TB
        SET WC_REG_STATUS = 'DISCHARGED', WC_REG_DISCHARGED_AT = FN_WC_NOW(), WC_REG_DISCHARGED_BY_EMP_CD = v_nurse_cd
        WHERE WC_REG_CODE = p_registration;
    END IF;

    p_result := v_review;
END SP_WC_COMPLETE_REVIEW;
/
SHOW ERRORS

-- ----------------------------------------------------------------------------
-- SP_WC_ADD_WOUND
--   p_registration  رمز التسجيل (يجب أن يكون نشطاً)
--   p_payload       XML نموذج الجرح (StoreWoundDiagnosisRequest)؛ بيانات الجرح في <assessment> أو في الجذر
--   p_result        OUT: رمز الجرح الجديد
-- رقم المستشفى من WC_REG_HOS_NO لصف التسجيل.
-- ----------------------------------------------------------------------------
CREATE OR REPLACE PROCEDURE SP_WC_ADD_WOUND(
    p_registration  IN  VARCHAR2,
    p_payload       IN  CLOB,
    p_result        OUT VARCHAR2
)
IS
    v_payload       XMLTYPE := XMLTYPE(p_payload);
    v_item          XMLTYPE;
    v_status        WC_REGISTRATION_TB.WC_REG_STATUS%TYPE;
    v_hos_no        WC_REGISTRATION_TB.WC_REG_HOS_NO%TYPE;
    v_diagnosis     VARCHAR2(36 CHAR) := FN_WC_UUID();
    v_valid_master  NUMBER;
    v_diag_code     VARCHAR2(100 CHAR);
    v_wtype_code    VARCHAR2(100 CHAR);
    v_loc_notes     VARCHAR2(4000 CHAR);
    v_nurse         VARCHAR2(100 CHAR);
    v_nurse_cd      NUMBER(9);
    v_entry_user    VARCHAR2(100 CHAR);
BEGIN
    BEGIN
        SELECT WC_REG_STATUS, WC_REG_HOS_NO INTO v_status, v_hos_no
        FROM WC_REGISTRATION_TB WHERE WC_REG_CODE = p_registration
        FOR UPDATE;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            RAISE_APPLICATION_ERROR(-20001, 'REGISTRATION_NOT_FOUND');
    END;
    IF v_status <> 'ACTIVE' THEN
        RAISE_APPLICATION_ERROR(-20001, 'REGISTRATION_NOT_ACTIVE');
    END IF;

    -- The wound data is in <assessment> when it has children, otherwise at the root
    IF v_payload.existsNode('/*/assessment/*') = 1 THEN
        v_item := v_payload.extract('/*/assessment');
    ELSE
        v_item := v_payload;
    END IF;
    v_diag_code  := FN_WC_XML_TEXT(v_item, 'diagnosis_code');
    v_wtype_code := FN_WC_XML_TEXT(v_item, 'wound_type_code');
    v_loc_notes  := FN_WC_XML_TEXT(v_item, 'location_notes');
    v_nurse      := FN_WC_XML_TEXT(v_payload, 'clinic_nurse_code');
    v_entry_user := FN_WC_XML_TEXT(v_payload, 'entry_user_code');

    IF v_nurse IS NOT NULL AND FN_WC_EMPLOYEE_EXISTS(v_nurse, v_hos_no) = 0 THEN
        RAISE_APPLICATION_ERROR(-20001, 'INVALID_EMPLOYEE');
    END IF;
    v_nurse_cd := FN_WC_TO_CODE(v_nurse);

    SELECT (SELECT COUNT(*) FROM WC_WOUND_TYPE_TB WHERE WC_WTYPE_CODE = v_wtype_code AND WC_WTYPE_IS_ACTIVE = 1)
         * (SELECT COUNT(*) FROM WC_DIAGNOSIS_TB WHERE WC_DIAG_CODE = v_diag_code AND WC_DIAG_IS_ACTIVE = 1)
    INTO v_valid_master FROM DUAL;
    IF v_valid_master = 0 THEN
        RAISE_APPLICATION_ERROR(-20001, 'INVALID_WOUND_MASTER_DATA');
    END IF;

    INSERT INTO WC_REG_DIAGNOSIS_TB (
        WC_RDIAG_CODE, WC_RDIAG_HOS_NO, WC_RDIAG_REGISTRATION_CD, WC_RDIAG_DIAGNOSIS_CD, WC_RDIAG_WOUND_TYPE_CD,
        WC_RDIAG_DESCRIPTION, WC_RDIAG_CLINICAL_EMP_CD, WC_RDIAG_ENTRY_USER_CD
    ) VALUES (
        v_diagnosis, v_hos_no, p_registration, v_diag_code, v_wtype_code,
        v_loc_notes, v_nurse_cd, v_entry_user
    );

    SP_WC_INSERT_ASSESSMENT(v_diagnosis, NULL, 'INITIAL', v_hos_no, v_payload, v_item);

    p_result := v_diagnosis;
END SP_WC_ADD_WOUND;
/
SHOW ERRORS

-- ----------------------------------------------------------------------------
-- تحقق: يجب ألا تُرجع أي صف
-- ----------------------------------------------------------------------------
SELECT object_name, object_type FROM all_objects WHERE owner = 'WCCMS' AND status = 'INVALID';
