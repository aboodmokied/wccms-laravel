@extends('layouts.app')

@section('title', 'إحالة جديدة')

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="wccms-page-heading mb-1">إحالة جديدة</h1>
            <p class="text-muted mb-4">ابحث عن المريض أولاً، ثم أدخل بيانات الإحالة وأرسلها.</p>
            <x-card>

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>تنبيه:</strong> يرجى تصحيح الأخطاء التالية:
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- 1. Patient Search Section -->
                <div class="card bg-light border-0 mb-4">
                    <div class="card-body">
                        <h2 class="fs-5 fw-bold mb-3">1. اختيار المريض</h2>
                        <div class="input-group">
                            <input type="text" id="patientSearchQuery" class="form-control" placeholder="أدخل رقم الهوية أو اسم المريض هنا..." aria-label="البحث عن مريض">
                            <button class="btn btn-primary px-4" type="button" id="btnSearchPatient">
                                <i class="bi bi-search me-1"></i>بحث
                            </button>
                        </div>
                        <div id="searchSpinner" class="text-center py-3 d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري البحث...</span>
                            </div>
                            <span class="ms-2 text-muted">جاري البحث في الفهرس الطبي للمستشفى...</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Patient Details & Referral Form (Hidden until patient selected) -->
                <form action="{{ route('requests.store') }}" method="POST" id="referralForm">
                    @csrf
                    <input type="hidden" name="patient_code" id="inputPatientCode" value="{{ old('patient_code') }}">

                    <div id="patientResultCard" class="{{ old('patient_code') ? '' : 'd-none' }} mb-4">
                        <div class="alert alert-info border-info d-flex align-items-center mb-3" id="patientWarningAlert" style="display: none !important;">
                            <i class="bi bi-exclamation-circle-fill me-2 fs-4"></i>
                            <span id="patientWarningText"></span>
                        </div>

                        <div class="card border-primary border-opacity-25 shadow-sm mb-4">
                            <div class="card-header bg-primary bg-opacity-10 text-primary fw-bold">
                                <i class="bi bi-person-vcard me-2"></i>بيانات المريض والقبول الحالي
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label text-muted small">اسم المريض الكامل</label>
                                        <div class="fw-bold fs-6" id="dispPatientName">-</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-muted small">رقم الهوية الوطنية</label>
                                        <div class="fw-bold fs-6" id="dispPatientId">-</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-muted small">الجنس / تاريخ الميلاد</label>
                                        <div class="fw-bold fs-6" id="dispPatientGenderDob">-</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-muted small">القسم / القسم الإيوائي الحالي</label>
                                        <div class="fw-bold fs-6 text-primary" id="dispWardName">-</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-muted small">الطبيب المعالج</label>
                                        <div class="fw-bold fs-6" id="dispAttendingDoctor">-</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-muted small">تاريخ الدخول للمستشفى</label>
                                        <div class="fw-bold fs-6" id="dispAdmissionDate">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Referral Details -->
                        <h2 class="fs-5 fw-bold mb-3">2. بيانات الإحالة</h2>

                        @include('partials.staff_lists_notice')
                        <div class="mb-3">
                            <label for="attending_nurse_code" class="form-label fw-semibold">الممرض المحول / مسؤول القسم <span class="text-danger">*</span></label>
                            <select name="attending_nurse_code" id="attending_nurse_code" class="form-select @error('attending_nurse_code') is-invalid @enderror" required>
                                <option value="">-- اختر الممرض المحول --</option>
                                @foreach ($nurses as $employee)
                                    <option value="{{ $employee->EMP_SERIAL }}" {{ old('attending_nurse_code') == $employee->EMP_SERIAL ? 'selected' : '' }}>
                                        {{ $employee->full_name }} ({{ $employee->job_title ?? 'ممرض' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('attending_nurse_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">سبب الإحالة والملاحظات السريرية الأولية</label>
                            <textarea name="notes" id="notes" rows="4" class="form-control @error('notes') is-invalid @enderror" placeholder="اكتب تفاصيل وتقييم الجرح، سبب طلب الاستشارة، أو الملاحظات الطبية...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
                            <a href="{{ route('requests.my') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right me-1"></i>رجوع
                            </a>
                            <button type="submit" class="btn btn-primary px-4" id="btnSubmitReferral">
                                <i class="bi bi-check-circle-fill me-1"></i>إرسال الإحالة
                            </button>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnSearch = document.getElementById('btnSearchPatient');
    const inputQuery = document.getElementById('patientSearchQuery');
    const spinner = document.getElementById('searchSpinner');
    const resultCard = document.getElementById('patientResultCard');
    const inputPatientCode = document.getElementById('inputPatientCode');

    btnSearch.addEventListener('click', performSearch);
    inputQuery.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });

    function performSearch() {
        const q = inputQuery.value.trim();
        if (!q) {
            alert('يرجى إدخال رقم الهوية أو اسم المريض للبحث');
            return;
        }

        spinner.classList.remove('d-none');
        resultCard.classList.add('d-none');

        fetch(`{{ route('requests.search_patient') }}?query=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(data => {
                spinner.classList.add('d-none');
                if (data.found) {
                    inputPatientCode.value = data.patient.code;
                    document.getElementById('dispPatientName').innerText = data.patient.full_name;
                    document.getElementById('dispPatientId').innerText = data.patient.id_number;
                    document.getElementById('dispPatientGenderDob').innerText = `${data.patient.gender} (${data.patient.dob || 'غير محدد'})`;
                    document.getElementById('dispWardName').innerText = data.admission.ward_name;
                    document.getElementById('dispAttendingDoctor').innerText = data.admission.attending_doctor;
                    document.getElementById('dispAdmissionDate').innerText = data.admission.admission_date;

                    resultCard.classList.remove('d-none');

                    const btnSubmit = document.getElementById('btnSubmitReferral');
                    if (data.has_pending_request || data.has_active_registration) {
                        btnSubmit.disabled = true;
                        alert('تنبيه: هذا المريض لديه طلب تسجيل نشط أو تسجيل حالي في العيادة.');
                    } else {
                        btnSubmit.disabled = false;
                    }
                } else {
                    alert('لم يتم العثور على المريض.');
                }
            })
            .catch(err => {
                spinner.classList.add('d-none');
                alert('حدث خطأ أثناء الاتصال بالنظام: ' + err.message);
            });
    }
});
</script>
@endpush
@endsection
