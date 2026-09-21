<header class="m8-header bg-white border-bottom py-3 px-4 mb-4 d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ url('/dashboard') }}" class="text-decoration-none d-flex align-items-center gap-2">
            <span class="fs-4 fw-bold text-primary">{{ __('navigation.brand') }}</span>
        </a>
        <span class="text-muted small border-start ps-3 me-2">{{ __('navigation.subtitle') }}</span>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div class="text-end me-3">
            <span class="d-block fw-bold small text-dark">أ. فاطمة الزهراني</span>
            <span class="d-block text-muted x-small">{{ __('navigation.header.user_role') }}</span>
        </div>
        <button type="button" class="btn btn-sm btn-light-primary position-relative" title="{{ __('navigation.header.notifications') }}">
            <i class="bi bi-bell"></i> {{ __('navigation.header.notifications') }}
            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
        </button>
        <button type="button" class="btn btn-sm btn-light-danger" title="{{ __('navigation.header.logout') }}">
            {{ __('navigation.header.logout') }}
        </button>
    </div>
</header>
