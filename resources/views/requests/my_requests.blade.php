@extends('layouts.app')

@section('title', 'طلبات التحويل المحررة بواسطتي')

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

    <x-card title="طلبات التحويل الخاصة بي (طلبات القسم)" icon="bi-folder-check" subtitle="متابعة وإدارة طلبات التحويل المقدمة لعيادة العناية بالجروح">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="fw-bold mb-0 text-muted">إجمالي الطلبات المحررة: {{ $requests->total() }}</h6>
            <a href="{{ route('requests.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle-fill me-1"></i> تقديم طلب تحويل جديد
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle border">
                <thead class="table-light">
                    <tr>
                        <th>كود الطلب</th>
                        <th>اسم المريض</th>
                        <th>رقم الهوية</th>
                        <th>الممرض المحول</th>
                        <th>الحالة</th>
                        <th>تاريخ التقديم</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $req)
                        <tr>
                            <td class="fw-bold text-primary">REQ-{{ substr($req->WC_REQ_CODE, 0, 8) }}</td>
                            <td class="fw-semibold">{{ $req->WC_REQ_PAT_NAME_SNAPSHOT }}</td>
                            <td><code>{{ $req->WC_REQ_PAT_ID_SNAPSHOT }}</code></td>
                            <td>{{ $req->nurse?->full_name ?? 'غير محدد' }}</td>
                            <td><x-status-badge :status="$req->WC_REQ_STATUS" /></td>
                            <td>{{ \Carbon\Carbon::parse($req->WC_REQ_CREATED_AT)->format('Y-m-d H:i') }}</td>
                            <td class="text-center">
                                @if ($req->WC_REQ_STATUS === 'PENDING')
                                    <form action="{{ route('requests.cancel', $req->WC_REQ_CODE) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت تأكد من إلغاء طلب التحويل هذا؟')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x-circle me-1"></i> إلغاء الطلب
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-sm btn-light text-muted border" disabled>
                                        لا يمكن الإلغاء
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-folder-x fs-2 d-block mb-2"></i>
                                لم تقم بتقديم أي طلبات تحويل حتى الآن.
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
