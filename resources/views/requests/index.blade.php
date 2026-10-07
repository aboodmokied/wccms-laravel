@extends('layouts.app')

@section('title', 'طلبات الإحالة')

@section('content')
<div class="container-fluid py-3">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <h1 class="wccms-page-heading mb-1">طلبات الإحالة</h1>
    <p class="text-muted mb-4">ابحث عن المريض، ثم افتح الإحالة لمراجعتها.</p>

    <x-card>
        <form action="{{ route('requests.index') }}" method="GET" class="mb-4">
            <div class="row g-2">
                <div class="col-md-6">
                    <x-search-filter name="search" value="{{ $query }}" placeholder="ابحث برقم الطلب، اسم المريض، أو الهوية..." />
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>بحث</button>
                </div>
                @if($query)
                    <div class="col-md-2">
                        <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary w-100">إلغاء التصفية</a>
                    </div>
                @endif
            </div>
        </form>

        <div class="table-responsive" tabindex="0" role="region" aria-label="طلبات الإحالة">
            <table class="table table-hover align-middle border">
                <thead class="table-light">
                    <tr>
                        <th scope="col">المريض</th>
                        <th scope="col">فريق الإحالة</th>
                        <th scope="col">الحالة</th>
                        <th scope="col">تاريخ الإحالة</th>
                        <th scope="col" class="text-center">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $req)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $req->WC_REQ_PAT_NAME_SNAPSHOT }}</div>
                                <div class="small text-muted">الهوية: <bdi>{{ $req->WC_REQ_PAT_ID_SNAPSHOT }}</bdi></div>
                                <div class="small text-muted">الطلب: <bdi>REQ-{{ substr($req->WC_REQ_CODE, 0, 8) }}</bdi></div>
                            </td>
                            <td>
                                <div>{{ $req->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</div>
                                <div class="small text-muted">المراجع: {{ $req->reviewer?->PER_EMP_FULL_NAME ?? 'لم تبدأ الزيارة' }}</div>
                            </td>
                            <td><x-status-badge :status="$req->WC_REQ_STATUS" /></td>
                            <td>{{ \Carbon\Carbon::parse($req->WC_REQ_CREATED_AT)->format('Y-m-d H:i') }}</td>
                            <td class="text-center">
                                <a href="{{ route('requests.show', $req->WC_REQ_CODE) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye-fill me-1"></i>فتح الإحالة
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
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
