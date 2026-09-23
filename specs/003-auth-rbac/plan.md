# Implementation Plan: Phase 3 — Auth & RBAC

**Branch**: `003-auth-rbac` | **Date**: 2026-09-22 | **Spec**: [spec.md](file:///e:/wamp64/www/wccms/specs/003-auth-rbac/spec.md)

**Input**: Feature specification from `specs/003-auth-rbac/spec.md`

## Summary

Phase 3 implements WCCMS identity resolution, Role-Based Access Control (RBAC), and user-to-employee profile linkage. WCCMS delegates primary authentication to an external hospital system via signed JWT session artifacts (bearer tokens or cookies). WCCMS middleware extracts the identity, auto-provisions missing user records into `PER_USERS_TB` (with `NULL` role and `PER_USER_IS_ACTIVE = 0` pending Admin approval), enforces WCCMS-local roles (`ADMIN` vs `NURSE`), and links users to `PER_EMPLOYEE_TB` records to satisfy Actor vs. Data-Entry separation. Unauthenticated users are redirected to `APP_HOSPITAL_LOGIN_URL`, and all access control errors are returned in Arabic with RTL support.

## Technical Context

**Language/Version**: PHP 8.3 (Laravel 11 framework)  
**Primary Dependencies**: `firebase/php-jwt` or `lcobucci/jwt` for JWT verification, Laravel Auth & Middleware subsystem, PHPUnit for feature testing  
**Storage**: MySQL 8.0 local database (`wccms` schema), using existing `per_users_tb` and `per_employee_tb` tables  
**Testing**: PHPUnit feature & unit tests (`php artisan test --compact`)  
**Target Platform**: Web application (Windows WAMP local server / PHP 8.3)  
**Project Type**: Monolithic Laravel Web Application  
**Performance Goals**: Middleware identity resolution overhead < 50ms; Dashboard load < 1.5s after identity resolution  
**Constraints**: Must adhere to Constitution Principles (Database-First naming conventions, Stack assumptions, Arabic/RTL first-class support, Actor vs Data-Entry separation, MySQL-first local development)  
**Scale/Scope**: ~50 active hospital staff users, 2 RBAC roles (`ADMIN`, `NURSE`), 2 core user/employee tables  

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Requirement / Constraint | Compliance Status | Notes |
|---|---|---|---|
| **I. Stack Assumption** | Laravel (PHP 8.3) + Database | **PASS** | Middleware & RBAC built natively in Laravel 11 / PHP 8.3. |
| **II. Database-First Architecture** | Eloquent models map to existing `*_tb` tables without replacing stored procedure workflows | **PASS** | `User` and `Employee` models bind strictly to `per_users_tb` and `per_employee_tb`. |
| **III. Unified Naming Protocol** | Tables end in `_tb`, PKs are `<PREFIX>_CODE` UUIDs, FKs end in `_CD` | **PASS** | `PER_USER_CODE` (PK), `PER_EMP_CODE` (PK), `PER_EMP_USER_CD` (FK). |
| **IV. First-Class Arabic & RTL Support** | All user-facing error & status messages localized in Arabic | **PASS** | All auth/permission denial screens and notifications rendered in Arabic ("تم إيقاف صلاحية الوصول", "حسابك قيد المراجعة والاعتماد", etc.). |
| **V. Actor vs. Data-Entry Separation** | Distinguish Clinical Actor (`PER_EMPLOYEE_TB`) from Data-Entry User (`PER_USERS_TB`) | **PASS** | Header displays linked employee full name (`PER_EMP_FULL_NAME`); models maintain `PER_EMP_USER_CD` linkage. |
| **VI. MySQL-First, Oracle-Later** | Fluent migrations / driver-agnostic model configuration | **PASS** | Uses standard Laravel schema conventions matching existing MySQL `wccms` schema. |

## Project Structure

### Documentation (this feature)

```text
specs/003-auth-rbac/
├── spec.md              # Feature specification
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── data-model.md        # Phase 1 output (/speckit-plan command)
├── quickstart.md        # Phase 1 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command)
│   ├── jwt-payload-schema.json
│   ├── middleware-auth-contract.md
│   └── rbac-policies-contract.md
└── checklists/
    └── requirements.md  # Specification quality checklist
```

### Source Code (repository root)

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── Admin/
│   │       └── UserController.php           # User role assignment, employee linkage, active toggle
│   ├── Middleware/
│   │   ├── HospitalJwtAuthMiddleware.php    # Resolves JWT identity, auto-provisions user, checks IS_ACTIVE
│   │   └── EnsureUserHasRole.php            # Enforces ADMIN vs NURSE RBAC per route
│   └── Requests/
│       └── UpdateUserAccessRequest.php      # Form request validation for admin role & employee updates
├── Models/
│   ├── User.php                             # Maps to per_users_tb
│   └── Employee.php                         # Maps to per_employee_tb
└── Providers/
    └── AuthServiceProvider.php             # Gates & Policies definitions for ADMIN / NURSE

routes/
├── web.php                                  # Protected web routes with middleware stack
└── api.php                                  # API endpoints guarded by hospital JWT middleware

tests/
├── Feature/
│   ├── HospitalJwtAuthMiddlewareTest.php   # Middleware identity resolution & auto-provisioning tests
│   ├── RbacAuthorizationTest.php            # Admin vs Nurse route access & 403 response tests
│   └── UserManagementTest.php              # Admin user role assignment & active toggle tests
└── Unit/
    └── UserModelTest.php                    # DB-First model key & relationship tests
```

**Structure Decision**: Monolithic Laravel structure. Extends existing `app/Models`, `app/Http/Middleware`, `app/Http/Controllers`, and `tests/Feature` directories matching project convention.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| *None* | All design choices adhere strictly to project constitution and Laravel conventions. | N/A |
