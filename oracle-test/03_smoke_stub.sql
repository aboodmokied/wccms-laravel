-- ============================================================================
-- 03_smoke_stub.sql: 03_smoke_test.sql on the HOS stub of 01_hos_stub.sql (run.ps1 / run.sh run it as WCCMS).
-- Hospital 1; employee 1001 exists in hospitals 1 and 2, 1002 only in 1, 2001 only in 2; patient 5001.
-- ============================================================================
SET DEFINE ^
SET VERIFY OFF
DEFINE hos_schema = HOS
DEFINE hos = 1
DEFINE nurse = 1002
DEFINE user_emp = 1001
DEFINE foreign_emp = 2001
DEFINE patient = 5001
DEFINE mode = STUB
@@03_smoke_test.sql
