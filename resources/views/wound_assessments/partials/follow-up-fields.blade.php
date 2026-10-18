<article class="card border-0 shadow-sm rounded-4 wound-followup-card" data-wound-card>
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <div><span class="badge text-bg-primary">جرح {{ $index + 1 }}</span> <strong>{{ $diagnosis->woundType?->WC_WTYPE_NAME }} — {{ $diagnosis->diagnosis?->WC_DIAG_NAME }}</strong></div>
        <span class="small text-muted" data-completion-label>غير مكتمل</span>
    </div>
    <div class="card-body">
        <input type="hidden" name="details[{{ $index }}][registration_diagnosis_code]" value="{{ $diagnosis->WC_RDIAG_CODE }}">
        @include('wound_assessments.partials.latest-comparison', ['assessment' => $diagnosis->latestAssessment])
        @include('wound_assessments.partials.fields', ['fieldPrefix' => "details[{$index}]", 'initial' => false])
        <div class="row g-3 mt-1">
            <div class="col-md-6"><label class="form-label fw-semibold">الملاحظة السريرية</label><textarea class="form-control" name="details[{{ $index }}][clinical_observation]" rows="2">{{ old("details.{$index}.clinical_observation") }}</textarea></div>
            <div class="col-md-6"><label class="form-label fw-semibold">التغيرات منذ آخر زيارة</label><textarea class="form-control" name="details[{{ $index }}][wound_changes]" rows="2">{{ old("details.{$index}.wound_changes") }}</textarea></div>
            <div class="col-md-6"><label class="form-label fw-semibold">نتيجة الجرح <span class="text-danger">*</span></label><select class="form-select" name="details[{{ $index }}][outcome_status]" required><option value="IMPROVED_FOLLOWUP">تحسن ويحتاج متابعة</option><option value="IMPROVED_DISCHARGE">تحسن واكتمال العلاج</option><option value="WORSENED">تدهور</option><option value="DECEASED">وفاة</option></select></div>
            <div class="col-md-6"><label class="form-label fw-semibold d-block">يحتاج متابعة؟</label><input type="hidden" name="details[{{ $index }}][follow_up_required]" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="details[{{ $index }}][follow_up_required]" value="1" checked><span class="form-check-label">نعم</span></label></div>
        </div>
    </div>
</article>
