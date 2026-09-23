@extends('layouts.app')

@section('title', 'قائمة طلبات التسجيل والإحالات القادمة')

@section('content')
<div class="container-fluid py-3">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <x-card title="قائمة طلبات تحويل المرضى لعيادة العناية بالجروح" icon="bi-inbox-fill" subtitle="مراجعة الإحالات القادمة من الأقسام وتنسيق زيارات التقييم">
        <form action="{{ route('requests.index') }}" method="GET" class="mb-4">
            <div class="row g-2">
                <div class="col-md-6">
                    <x-search-filter name="search" value="{{ $query }}" placeholder="ابحث برقم الطلب، اسم المريض، أو الهوية..." />
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> تصفية</button>
                </div>
                @if($query)
                    <div class="col-md-2">
                        <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary w-100">إلغاء التصفية</a>
                    </div>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle border">
                <thead class="table-light">
                    <tr>
                        <th>كود الطلب</th>
                        <th>اسم المريض</th>
                        <th>رقم الهوية</th>
                        <th>الممرض المحول (القسم)</th>
                        <th>ممرض الزيارة/المراجع</th>
                        <th>الحالة</th>
                        <th>تاريخ الإحالة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $req)
                        <tr>
                            <td class="fw-bold text-primary">REQ-{{ substr($req->WC_REQ_CODE, 0, 8) }}</td>
                            <td class="fw-semibold">{{ $req->WC_REQ_PAT_NAME_SNAPSHOT }}</td>
                            <td><code>{{ $req->WC_REQ_PAT_ID_SNAPSHOT }}</code></td>
                            <td>{{ $req->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</td>
                            <td>{{ $req->reviewer?->PER_EMP_FULL_NAME ?? 'لم تبدأ الزيارة' }}</td>
                            <td><x-status-badge :status="$req->WC_REQ_STATUS" /></td>
                            <td>{{ \Carbon\Carbon::parse($req->WC_REQ_CREATED_AT)->format('Y-m-d H:i') }}</td>
                            <td class="text-center">
                                <a href="{{ route('requests.show', $req->WC_REQ_CODE) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye-fill me-1"></i> معاينة والتقييم
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                لا توجد طلبات تحويل قادمة حالياً.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $requests->links() }}
        </div>
    </x-card>
</div>
@endsection
