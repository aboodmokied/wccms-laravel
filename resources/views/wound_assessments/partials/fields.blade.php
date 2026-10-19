@php
    $oldPath = str_replace(['[', ']'], ['.', ''], $fieldPrefix);
    $oldValue = fn (string $field, mixed $default = null) => data_get(old(), "{$oldPath}.{$field}", $default);
    $choiceList = fn (string $category) => $assessmentChoices->get($category, collect());
    $term = fn (string $field): string => __("clinical.fields.{$field}");
@endphp

<div class="wound-assessment-fields" data-wound-assessment>
    <div class="row g-3">
        @if($initial ?? false)
            <div class="col-md-6">
                <label class="form-label fw-semibold"><bdi>{{ $term('wound_type') }}</bdi> <span class="text-danger">*</span></label>
                <select class="form-select" name="{{ $fieldPrefix }}[wound_type_code]" required>
                    <option value="">اختر</option>
                    @foreach($woundTypes as $item)<option value="{{ $item->WC_WTYPE_CODE }}" @selected($oldValue('wound_type_code') === $item->WC_WTYPE_CODE)>{{ $item->WC_WTYPE_NAME }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold"><bdi>{{ $term('diagnosis') }}</bdi> <span class="text-danger">*</span></label>
                <select class="form-select" name="{{ $fieldPrefix }}[diagnosis_code]" required>
                    <option value="">اختر</option>
                    @foreach($diagnoses as $item)<option value="{{ $item->WC_DIAG_CODE }}" @selected($oldValue('diagnosis_code') === $item->WC_DIAG_CODE)>{{ $item->WC_DIAG_NAME }}</option>@endforeach
                </select>
            </div>
        @endif

        @foreach(['WOUND_STATUS' => 'wound_status', 'EXUDATE_AMOUNT' => 'exudate_amount', 'EXUDATE_TYPE' => 'exudate_type', 'ODOR' => 'odor', 'PAIN_SEVERITY' => 'pain_severity'] as $category => $termKey)
            @php($field = strtolower($category).'_choice_code')
            <div class="col-md-4">
                <label class="form-label fw-semibold"><bdi>{{ $term($termKey) }}</bdi> {!! $category === 'EXUDATE_TYPE' ? '' : '<span class="text-danger">*</span>' !!}</label>
                <select class="form-select" name="{{ $fieldPrefix }}[{{ $field }}]" data-category="{{ $category }}" @required($category !== 'EXUDATE_TYPE')>
                    <option value="">اختر</option>
                    @foreach($choiceList($category) as $item)<option value="{{ $item->WC_ACH_CODE }}" data-key="{{ $item->WC_ACH_KEY }}" @selected($oldValue($field) === $item->WC_ACH_CODE)>{{ $item->displayLabel() }}</option>@endforeach
                </select>
            </div>
        @endforeach

        <div class="col-12">
            <label class="form-label fw-semibold d-block"><bdi>{{ $term('wound_bed') }}</bdi> <span class="text-danger">*</span></label>
            @foreach($choiceList('WOUND_BED') as $item)
                <label class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="{{ $fieldPrefix }}[wound_bed_choice_codes][]" value="{{ $item->WC_ACH_CODE }}" @checked(in_array($item->WC_ACH_CODE, $oldValue('wound_bed_choice_codes', []), true))><bdi class="form-check-label">{{ $item->displayLabel() }}</bdi></label>
            @endforeach
        </div>

        <div class="col-12">
            <label class="form-label fw-semibold d-block"><bdi>{{ $term('wound_margin') }}</bdi> @if($initial ?? false)<span class="text-danger">*</span>@endif</label>
            @foreach($choiceList('WOUND_MARGIN') as $item)
                <label class="form-check form-check-inline"><input class="form-check-input" type="checkbox" data-margin-key="{{ $item->WC_ACH_KEY }}" name="{{ $fieldPrefix }}[wound_margin_choice_codes][]" value="{{ $item->WC_ACH_CODE }}" @checked(in_array($item->WC_ACH_CODE, $oldValue('wound_margin_choice_codes', []), true))><bdi class="form-check-label">{{ $item->displayLabel() }}</bdi></label>
            @endforeach
        </div>

        @foreach(['length_mm', 'width_mm', 'depth_mm'] as $field)
            <div class="col-4"><label class="form-label fw-semibold"><bdi>{{ $term($field) }} (mm)</bdi></label><input class="form-control" type="number" min="{{ $field === 'depth_mm' ? 0 : 0.01 }}" max="1000" step="0.01" name="{{ $fieldPrefix }}[{{ $field }}]" value="{{ $oldValue($field) }}" @required($initial ?? false)></div>
        @endforeach

        @if($initial ?? false)
            @foreach(['FEVER' => 'fever', 'NUTRITION' => 'nutrition', 'LOCATION' => 'location', 'LATERALITY' => 'laterality'] as $category => $termKey)
                @php($field = strtolower($category).'_choice_code')
                <div class="col-md-3"><label class="form-label fw-semibold"><bdi>{{ $term($termKey) }}</bdi></label><select class="form-select" name="{{ $fieldPrefix }}[{{ $field }}]" @required($category !== 'LATERALITY')><option value="">اختر</option>@foreach($choiceList($category) as $item)<option value="{{ $item->WC_ACH_CODE }}" data-key="{{ $item->WC_ACH_KEY }}" @selected($oldValue($field) === $item->WC_ACH_CODE)>{{ $item->displayLabel() }}</option>@endforeach</select></div>
            @endforeach
            <div class="col-md-6"><label class="form-label fw-semibold"><bdi>{{ $term('location_notes') }}</bdi></label><input class="form-control" name="{{ $fieldPrefix }}[location_notes]" value="{{ $oldValue('location_notes') }}"></div>
            <div class="col-md-3"><label class="form-label fw-semibold d-block"><bdi>{{ $term('swab_taken') }}</bdi></label><input type="hidden" name="{{ $fieldPrefix }}[swab_taken]" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="{{ $fieldPrefix }}[swab_taken]" value="1" @checked($oldValue('swab_taken'))><span class="form-check-label">نعم</span></label></div>
        @endif

        <div class="col-md-3"><label class="form-label fw-semibold d-block"><bdi>{{ $term('pain_at_dressing') }}</bdi></label><input type="hidden" name="{{ $fieldPrefix }}[pain_at_dressing]" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="{{ $fieldPrefix }}[pain_at_dressing]" value="1" @checked($oldValue('pain_at_dressing'))><span class="form-check-label">نعم</span></label></div>
        <div class="col-md-4"><label class="form-label fw-semibold"><bdi>{{ $term('dressing') }}</bdi></label><select class="form-select" name="{{ $fieldPrefix }}[dressing_code]"><option value="">بدون</option>@foreach($dressings as $item)<option value="{{ $item->WC_DRESS_CODE }}" @selected($oldValue('dressing_code') === $item->WC_DRESS_CODE)>{{ $item->WC_DRESS_NAME }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label fw-semibold"><bdi>{{ $term('medication') }}</bdi></label><select class="form-select" name="{{ $fieldPrefix }}[medication_code]"><option value="">بدون</option>@foreach($medications as $item)<option value="{{ $item->WC_MED_CODE }}" @selected($oldValue('medication_code') === $item->WC_MED_CODE)>{{ $item->WC_MED_NAME }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label fw-semibold"><bdi>{{ $term('procedure') }}</bdi></label><select class="form-select" name="{{ $fieldPrefix }}[procedure_code]"><option value="">بدون</option>@foreach($procedures as $item)<option value="{{ $item->WC_PROC_CODE }}" @selected($oldValue('procedure_code') === $item->WC_PROC_CODE)>{{ $item->WC_PROC_NAME }}</option>@endforeach</select></div>
        <div class="col-12"><label class="form-label fw-semibold"><bdi>{{ $term('treatment_notes') }}</bdi></label><textarea class="form-control" rows="2" name="{{ $fieldPrefix }}[treatment_notes]">{{ $oldValue('treatment_notes') }}</textarea><div class="form-text">يجب توثيق واحد على الأقل من: <bdi>{{ $term('dressing') }}</bdi>، <bdi>{{ $term('medication') }}</bdi>، <bdi>{{ $term('procedure') }}</bdi>، <bdi>{{ $term('treatment_notes') }}</bdi>.</div></div>
        <div class="col-12"><label class="form-label fw-semibold">صور الجرح</label><input class="form-control" type="file" multiple accept="image/jpeg,image/png,image/webp" name="{{ $fieldPrefix }}[wound_photos][]"></div>
    </div>
</div>
