<aside class="m8-sidebar" id="m8-sidebar">

    {{-- Brand / Logo --}}
    <a href="{{ route('dashboard') }}" class="m8-brand text-decoration-none">
        <div class="m8-brand-icon">
            <i class="bi bi-heart-pulse-fill text-white"></i>
        </div>
        <div class="d-flex flex-column">
            <span class="m8-brand-title">WCCMS</span>
            <span class="text-muted" style="font-size: 0.725rem;">عيادة العناية بالجروح</span>
        </div>
    </a>

    {{-- Navigation Menu --}}
    <div class="m8-menu-container">

        <div class="m8-menu-section">القائمة الرئيسية</div>

        <a href="{{ route('dashboard') }}"
           class="m8-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>لوحة التحكم</span>
        </a>

        <a href="{{ route('requests.index') }}"
           class="m8-nav-item {{ request()->routeIs('requests.index', 'requests.show') ? 'active' : '' }}">
            <i class="bi bi-send-plus-fill"></i>
            <span>طلبات الإحالة</span>
        </a>

        <a href="{{ route('requests.create') }}"
           class="m8-nav-item {{ request()->routeIs('requests.create') ? 'active' : '' }}">
            <i class="bi bi-plus-circle-fill"></i>
            <span>إحالة جديدة</span>
        </a>

        <div class="m8-menu-section mt-3">السجلات السريرية</div>

        <a href="{{ route('registrations.index') }}"
           class="m8-nav-item {{ request()->routeIs('registrations.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i>
            <span>المرضى المسجلون</span>
        </a>

        <a href="{{ route('patients.index') }}"
           class="m8-nav-item {{ request()->routeIs('patients.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-medical-fill"></i>
            <span>السجل الطبي للمريض</span>
        </a>

        <a href="{{ route('reviews.daily') }}"
           class="m8-nav-item {{ request()->routeIs('reviews.daily') ? 'active' : '' }}">
            <i class="bi bi-clipboard2-pulse-fill"></i>
            <span>الزيارة اليومية</span>
        </a>

        <a href="{{ route('reviews.scheduled') }}"
           class="m8-nav-item {{ request()->routeIs('reviews.scheduled*') ? 'active' : '' }}">
            <i class="bi bi-calendar-check-fill"></i>
            <span>المراجعات المجدولة</span>
        </a>

        <div class="m8-menu-section mt-3">النظام</div>

        <a href="{{ route('reports.history') }}"
           class="m8-nav-item {{ request()->routeIs('reports.history') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line-fill"></i>
            <span>التقارير</span>
        </a>

        <a href="{{ route('requests.my') }}"
           class="m8-nav-item {{ request()->routeIs('requests.my') ? 'active' : '' }}">
            <i class="bi bi-person-lines-fill"></i>
            <span>طلباتي</span>
        </a>

        @if(auth()->user()?->PER_USER_ROLE === 'ADMIN')
            <a href="{{ route('admin.index') }}"
               class="m8-nav-item {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                <i class="bi bi-gear-fill"></i>
                <span>الإدارة</span>
            </a>
        @endif

    </div>

    {{-- Sidebar Footer: User Info + Logout --}}
    <div class="m8-sidebar-footer">
        <div class="m8-user-pill">
            <div class="m8-user-avatar">
                {{ mb_substr(auth()->user()?->employee?->PER_EMP_FULL_NAME ?? auth()->user()?->PER_USER_USERNAME ?? 'م', 0, 1) }}
            </div>
            <div class="d-flex flex-column text-truncate flex-grow-1">
                <span class="fw-bold text-white text-truncate" style="font-size: 0.82rem;">
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
