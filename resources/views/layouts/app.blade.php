<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'مركـز العنايـة بالـجروح') - WCCMS</title>
    
    <!-- Bootstrap 5 RTL CSS (CDN fallback / styling grid) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Laravel Vite Bundled Master Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Page-Specific Asset Stacks -->
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <!-- Master Header Navbar Partial -->
    @include('partials.header')

    <div class="container-fluid px-4 flex-grow-1">
        <div class="row">
            <!-- Sidebar Navigation Partial -->
            <div class="col-12 col-md-3 col-lg-2">
                @include('partials.sidebar')
            </div>

            <!-- Page Main Content Slot -->
            <main class="col-12 col-md-9 col-lg-10">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Master Footer Partial -->
    @include('partials.footer')

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Page-Specific Script Stacks -->
    @stack('scripts')
</body>
</html>
