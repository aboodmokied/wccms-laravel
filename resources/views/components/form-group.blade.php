@props(['label' => '', 'name' => '', 'type' => 'text', 'value' => '', 'required' => false])

<div {{ $attributes->merge(['class' => 'mb-3']) }}>
    @if($label)
        <label for="{{ $name }}" class="form-label fw-bold text-dark small">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif
    
    @if($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" class="form-control" rows="3" {{ $required ? 'required' : '' }}>{{ old($name, $value) }}</textarea>
    @else
        <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $value) }}" class="form-control" {{ $required ? 'required' : '' }}>
    @endif

    @error($name)
        <div class="invalid-feedback d-block small mt-1">{{ $message }}</div>
    @enderror
</div>
