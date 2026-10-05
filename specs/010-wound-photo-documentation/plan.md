# Implementation Plan: Phase 10 — Wound Photo Documentation (توثيق صور الجروح)

**Branch**: `010-wound-photo-documentation` | **Date**: 2026-10-05 | **Spec**: [specs/010-wound-photo-documentation/spec.md](file:///e:/wamp64/www/wccms/specs/010-wound-photo-documentation/spec.md)

---

## Summary

Phase 10 implements visual wound progress tracking through photo documentation at both initial registration/diagnosis (`WC_PHOTO_STAGE = 'INITIAL'`) and every subsequent daily review (`WC_PHOTO_STAGE = 'REVIEW'`).

Key Deliverables:
1. **Database Schema & Eloquent Model**: `WcWoundPhoto` model mapping to `wc_wound_photo_tb` with fields for photo stage, file storage path, optional caption, timestamp, nurse actor, and data-entry user.
2. **Initial Wound Photo Intake (Acceptance)**: Optional photo upload during initial registration request acceptance (`RegistrationRequestController@accept`) or direct diagnosis creation (`RegistrationController@storeDiagnosis`). Multiple photos supported per wound.
3. **Follow-up Review Photo Intake (Daily Review)**: Optional photo upload per wound detail during daily review recording (`DailyReviewController@store`). Multiple photos supported per wound item.
4. **Visual Gallery & Progression Timeline Component**: Interactive Blade partial rendering chronological photo progression per wound in patient registration details (`registrations/show.blade.php`) with side-by-side baseline comparison lightbox.
5. **Secure Storage Pipeline**: Laravel Storage disk (`storage/app/public/wound_photos`), HTTP upload validation (JPEG/PNG/WebP, <=5MB), and transactional rollback protection.

---

## Technical Context

**Language/Version**: PHP 8.3 (Laravel 11.x)  
**Storage**: MySQL `wccms` DB-First table `wc_wound_photo_tb` + Laravel Storage (`storage/app/public/wound_photos`)  
**Testing**: PHPUnit / Laravel Feature & Unit test runner  
**Target Platform**: Web (Modern desktop & mobile browsers with camera capture / file picker upload in Arabic RTL layout)  
**Performance Goals**: Image upload under 1s, photo progression gallery load under 400ms  
**Constraints**: 100% Arabic RTL layout, DB-First architecture rules (`CHAR(36)` UUID PK, `$timestamps = false`, `<PREFIX>_` column naming), dual audit tracking (nurse vs data-entry user), atomic storage/database transactions.

---

## Constitution Check

- **Database-First Architecture**: ✅ PASS. Table `wc_wound_photo_tb` created with standard PK `WC_PHOTO_CODE` (`CHAR(36)`), no default timestamps (`$timestamps = false`), explicit FK mappings.
- **Naming Protocol**: ✅ PASS. Prefix `WC_PHOTO_` for table columns.
- **Arabic/RTL Support**: ✅ PASS. Full Arabic interface for image upload inputs, photo captions, and timeline gallery.
- **Actor vs. Data-Entry Audit**: ✅ PASS. Dual audit tracking `WC_PHOTO_NURSE_CD` (examining nurse) and `WC_PHOTO_ENTRY_USER_CD` (authenticated user).

---

## Project Structure

### Documentation (this feature)

```text
specs/010-wound-photo-documentation/
├── spec.md              # Feature specification
├── plan.md              # Implementation plan (/speckit-plan output)
├── research.md          # Research & technical decisions
├── data-model.md        # DB-First entity mapping & relationships
├── quickstart.md        # Runnable verification guide
└── contracts/           # Route & controller API contracts
    └── photo-api.md
```

### Source Code

```text
app/
├── Models/
│   └── WcWoundPhoto.php
├── Http/
│   ├── Controllers/
│   │   ├── RegistrationRequestController.php (Updated for initial photo)
│   │   ├── DailyReviewController.php         (Updated for review photo)
│   │   └── WoundPhotoController.php          (Image viewer / gallery stream)
│   └── Requests/
│       ├── AcceptRegistrationRequest.php     (Updated for photo validation)
│       └── StoreDailyReviewRequest.php       (Updated for photo validation)
resources/views/
├── registrations/
│   └── partials/
│       └── wound_photo_timeline.blade.php    (Wound visual progression timeline)
└── daily_reviews/
    └── partials/
        └── wound_photo_uploader.blade.php
```

---

## Planned Artifacts & Phases

- **Phase 0 Research**: [specs/010-wound-photo-documentation/research.md](file:///e:/wamp64/www/wccms/specs/010-wound-photo-documentation/research.md)
- **Phase 1 Data Model**: [specs/010-wound-photo-documentation/data-model.md](file:///e:/wamp64/www/wccms/specs/010-wound-photo-documentation/data-model.md)
- **Phase 1 Contracts**: [specs/010-wound-photo-documentation/contracts/photo-api.md](file:///e:/wamp64/www/wccms/specs/010-wound-photo-documentation/contracts/photo-api.md)
- **Phase 1 Quickstart**: [specs/010-wound-photo-documentation/quickstart.md](file:///e:/wamp64/www/wccms/specs/010-wound-photo-documentation/quickstart.md)
