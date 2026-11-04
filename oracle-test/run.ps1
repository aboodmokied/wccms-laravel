# Runs the WCCMS Oracle scripts on Oracle XE 11g (Docker) from an empty database, then the smoke test.
#   .\run.ps1                    HOS stub + wccms_oracle_install.sql + 03_smoke_test.sql
#   .\run.ps1 -WithForeignKeys   the same, plus the optional composite foreign keys (section 8) and 05_fk_check.sql
# Passwords: letters, digits and underscore only (they are passed unquoted to sqlplus).
param(
    [switch]$WithForeignKeys
)

# Native commands report failure through $LASTEXITCODE, checked after each call.
$ErrorActionPreference = 'Continue'
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
Set-Location $PSScriptRoot

$Container = 'wccms-oracle11'
$Root = Split-Path $PSScriptRoot -Parent
if (-not $env:ORACLE_PASSWORD) { $env:ORACLE_PASSWORD = 'Oracle_Test_1' }
$OraclePassword = $env:ORACLE_PASSWORD
$WccmsPassword = if ($env:WCCMS_PASSWORD) { $env:WCCMS_PASSWORD } else { 'Wccms_Test_1' }
$Tablespace = if ($env:WCCMS_TABLESPACE) { $env:WCCMS_TABLESPACE } else { 'USERS' }
$Utf8 = New-Object System.Text.UTF8Encoding $false

function Stop-Run([string]$Message) {
    Write-Host $Message -ForegroundColor Red
    exit 1
}

docker compose up -d
if ($LASTEXITCODE -ne 0) { Stop-Run 'docker compose up failed. Is Docker Desktop running?' }

Write-Host 'Waiting for Oracle XE 11g (the first start takes a few minutes)...'
$status = 'starting'
for ($i = 0; $i -lt 120; $i++) {
    $status = docker inspect -f '{{.State.Health.Status}}' $Container 2>$null
    if ($status -eq 'healthy') { break }
    Start-Sleep -Seconds 5
}
if ($status -ne 'healthy') { Stop-Run "Oracle did not become healthy. See: docker logs $Container" }

# build\: copies with LF line endings (sqlplus on Linux does not see "/\r" as a terminator) and the two
# placeholders replaced.
New-Item -ItemType Directory -Force (Join-Path $PSScriptRoot 'build') | Out-Null

function Copy-Prepared([string]$Source, [string]$Target) {
    $text = [System.IO.File]::ReadAllText($Source, $Utf8)
    $text = $text.Replace("`r`n", "`n").Replace('{{WCCMS_PASSWORD}}', $WccmsPassword).Replace('{{WCCMS_TABLESPACE}}', $Tablespace)
    [System.IO.File]::WriteAllText((Join-Path $PSScriptRoot $Target), $text, $Utf8)
}

Copy-Prepared (Join-Path $PSScriptRoot '01_hos_stub.sql') 'build/01_hos_stub.sql'
Copy-Prepared (Join-Path $PSScriptRoot '02_wccms_user.sql') 'build/02_wccms_user.sql'
Copy-Prepared (Join-Path $Root 'wccms_oracle_install.sql') 'build/02_wccms_oracle_install.sql'
Copy-Prepared (Join-Path $Root 'wccms_oracle_uninstall.sql') 'build/06_wccms_oracle_uninstall.sql'
Copy-Prepared (Join-Path $PSScriptRoot '03_smoke_test.sql') 'build/03_smoke_test.sql'
Copy-Prepared (Join-Path $PSScriptRoot '03_smoke_stub.sql') 'build/03_smoke_stub.sql'
Copy-Prepared (Join-Path $PSScriptRoot '05_fk_check.sql') 'build/05_fk_check.sql'

$left = Select-String -Path (Join-Path $PSScriptRoot 'build/*.sql') -Pattern '\{\{[A-Z_]+\}\}' -CaseSensitive
if ($left) {
    $left | ForEach-Object { Write-Host $_ }
    Stop-Run 'A placeholder was not replaced (see above).'
}

# Section 9 of the install file (commented out): from its first ALTER TABLE line to the end, without "-- ".
# The REFERENCES grants it needs are in 02_wccms_user.sql.
$schemaLines = [System.IO.File]::ReadAllLines((Join-Path $PSScriptRoot 'build/02_wccms_oracle_install.sql'), $Utf8)
$start = -1
for ($i = 0; $i -lt $schemaLines.Length; $i++) {
    if ($schemaLines[$i].StartsWith('-- ALTER TABLE WC_USERS_TB ADD CONSTRAINT FK_WC_USERS_EMPLOYEE')) { $start = $i; break }
}
if ($start -lt 0) { Stop-Run 'Section 9 (first ALTER TABLE) not found in wccms_oracle_install.sql.' }
$fkLines = @('WHENEVER SQLERROR EXIT FAILURE ROLLBACK', 'SET DEFINE OFF')
$fkLines += $schemaLines[$start..($schemaLines.Length - 1)] | ForEach-Object { $_ -replace '^-- ?', '' }
$fkLines += 'PROMPT Composite foreign keys to HOS enabled.'
[System.IO.File]::WriteAllText((Join-Path $PSScriptRoot 'build/04_enable_fk.sql'), (($fkLines -join "`n") + "`n"), $Utf8)

# Runs /kit/<File> in the container with SQL*Plus; stops this script on a non-zero exit code.
function Invoke-Sql([string]$User, [string]$Password, [string]$File) {
    Write-Host ''
    Write-Host ">>> $File (as $User)"
    docker exec -e NLS_LANG=AMERICAN_AMERICA.AL32UTF8 -e "U=$User" -e "PW=$Password" -e "F=/kit/$File" $Container `
        bash -c 'echo exit | sqlplus -L -S $U/$PW@//localhost:1521/XE @$F'
    if ($LASTEXITCODE -ne 0) { Stop-Run "$File failed (exit code $LASTEXITCODE)." }
}

Invoke-Sql 'system' $OraclePassword 'build/01_hos_stub.sql'
Invoke-Sql 'system' $OraclePassword 'build/02_wccms_user.sql'
# Install, uninstall, install again: the uninstall file must leave the account ready for a clean reinstall.
Invoke-Sql 'wccms' $WccmsPassword 'build/02_wccms_oracle_install.sql'
Invoke-Sql 'wccms' $WccmsPassword 'build/06_wccms_oracle_uninstall.sql'
Invoke-Sql 'wccms' $WccmsPassword 'build/02_wccms_oracle_install.sql'
if ($WithForeignKeys) { Invoke-Sql 'wccms' $WccmsPassword 'build/04_enable_fk.sql' }
Invoke-Sql 'wccms' $WccmsPassword 'build/03_smoke_stub.sql'
if ($WithForeignKeys) { Invoke-Sql 'wccms' $WccmsPassword 'build/05_fk_check.sql' }

Write-Host ''
Write-Host 'All steps finished without errors.' -ForegroundColor Green
