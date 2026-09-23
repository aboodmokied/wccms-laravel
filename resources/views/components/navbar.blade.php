<header class="m8-header bg-white border-bottom py-3 px-4 mb-4 d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ url('/dashboard') }}" class="text-decoration-none d-flex align-items-center gap-2">
            <span class="fs-4 fw-bold text-primary">نظام عيادة الجروح (WCCMS)</span>
        </a>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div class="text-end me-3">
            <span class="d-block fw-bold small text-dark">
                {{ auth()->user()?->employee?->PER_EMP_FULL_NAME ?? auth()->user()?->PER_USER_USERNAME ?? 'مستخدم العيادة' }}
            </span>
            <span class="d-block text-muted x-small">
                @if(auth()->user()?->PER_USER_ROLE === 'ADMIN')
                    مسؤول النظام (Admin)
                @elseif(auth()->user()?->PER_USER_ROLE === 'NURSE')
                    ممرض جروح (Nurse)
                @else
                    قيد الاعتماد
                @endif
            </span>
        </div>
        <form action="{{ route('logout') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-light-danger">
                تسجيل الخروج
            </button>
        </form>
    </div>
</header>
