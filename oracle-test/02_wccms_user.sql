-- ============================================================================
-- 02_wccms_user.sql: the Docker kit's application account WCCMS (run as SYSTEM, after 01_hos_stub.sql).
--
-- wccms_oracle_install.sql no longer creates a user: it installs into the account it runs in. The kit gives
-- it a dedicated account with the least it needs: CREATE TABLE / PROCEDURE / TYPE, a quota, SELECT on the
-- 8 HOS tables, and REFERENCES on the two tables of the optional composite foreign keys (section 9).
-- ============================================================================

WHENEVER SQLERROR EXIT FAILURE ROLLBACK
SET DEFINE OFF

CREATE USER WCCMS IDENTIFIED BY "{{WCCMS_PASSWORD}}" DEFAULT TABLESPACE USERS QUOTA UNLIMITED ON USERS;
GRANT CREATE SESSION, CREATE TABLE, CREATE PROCEDURE, CREATE TYPE TO WCCMS;

GRANT SELECT ON HOS.MR_PATIENT_TB TO WCCMS;
GRANT SELECT ON HOS.EMPLOYEE_TB TO WCCMS;
GRANT SELECT ON HOS.ADMISSION_PATIENT_TB TO WCCMS;
GRANT SELECT ON HOS.INPATIENT_ADM_TB TO WCCMS;
GRANT SELECT ON HOS.C_LOCATION_TB TO WCCMS;
GRANT SELECT ON HOS.C_SEX_TB TO WCCMS;
GRANT SELECT ON HOS.C_JOB_TB TO WCCMS;
GRANT SELECT ON HOS.C_HOSPITAL_TB TO WCCMS;

GRANT REFERENCES ON HOS.MR_PATIENT_TB TO WCCMS;
GRANT REFERENCES ON HOS.EMPLOYEE_TB TO WCCMS;

PROMPT User WCCMS created.
