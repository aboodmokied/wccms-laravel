<aside class="m8-sidebar offcanvas-lg offcanvas-start" id="m8-sidebar" tabindex="-1" aria-labelledby="sidebar-title">

    <div class="offcanvas-header d-lg-none">
        <h2 class="offcanvas-title fs-5">القائمة الرئيسية</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#m8-sidebar" aria-label="إغلاق القائمة"></button>
    </div>

    {{-- Brand / Logo --}}
    <a href="{{ route('dashboard') }}" class="m8-brand text-decoration-none">
        <div class="m8-brand-icon">
            <i class="bi bi-heart-pulse-fill text-white"></i>
        </div>
        <div class="d-flex flex-column">
            <span class="m8-brand-title" id="sidebar-title">WCCMS</span>
            <span class="text-muted" style="font-size: 0.725rem;">عيادة العناية بالجروح</span>
        </div>
    </a>

    {{-- Navigation Menu --}}
    <nav class="m8-menu-container" aria-label="التنقل الرئيسي">

        <div class="m8-menu-section">العمل اليومي</div>

        <a href="{{ route('dashboard') }}"
           class="m8-nav-item {{ request()->routeIs('dashboard') || request()->is('/') ? 'active' : '' }}" @if(request()->routeIs('dashboard') || request()->is('/')) aria-current="page" @endif>
            <i class="bi bi-speedometer2"></i>
            <span>الرئيسية</span>
        </a>

        <a href="{{ route('requests.index') }}"
           class="m8-nav-item {{ request()->routeIs('requests.index', 'requests.show', 'requests.create') ? 'active' : '' }}" @if(request()->routeIs('requests.index', 'requests.show', 'requests.create')) aria-current="page" @endif>
            <i class="bi bi-send-plus-fill"></i>
            <span>طلبات الإحالة</span>
        </a>

        <a href="{{ route('registrations.index') }}"
           class="m8-nav-item {{ request()->routeIs('registrations.index', 'registrations.show') ? 'active' : '' }}" @if(request()->routeIs('registrations.index', 'registrations.show')) aria-current="page" @endif>
            <i class="bi bi-people-fill"></i>
            <span>المرضى المسجلون</span>
        </a>

        <a href="{{ route('reviews.daily') }}"
           class="m8-nav-item {{ request()->routeIs('reviews.daily*', 'registrations.daily-reviews.*') ? 'active' : '' }}" @if(request()->routeIs('reviews.daily*', 'registrations.daily-reviews.*')) aria-current="page" @endif>
            <i class="bi bi-clipboard2-pulse-fill"></i>
            <span>المراجعة اليومية</span>
        </a>

        <a href="{{ route('reviews.scheduled') }}"
           class="m8-nav-item {{ request()->routeIs('reviews.scheduled*') ? 'active' : '' }}" @if(request()->routeIs('reviews.scheduled*')) aria-current="page" @endif>
            <i class="bi bi-calendar-check-fill"></i>
            <span>المواعيد</span>
        </a>

        <div class="m8-menu-section mt-3">السجلات والتقارير</div>

        <a href="{{ route('patients.index') }}"
           class="m8-nav-item {{ request()->routeIs('patients.*') ? 'active' : '' }}" @if(request()->routeIs('patients.*')) aria-current="page" @endif>
            <i class="bi bi-file-earmark-medical-fill"></i>
            <span>السجل الطبي</span>
        </a>

        <a href="{{ route('reports.history') }}"
           class="m8-nav-item {{ request()->routeIs('reports.history') ? 'active' : '' }}" @if(request()->routeIs('reports.history')) aria-current="page" @endif>
            <i class="bi bi-bar-chart-line-fill"></i>
            <span>التقارير</span>
        </a>

        <a href="{{ route('reports.monthly.cases') }}"
           class="m8-nav-item {{ request()->routeIs('reports.monthly.*') ? 'active' : '' }}" @if(request()->routeIs('reports.monthly.*')) aria-current="page" @endif>
            <i class="bi bi-calendar3"></i>
            <span>التقارير الشهرية</span>
        </a>

        <a href="{{ route('requests.my') }}"
           class="m8-nav-item {{ request()->routeIs('requests.my') ? 'active' : '' }}" @if(request()->routeIs('requests.my')) aria-current="page" @endif>
            <i class="bi bi-person-lines-fill"></i>
            <span>طلباتي</span>
        </a>

        <a href="{{ route('admin.index') }}"
           class="m8-nav-item {{ request()->routeIs('admin.*') ? 'active' : '' }}" @if(request()->routeIs('admin.*')) aria-current="page" @endif>
            <i class="bi bi-gear-fill"></i>
            <span>الإدارة</span>
        </a>

    </nav>

    {{-- Sidebar Footer: User Info + Logout --}}
    <div class="m8-sidebar-footer">
        <div class="m8-user-pill">
            <div class="m8-user-avatar">
                {{ mb_substr(auth()->user()?->employee?->PER_EMP_FULL_NAME ?? auth()->user()?->PER_USER_USERNAME ?? 'م', 0, 1) }}
            </div>
            <div class="d-flex flex-column text-truncate flex-grow-1">
                <span class="fw-bold text-truncate" style="font-size: 0.82rem;">
                    {{ auth()->user()?->employee?->PER_EMP_FULL_NAME ?? auth()->user()?->PER_USER_USERNAME ?? 'مستخدم' }}
                </span>
                <span class="text-muted" style="font-size: 0.725rem;">
                    @if(auth()->user()?->PER_USER_ROLE === 'ADMIN') مسؤول النظام
                    @elseif(auth()->user()?->PER_USER_ROLE === 'NURSE') ممرض جروح
                    @else مستخدم @endif
                </span>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="m8-logout-btn mt-2">
                <i class="bi bi-box-arrow-right"></i>
                <span>تسجيل الخروج</span>
            </button>
        </form>
    </div>

</aside>
