# RBAC Policies & Permission Matrix: Phase 3 — Auth & RBAC

**Branch**: `003-auth-rbac` | **Date**: 2026-09-22 | **Spec**: [spec.md](file:///e:/wamp64/www/wccms/specs/003-auth-rbac/spec.md)

This contract specifies the Role-Based Access Control (RBAC) permission matrix, gate definitions, and route authorization rules enforced by `EnsureUserHasRole` middleware and Laravel Gates.

---

## 1. Role Permission Matrix

| Module / Action | `ADMIN` Role | `NURSE` Role | Unassigned (`NULL`) / Inactive |
|---|---|---|---|
| View Dashboard (`/dashboard`) | **ALLOWED** | **ALLOWED** | **BLOCKED (403)** |
| View Personal Daily Queue | **ALLOWED** | **ALLOWED** | **BLOCKED (403)** |
| Create Registration Request (`/requests/create`) | **ALLOWED** | **ALLOWED** | **BLOCKED (403)** |
| Record Daily Clinical Review (`/reviews/create`) | **ALLOWED** | **ALLOWED** | **BLOCKED (403)** |
| Review / Approve Registration Requests (`/requests/review`) | **ALLOWED** | **BLOCKED (403)** | **BLOCKED (403)** |
| Manage User Roles & Active Status (`/admin/users`) | **ALLOWED** | **BLOCKED (403)** | **BLOCKED (403)** |
| Edit Master Reference Data (Dressings, Meds, Depts) | **ALLOWED** | **BLOCKED (403)** | **BLOCKED (403)** |

---

## 2. Arabic Unauthorized Response Contract

When a user with the `NURSE` role attempts to access an `ADMIN`-only route or action:
- **HTTP Status**: `403 Forbidden`
- **Web View / Exception**: Renders an Arabic authorization error view displaying:
  > **غير مصرح لك بالوصول إلى هذه الصفحة**  
  > *عفواً، لا تملك الصلاحيات الكافية للوصول إلى هذه الخاصية.*
- **JSON API Response**:
  ```json
  {
    "error": "unauthorized",
    "message": "غير مصرح لك بالوصول إلى هذه الصفحة"
  }
  ```

---

## 3. Laravel Gate Definitions

```php
Gate::define('admin-only', function (User $user) {
    return $user->PER_USER_IS_ACTIVE && $user->PER_USER_ROLE === 'ADMIN';
});

Gate::define('clinic-staff', function (User $user) {
    return $user->PER_USER_IS_ACTIVE && in_array($user->PER_USER_ROLE, ['ADMIN', 'NURSE'], true);
});
```
