@extends('layouts.app')

@section('title', 'تفاصيل طلب الإحالة وتقييم زيارة القسم')

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

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <x-card title="تفاصيل طلب الإحالة وتقييم الحالة" icon="bi-file-earmark-medical-fill" subtitle="معاينة بيانات المريض وإجراء زيارة التقييم بالقسم الإيوائي">

                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <div>
                        <span class="text-muted small d-block">كود الطلب</span>
                        <h4 class="fw-bold text-primary mb-0">REQ-{{ substr($request->WC_REQ_CODE, 0, 8) }}</h4>
                    </div>
                    <div>
                        <x-status-badge :status="$request->WC_REQ_STATUS" class="fs-6" />
                    </div>
                </div>

                <!-- Patient & Referral Info Summary -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card h-100 bg-light border-0">
                            <div class="card-body">
                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-fill me-2"></i>بيانات المريض المحول</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="text-muted" style="width: 140px;">اسم المريض:</td>
                                        <td class="fw-bold">{{ $request->WC_REQ_PAT_NAME_SNAPSHOT }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">رقم الهوية:</td>
                                        <td><code>{{ $request->WC_REQ_PAT_ID_SNAPSHOT }}</code></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">رقم الهاتف:</td>
                                        <td>{{ $request->WC_REQ_PAT_PHONE_SNAPSHOT ?? 'غير مدخل' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">تاريخ الطلب:</td>
                                        <td>{{ \Carbon\Carbon::parse($request->WC_REQ_CREATED_AT)->format('Y-m-d H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 bg-light border-0">
                            <div class="card-body">
                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-hospital-fill me-2"></i>معلومات القسم والمستفيدين</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="text-muted" style="width: 150px;">الممرض المحول:</td>
                                        <td class="fw-bold">{{ $request->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">مدخل البيانات:</td>
                                        <td>{{ $request->entryUser?->PER_USER_USERNAME ?? 'النظام' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">ممرض عيادة الجروح:</td>
                                        <td class="fw-bold text-primary">{{ $request->reviewer?->PER_EMP_FULL_NAME ?? 'قيد التعيين عند بدء الزيارة' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="card bg-white border mb-4">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-chat-left-text-fill me-2 text-primary"></i>سبب الإحالة والملاحظات السريرية
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $request->WC_REQ_NOTES ?: 'لا توجد ملاحظات إضافية.' }}</p>
                    </div>
                </div>

                <!-- Workflow Actions for Clinic Nurses -->
                <div class="card border-primary mb-3">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-gear-fill me-2"></i>إجراءات وتقييم عيادة العناية بالجروح
                    </div>
                    <div class="card-body">
                        @if ($request->WC_REQ_STATUS === 'PENDING')
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-info-circle-fill me-2"></i>الطلب حالياً <strong>قيد الانتظار</strong>. يرجى اختيار ممرض العيادة وبدء زيارة المريض بالقسم.
                            </div>
                            <form action="{{ route('requests.visit', $request->WC_REQ_CODE) }}" method="POST" class="row g-3 align-items-end">
                                @csrf
                                <div class="col-md-8">
                                    <label for="clinic_nurse_code" class="form-label fw-semibold">ممرض عيادة الجروح المكلف بالزيارة</label>
                                    <select name="clinic_nurse_code" class="form-select" required>
                                        <option value="">-- اختر ممرض العيادة --</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->PER_EMP_CODE }}">
                                                {{ $employee->PER_EMP_FULL_NAME }} ({{ $employee->PER_EMP_JOB_TITLE ?? 'ممرض' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-info text-white w-100 fw-bold">
                                        <i class="bi bi-person-walking me-1"></i> بدء زيارة القسم
                                    </button>
                                </div>
                            </form>
                        @elseif (in_array($request->WC_REQ_STATUS, ['PENDING', 'VISIT_IN_PROGRESS']))
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle-fill me-2"></i>الزيارة <strong>قيد التنفيذ بالقسم</strong>. يمكنك قبول المريض وتنسيق التسجيل أو تسجيلة كغير مؤهل.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <form action="{{ route('requests.accept', $request->WC_REQ_CODE) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold" onclick="return confirm('هل أنت تأكد من قبول المريض وإنشاء الملف الطبي بالعيادة؟')">
                                            <i class="bi bi-check-circle-fill me-1"></i> قبول المريض وإنشاء التسجيل
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-danger w-100 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#ineligibleModal">
                                        <i class="bi bi-x-circle-fill me-1"></i> عدم الأهلية لتسجيل العيادة
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-secondary mb-0">
                                <i class="bi bi-lock-fill me-2"></i>تم البت في هذا الطلب سابقاً وحالته الحالية هي: <strong><x-status-badge :status="$request->WC_REQ_STATUS" /></strong>.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-start">
                    <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right me-1"></i> العودة لقائمة الطلبات
                    </a>
                </div>
            </x-card>
        </div>
    </div>
</div>

<!-- Modal for Ineligible Reason -->
<div class="modal fade" id="ineligibleModal" tabindex="-1" aria-labelledby="ineligibleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('requests.ineligible', $request->WC_REQ_CODE) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="ineligibleModalLabel"><i class="bi bi-x-circle-fill me-2"></i>تسجيل عدم الأهلية للتحويل</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reason" class="form-label fw-semibold">سبب عدم الأهلية للعيادة <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reason" rows="4" class="form-control" placeholder="يرجى كتابة التقييم السريري وسبب عدم الأهلية بالتفصيل..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">تأكيد عدم الأهلية</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
