<div class="modal fade" id="addWoundModal" tabindex="-1" aria-labelledby="addWoundModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header bg-primary text-white"><h2 class="modal-title h5" id="addWoundModalLabel">إضافة جرح بتقييم أولي كامل</h2><button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal"></button></div>
        <form method="POST" enctype="multipart/form-data" action="{{ route('registrations.diagnoses.store', $registration->WC_REG_CODE) }}">@csrf
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    @include('partials.staff_lists_notice')
                    <div class="col-md-4"><label class="form-label fw-semibold">الممرض السريري</label><select class="form-select" name="clinical_nurse_code" required><option value="">اختر</option>@foreach($nurses as $item)<option value="{{ $item->EMP_SERIAL }}">{{ $item->full_name }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">عضو فريق الجروح</label><select class="form-select" name="wct_employee_code"><option value="">بدون</option>@foreach($woundCareTeam as $item)<option value="{{ $item->EMP_SERIAL }}">{{ $item->full_name }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">الطبيب</label><select class="form-select" name="doctor_employee_code"><option value="">بدون</option>@foreach($doctors as $item)<option value="{{ $item->EMP_SERIAL }}">{{ $item->full_name }}</option>@endforeach</select></div>
                </div>
                @include('wound_assessments.partials.fields', ['fieldPrefix' => 'assessment', 'initial' => true])
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">إلغاء</button><button class="btn btn-primary" type="submit">حفظ الجرح والتقييم</button></div>
        </form>
    </div></div>
</div>
