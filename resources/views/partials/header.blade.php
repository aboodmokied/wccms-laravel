<header class="m8-header">

    {{-- LEFT: Mobile toggle + Page title/breadcrumb --}}
    <div class="d-flex align-items-center gap-3">
        <button id="m8-mobile-toggle" class="btn btn-sm btn-icon btn-light d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#m8-sidebar" aria-controls="m8-sidebar" aria-label="فتح القائمة الرئيسية">
            <i class="bi bi-list fs-3"></i>
        </button>
        <div class="d-flex flex-column">
            <span class="fw-bold" style="font-size:0.95rem;">@yield('page-title', 'عيادة العناية بالجروح')</span>
        </div>
    </div>

    {{-- RIGHT: Date, quick actions, user --}}
    <div class="d-flex align-items-center gap-2 gap-md-3">

        {{-- Current Date --}}
        <span class="m8-header-date d-none d-md-inline-block" id="m8-current-date"></span>

        {{-- Quick new request --}}
        <a href="{{ route('requests.create') }}" class="btn btn-sm btn-primary align-items-center gap-1">
            <i class="bi bi-plus-lg"></i>
            <span>إحالة جديدة</span>
        </a>

        {{-- User Profile (dropdown) --}}
        <div class="dropdown">
            <button class="m8-header-user" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="قائمة الحساب">
                <div class="d-flex flex-column text-end d-none d-md-flex">
                    <span class="fw-semibold" style="font-size: 0.82rem;">
                        {{ auth()->user()?->employee?->full_name ?? auth()->user()?->PER_USER_USERNAME ?? 'مستخدم' }}
                    </span>
                    <span class="text-muted" style="font-size: 0.7rem;">
                        @if(auth()->user()?->PER_USER_ROLE === 'ADMIN') مسؤول النظام
                        @elseif(auth()->user()?->PER_USER_ROLE === 'NURSE') ممرض جروح
                        @else مستخدم @endif
                    </span>
                </div>
                <div class="m8-header-avatar">
                    {{ mb_substr(auth()->user()?->employee?->full_name ?? auth()->user()?->PER_USER_USERNAME ?? 'م', 0, 1) }}
                </div>
                <i class="bi bi-chevron-down text-muted d-none d-md-inline" style="font-size: 0.65rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-1 py-1" style="min-width: 220px;">
                <li class="px-3 py-2 border-bottom bg-light">
                    <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ auth()->user()?->employee?->full_name ?? auth()->user()?->PER_USER_USERNAME }}</div>
                    <div class="text-muted" style="font-size: 0.72rem;">{{ auth()->user()?->PER_USER_USERNAME }}</div>
                </li>
                <li><a class="dropdown-item py-2" href="{{ route('requests.my') }}"><i class="bi bi-journal-text me-2 text-primary"></i>طلباتي</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('registrations.index') }}"><i class="bi bi-people me-2 text-success"></i>المسجلون</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i>تسجيل الخروج</button>
                    </form>
                </li>
            </ul>
        </div>

    </div>

</header>

<script>
    // Display current date in header
    (function () {
        const el = document.getElementById('m8-current-date');
        if (el) {
            const now = new Date();
            el.textContent = now.toLocaleDateString('ar-SA', { year: 'numeric', month: 'long', day: 'numeric' });
        }
    })();
</script>
