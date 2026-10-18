@php
    $oldPath = str_replace(['[', ']'], ['.', ''], $fieldPrefix);
    $oldValue = fn (string $field, mixed $default = null) => data_get(old(), "{$oldPath}.{$field}", $default);
    $choiceList = fn (string $category) => $assessmentChoices->get($category, collect());
@endphp

<div class="wound-assessment-fields" data-wound-assessment>
    <div class="row g-3">
        @if($initial ?? false)
            <div class="col-md-6">
                <label class="form-label fw-semibold">نوع الجرح <span class="text-danger">*</span></label>
                <select class="form-select" name="{{ $fieldPrefix }}[wound_type_code]" required>
                    <option value="">اختر نوع الجرح</option>
                    @foreach($woundTypes as $item)<option value="{{ $item->WC_WTYPE_CODE }}" @selected($oldValue('wound_type_code') === $item->WC_WTYPE_CODE)>{{ $item->WC_WTYPE_NAME }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">التشخيص <span class="text-danger">*</span></label>
                <select class="form-select" name="{{ $fieldPrefix }}[diagnosis_code]" required>
                    <option value="">اختر التشخيص</option>
                    @foreach($diagnoses as $item)<option value="{{ $item->WC_DIAG_CODE }}" @selected($oldValue('diagnosis_code') === $item->WC_DIAG_CODE)>{{ $item->WC_DIAG_NAME }}</option>@endforeach
                </select>
            </div>
        @endif

        @foreach(['WOUND_STATUS' => 'حالة الجرح', 'EXUDATE_AMOUNT' => 'كمية الإفرازات', 'EXUDATE_TYPE' => 'نوع الإفرازات', 'ODOR' => 'الرائحة', 'PAIN_SEVERITY' => 'شدة الألم'] as $category => $label)
            @php($field = strtolower($category).'_choice_code')
            <div class="col-md-4">
                <label class="form-label fw-semibold">{{ $label }} {!! $category === 'EXUDATE_TYPE' ? '' : '<span class="text-danger">*</span>' !!}</label>
                <select class="form-select" name="{{ $fieldPrefix }}[{{ $field }}]" data-category="{{ $category }}" @required($category !== 'EXUDATE_TYPE')>
                    <option value="">اختر</option>
                    @foreach($choiceList($category) as $item)<option value="{{ $item->WC_ACH_CODE }}" data-key="{{ $item->WC_ACH_KEY }}" @selected($oldValue($field) === $item->WC_ACH_CODE)>{{ $item->WC_ACH_LABEL_AR }}</option>@endforeach
                </select>
            </div>
        @endforeach

        <div class="col-12">
            <label class="form-label fw-semibold d-block">سرير الجرح <span class="text-danger">*</span></label>
            @foreach($choiceList('WOUND_BED') as $item)
                <label class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="{{ $fieldPrefix }}[wound_bed_choice_codes][]" value="{{ $item->WC_ACH_CODE }}" @checked(in_array($item->WC_ACH_CODE, $oldValue('wound_bed_choice_codes', []), true))><span class="form-check-label">{{ $item->WC_ACH_LABEL_AR }}</span></label>
            @endforeach
        </div>

        <div class="col-12">
            <label class="form-label fw-semibold d-block">حواف الجرح @if($initial ?? false)<span class="text-danger">*</span>@endif</label>
            @foreach($choiceList('WOUND_MARGIN') as $item)
                <label class="form-check form-check-inline"><input class="form-check-input" type="checkbox" data-margin-key="{{ $item->WC_ACH_KEY }}" name="{{ $fieldPrefix }}[wound_margin_choice_codes][]" value="{{ $item->WC_ACH_CODE }}" @checked(in_array($item->WC_ACH_CODE, $oldValue('wound_margin_choice_codes', []), true))><span class="form-check-label">{{ $item->WC_ACH_LABEL_AR }}</span></label>
            @endforeach
        </div>

        @foreach(['length_mm' => 'الطول', 'width_mm' => 'العرض', 'depth_mm' => 'العمق'] as $field => $label)
            <div class="col-4"><label class="form-label fw-semibold">{{ $label }} (mm)</label><input class="form-control" type="number" min="{{ $field === 'depth_mm' ? 0 : 0.01 }}" max="1000" step="0.01" name="{{ $fieldPrefix }}[{{ $field }}]" value="{{ $oldValue($field) }}" @required($initial ?? false)></div>
        @endforeach

        @if($initial ?? false)
            @foreach(['FEVER' => 'الحرارة', 'NUTRITION' => 'التغذية', 'LOCATION' => 'الموقع', 'LATERALITY' => 'الجهة'] as $category => $label)
                @php($field = strtolower($category).'_choice_code')
                <div class="col-md-3"><label class="form-label fw-semibold">{{ $label }}</label><select class="form-select" name="{{ $fieldPrefix }}[{{ $field }}]" @required($category !== 'LATERALITY')><option value="">اختر</option>@foreach($choiceList($category) as $item)<option value="{{ $item->WC_ACH_CODE }}" data-key="{{ $item->WC_ACH_KEY }}" @selected($oldValue($field) === $item->WC_ACH_CODE)>{{ $item->WC_ACH_LABEL_AR }}</option>@endforeach</select></div>
            @endforeach
            <div class="col-md-6"><label class="form-label fw-semibold">تفصيل الموقع</label><input class="form-control" name="{{ $fieldPrefix }}[location_notes]" value="{{ $oldValue('location_notes') }}"></div>
            <div class="col-md-3"><label class="form-label fw-semibold d-block">تم أخذ مسحة؟</label><input type="hidden" name="{{ $fieldPrefix }}[swab_taken]" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="{{ $fieldPrefix }}[swab_taken]" value="1" @checked($oldValue('swab_taken'))><span class="form-check-label">نعم</span></label></div>
        @endif

        <div class="col-md-3"><label class="form-label fw-semibold d-block">ألم عند تغيير الضماد؟</label><input type="hidden" name="{{ $fieldPrefix }}[pain_at_dressing]" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="{{ $fieldPrefix }}[pain_at_dressing]" value="1" @checked($oldValue('pain_at_dressing'))><span class="form-check-label">نعم</span></label></div>
        <div class="col-md-4"><label class="form-label fw-semibold">الضماد</label><select class="form-select" name="{{ $fieldPrefix }}[dressing_code]"><option value="">بدون</option>@foreach($dressings as $item)<option value="{{ $item->WC_DRESS_CODE }}" @selected($oldValue('dressing_code') === $item->WC_DRESS_CODE)>{{ $item->WC_DRESS_NAME }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label fw-semibold">الدواء</label><select class="form-select" name="{{ $fieldPrefix }}[medication_code]"><option value="">بدون</option>@foreach($medications as $item)<option value="{{ $item->WC_MED_CODE }}" @selected($oldValue('medication_code') === $item->WC_MED_CODE)>{{ $item->WC_MED_NAME }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label fw-semibold">الإجراء</label><select class="form-select" name="{{ $fieldPrefix }}[procedure_code]"><option value="">بدون</option>@foreach($procedures as $item)<option value="{{ $item->WC_PROC_CODE }}" @selected($oldValue('procedure_code') === $item->WC_PROC_CODE)>{{ $item->WC_PROC_NAME }}</option>@endforeach</select></div>
        <div class="col-12"><label class="form-label fw-semibold">خطة/ملاحظات العلاج</label><textarea class="form-control" rows="2" name="{{ $fieldPrefix }}[treatment_notes]">{{ $oldValue('treatment_notes') }}</textarea><div class="form-text">يجب توثيق ضماد أو دواء أو إجراء أو ملاحظة علاج واحدة على الأقل.</div></div>
        <div class="col-12"><label class="form-label fw-semibold">صور الجرح</label><input class="form-control" type="file" multiple accept="image/jpeg,image/png,image/webp" name="{{ $fieldPrefix }}[wound_photos][]"></div>
    </div>
</div>
