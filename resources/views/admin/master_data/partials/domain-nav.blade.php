<ul class="nav nav-pills nav-fill bg-light p-2 rounded shadow-sm mb-4">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active fw-bold' : 'text-dark' }}" href="{{ route('admin.departments.index') }}">
            <i class="bi bi-building me-1"></i>الأقسام
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.pmh.*') ? 'active fw-bold' : 'text-dark' }}" href="{{ route('admin.pmh.index') }}">
            <i class="bi bi-journal-medical me-1"></i>التاريخ الطبي
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.diagnoses.*') ? 'active fw-bold' : 'text-dark' }}" href="{{ route('admin.diagnoses.index') }}">
            <i class="bi bi-activity me-1"></i>التشخيصات
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.wound_types.*') ? 'active fw-bold' : 'text-dark' }}" href="{{ route('admin.wound_types.index') }}">
            <i class="bi bi-patch-exclamation me-1"></i>أنواع الجروح
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.dressings.*') ? 'active fw-bold' : 'text-dark' }}" href="{{ route('admin.dressings.index') }}">
            <i class="bi bi-bandaid me-1"></i>الضمادات
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.medications.*') ? 'active fw-bold' : 'text-dark' }}" href="{{ route('admin.medications.index') }}">
            <i class="bi bi-capsule me-1"></i>الأدوية والعلاجات
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.procedures.*') ? 'active fw-bold' : 'text-dark' }}" href="{{ route('admin.procedures.index') }}">
            <i class="bi bi-scissors me-1"></i>الإجراءات السريرية
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.assessment_choices.*') ? 'active fw-bold' : 'text-dark' }}" href="{{ route('admin.assessment_choices.index') }}">
            <i class="bi bi-ui-checks-grid me-1"></i>خيارات التقييم
        </a>
    </li>
</ul>
