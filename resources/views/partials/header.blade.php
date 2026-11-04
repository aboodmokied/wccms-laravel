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
