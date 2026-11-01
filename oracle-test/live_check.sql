-- ============================================================================
-- live_check.sql: the same checks as the Docker kit (03_smoke_test.sql), against the REAL hospital database,
-- during deployment and BEFORE going live.
--
-- Run as WCCMS after wccms_oracle_schema.sql and wccms_oracle_procedures.sql, from the oracle-test folder:
--
--   sqlplus WCCMS/<password>@//<host>:<port>/<service> @live_check.sql <HOSPITAL_NO> <nurse EMP_SERIAL> <patient MRP_PATREC_CODE>
--
--   <HOSPITAL_NO>        the HOSPITAL_NO of .env (wccms:check-hospital prints it)
--   <nurse EMP_SERIAL>   any active employee of that hospital with no WCCMS account yet (a nurse, ideally)
--   <patient ...>        any patient of that hospital
--
-- Nothing stays in the database: the procedures never COMMIT and the script ends with ROLLBACK (also on an
-- error). It reads the 8 HOS tables, then accepts a test referral, adds a wound, records two reviews and a
-- discharge, and checks the rejections (unknown employee, request already decided...). The employee used as
-- "another hospital's employee" is one serial past the highest of this hospital.
-- Expected last lines: "RESULT: NN passed, 0 failed" and "SMOKE TEST: PASS".
-- ============================================================================
SET DEFINE ^
SET VERIFY OFF
DEFINE hos_schema = HOS
DEFINE hos = ^1
DEFINE nurse = ^2
DEFINE user_emp = ^2
DEFINE patient = ^3
DEFINE mode = LIVE

COLUMN foreign_emp_value NEW_VALUE foreign_emp NOPRINT
SELECT TO_CHAR(NVL(MAX(EMP_SERIAL), 0) + 1) AS foreign_emp_value FROM ^hos_schema..EMPLOYEE_TB WHERE HOS_NO = ^hos;

@@03_smoke_test.sql
