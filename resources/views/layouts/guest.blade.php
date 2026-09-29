<!DOCTYPE html>
<html lang="ar" dir="rtl" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'مركـز العنايـة بالـجروح') - WCCMS</title>

    <!-- Bootstrap 5 RTL + Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Laravel Vite Bundled Master Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="m8-guest-body">

    {{-- Minimal guest header --}}
    <header class="m8-guest-header">
        <div class="d-flex align-items-center gap-2">
            <div class="m8-brand-icon">
                <i class="bi bi-heart-pulse-fill text-white"></i>
            </div>
            <div>
                <span class="fw-bold text-dark" style="font-size:0.95rem;">مركز العناية بالجروح</span>
                <span class="d-block text-muted" style="font-size:0.68rem;letter-spacing:0.05em;">WCCMS</span>
            </div>
        </div>
    </header>

    {{-- Page Content --}}
    <main class="flex-grow-1 d-flex align-items-start">
        <div class="container py-4">
            @yield('content')
        </div>
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
