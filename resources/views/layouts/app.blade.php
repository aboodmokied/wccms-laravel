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

    <!-- Page-Specific Asset Stacks -->
    @stack('styles')
</head>
<body>
    <a href="#main-content" class="wccms-skip-link">انتقل إلى محتوى الصفحة</a>

    {{-- ================================================================
         METRONIC 8 LAYOUT: Fixed Sidebar + Main Wrapper
         ================================================================ --}}

    {{-- SIDEBAR --}}
    @include('partials.sidebar')

    {{-- MAIN WRAPPER --}}
    <div class="m8-main-wrapper d-flex flex-column min-vh-100">

        {{-- HEADER --}}
        @include('partials.header')

        {{-- PAGE CONTENT --}}
        <main id="main-content" class="wccms-content flex-grow-1" tabindex="-1">
            @yield('content')
        </main>

        {{-- FOOTER --}}
        @include('partials.footer')

    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Page-Specific Script Stacks -->
    @stack('scripts')
</body>
</html>
