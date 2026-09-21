<aside class="m8-sidebar bg-white border-end p-3 rounded-3 shadow-sm mb-4">
    <div class="nav flex-column nav-pills gap-1">
        <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }} d-flex align-items-center gap-2">
            <i class="bi bi-speedometer2"></i>
            <span>{{ __('navigation.sidebar.dashboard') }}</span>
        </a>
        <a href="{{ url('/requests') }}" class="nav-link {{ request()->is('requests*') ? 'active' : '' }} d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-plus"></i>
            <span>{{ __('navigation.sidebar.requests') }}</span>
        </a>
        <a href="{{ url('/registrations') }}" class="nav-link {{ request()->is('registrations') ? 'active' : '' }} d-flex align-items-center gap-2">
            <i class="bi bi-people"></i>
            <span>{{ __('navigation.sidebar.registrations') }}</span>
        </a>
        <a href="{{ url('/registrations/create') }}" class="nav-link {{ request()->is('registrations/create') ? 'active' : '' }} d-flex align-items-center gap-2 ps-4 text-secondary">
            <i class="bi bi-person-plus"></i>
            <span>{{ __('navigation.sidebar.complete_registration') }}</span>
        </a>
        <a href="{{ url('/reviews/daily') }}" class="nav-link {{ request()->is('reviews/daily*') ? 'active' : '' }} d-flex align-items-center gap-2">
            <i class="bi bi-clipboard2-pulse"></i>
            <span>{{ __('navigation.sidebar.daily_review') }}</span>
        </a>
        <a href="{{ url('/reviews/scheduled') }}" class="nav-link {{ request()->is('reviews/scheduled*') ? 'active' : '' }} d-flex align-items-center gap-2">
            <i class="bi bi-calendar-check"></i>
            <span>{{ __('navigation.sidebar.scheduled_reviews') }}</span>
        </a>
        <a href="{{ url('/admin') }}" class="nav-link {{ request()->is('admin*') ? 'active' : '' }} d-flex align-items-center gap-2">
            <i class="bi bi-gear"></i>
            <span>{{ __('navigation.sidebar.admin') }}</span>
        </a>
        <a href="{{ url('/reports/history') }}" class="nav-link {{ request()->is('reports/history*') ? 'active' : '' }} d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-bar-graph"></i>
            <span>{{ __('navigation.sidebar.patient_history') }}</span>
        </a>
    </div>
</aside>
