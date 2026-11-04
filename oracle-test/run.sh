#!/usr/bin/env bash
# Runs the WCCMS Oracle scripts on Oracle XE 11g (Docker) from an empty database, then the smoke test.
#   ./run.sh             HOS stub + wccms_oracle_install.sql + 03_smoke_test.sql
#   ./run.sh --with-fk   the same, plus the optional composite foreign keys (section 8) and 05_fk_check.sql
# Passwords: letters, digits and underscore only (they are passed unquoted to sqlplus).
set -euo pipefail

cd "$(dirname "$0")"

CONTAINER=wccms-oracle11
ROOT=..
export ORACLE_PASSWORD="${ORACLE_PASSWORD:-Oracle_Test_1}"
WCCMS_PASSWORD="${WCCMS_PASSWORD:-Wccms_Test_1}"
WCCMS_TABLESPACE="${WCCMS_TABLESPACE:-USERS}"
WITH_FK=0
if [ "${1:-}" = "--with-fk" ]; then
    WITH_FK=1
fi

docker compose up -d

echo "Waiting for Oracle XE 11g (the first start takes a few minutes)..."
status=starting
for _ in $(seq 1 120); do
    status=$(docker inspect -f '{{.State.Health.Status}}' "$CONTAINER" 2>/dev/null || echo starting)
    if [ "$status" = "healthy" ]; then
        break
    fi
    sleep 5
done
if [ "$status" != "healthy" ]; then
    echo "Oracle did not become healthy. See: docker logs $CONTAINER" >&2
    exit 1
fi

# build/: copies with LF line endings (sqlplus on Linux does not see "/\r" as a terminator) and the two
# placeholders replaced.
mkdir -p build
prepare() {
    sed -e 's/\r$//' \
        -e "s/{{WCCMS_PASSWORD}}/${WCCMS_PASSWORD}/g" \
        -e "s/{{WCCMS_TABLESPACE}}/${WCCMS_TABLESPACE}/g" \
        "$1" > "$2"
}
prepare 01_hos_stub.sql build/01_hos_stub.sql
prepare 02_wccms_user.sql build/02_wccms_user.sql
prepare "$ROOT/wccms_oracle_install.sql" build/02_wccms_oracle_install.sql
prepare "$ROOT/wccms_oracle_uninstall.sql" build/06_wccms_oracle_uninstall.sql
prepare 03_smoke_test.sql build/03_smoke_test.sql
prepare 03_smoke_stub.sql build/03_smoke_stub.sql
prepare 05_fk_check.sql build/05_fk_check.sql

if grep -nE '\{\{[A-Z_]+\}\}' build/*.sql; then
    echo "A placeholder was not replaced (see above)." >&2
    exit 1
fi

# Section 9 of the install file (commented out): from its first ALTER TABLE line to the end, without "-- ".
# The REFERENCES grants it needs are in 02_wccms_user.sql.
if ! grep -q '^-- ALTER TABLE WC_USERS_TB ADD CONSTRAINT FK_WC_USERS_EMPLOYEE' build/02_wccms_oracle_install.sql; then
    echo "Section 9 (first ALTER TABLE) not found in wccms_oracle_install.sql." >&2
    exit 1
fi
{
    echo "WHENEVER SQLERROR EXIT FAILURE ROLLBACK"
    echo "SET DEFINE OFF"
    sed -n '/^-- ALTER TABLE WC_USERS_TB ADD CONSTRAINT FK_WC_USERS_EMPLOYEE/,$p' build/02_wccms_oracle_install.sql | sed -E 's/^-- ?//'
    echo "PROMPT Composite foreign keys to HOS enabled."
} > build/04_enable_fk.sql

# sql USER PASSWORD FILE: runs /kit/FILE in the container with SQL*Plus; stops this script on a non-zero exit.
sql() {
    echo
    echo ">>> $3 (as $1)"
    docker exec \
        -e NLS_LANG=AMERICAN_AMERICA.AL32UTF8 \
        -e "U=$1" -e "PW=$2" -e "F=/kit/$3" \
        "$CONTAINER" bash -c 'echo exit | sqlplus -L -S $U/$PW@//localhost:1521/XE @$F'
}

sql system "$ORACLE_PASSWORD" build/01_hos_stub.sql
sql system "$ORACLE_PASSWORD" build/02_wccms_user.sql
# Install, uninstall, install again: the uninstall file must leave the account ready for a clean reinstall.
sql wccms "$WCCMS_PASSWORD" build/02_wccms_oracle_install.sql
sql wccms "$WCCMS_PASSWORD" build/06_wccms_oracle_uninstall.sql
sql wccms "$WCCMS_PASSWORD" build/02_wccms_oracle_install.sql
if [ "$WITH_FK" = "1" ]; then
    sql wccms "$WCCMS_PASSWORD" build/04_enable_fk.sql
fi
sql wccms "$WCCMS_PASSWORD" build/03_smoke_stub.sql
if [ "$WITH_FK" = "1" ]; then
    sql wccms "$WCCMS_PASSWORD" build/05_fk_check.sql
fi

echo
echo "All steps finished without errors."
