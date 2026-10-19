@php
    $departmentQuery = ['departments' => $selectedDepartments];
    $tabs = [
        'reports.monthly.cases' => ['label' => 'حالات الشهر', 'icon' => 'bi-list-ul'],
        'reports.monthly.summary' => ['label' => 'الملخص', 'icon' => 'bi-bar-chart-line'],
        'reports.monthly.readmissions' => ['label' => 'إعادة الدخول', 'icon' => 'bi-arrow-repeat'],
    ];
@endphp

<ul class="nav nav-tabs m8-report-tabs mb-4">
    @foreach($tabs as $routeName => $tab)
        <li class="nav-item">
            <a href="{{ route($routeName, $departmentQuery) }}"
               class="nav-link {{ request()->routeIs($routeName) ? 'active' : '' }}"
               @if(request()->routeIs($routeName)) aria-current="page" @endif>
                <i class="bi {{ $tab['icon'] }} me-1"></i>{{ $tab['label'] }}
            </a>
        </li>
    @endforeach
</ul>
