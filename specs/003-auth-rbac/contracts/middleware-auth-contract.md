# Middleware Interface Contract: `HospitalJwtAuthMiddleware`

**Branch**: `003-auth-rbac` | **Date**: 2026-09-22 | **Spec**: [spec.md](file:///e:/wamp64/www/wccms/specs/003-auth-rbac/spec.md)

This contract defines the execution behavior, inputs, resolution pipeline, and status responses for `App\Http\Middleware\HospitalJwtAuthMiddleware`.

---

## 1. Input Inspection Order

The middleware inspects incoming HTTP requests for a hospital JWT token in the following priority order:
1. `Authorization` Header (`Authorization: Bearer <jwt_token>`)
2. HTTP Cookie (`hospital_token`)

---

## 2. Decision Pipeline & Response Contracts

```mermaid
flowchart TD
    A[Incoming Request] --> B{JWT Present?}
    B -- No --> C[Web: Redirect APP_HOSPITAL_LOGIN_URL / API: 401]
    B -- Yes --> D{JWT Valid?}
    D -- Invalid/Expired --> E[Clear session, Web: Redirect APP_HOSPITAL_LOGIN_URL / API: 401]
    D -- Valid --> F{PER_USER_USERNAME Exists in DB?}
    F -- No --> G[Auto-provision PER_USERS_TB record: ROLE=NULL, ACTIVE=0]
    G --> H[Return 403: Arabic Pending Approval Error]
    F -- Yes --> I{PER_USER_IS_ACTIVE == 1?}
    I -- No (0) --> J[Return 403: Arabic Disabled/Pending Error]
    I -- Yes (1) --> K{PER_USER_ROLE is NOT NULL?}
    K -- No --> H
    K -- Yes --> L[Auth::login(user) & Proceed to Route]
```

---

## 3. Response Statuses & Localization Messages

| Condition | Response Type | HTTP Status | Response Content / Redirect Target |
|---|---|---|---|
| Missing or Expired JWT (Web GET) | Redirect | `302 Found` | Redirects to `env('APP_HOSPITAL_LOGIN_URL')` |
| Missing or Expired JWT (API/JSON) | JSON | `401 Unauthorized` | `{"error": "unauthenticated", "message": "جلسة العمل غير صالحة، يرجى تسجيل الدخول عبر نظام المستشفى"}` |
| Invalid JWT Signature / Tampered | JSON / Redirect | `401 Unauthorized` | `{"error": "invalid_token", "message": "رمز التحقق غير صالح"}` |
| First Visit Auto-Provisioned / Role NULL | Render / JSON | `403 Forbidden` | `{"error": "pending_approval", "message": "حسابك قيد المراجعة والاعتماد من قبل مسؤول النظام"}` |
| Deactivated Account (`IS_ACTIVE` = 0) | Render / JSON | `403 Forbidden` | `{"error": "access_disabled", "message": "تم إيقاف صلاحية الوصول لنظام العيادة، يرجى مراجعة المسؤول"}` |
| Valid JWT & Active Account | Pass | Proceed | Initializes `Auth::user()` and passes control to route controller |
