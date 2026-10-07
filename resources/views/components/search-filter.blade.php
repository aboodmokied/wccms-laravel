@props(['placeholder' => null, 'name' => 'search', 'value' => null, 'label' => 'البحث'])

<div {{ $attributes->merge(['class' => 'input-group']) }}>
    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search" aria-hidden="true"></i></span>
    <input type="search" name="{{ $name }}" class="form-control border-start-0" aria-label="{{ $label }}" placeholder="{{ $placeholder ?? __('ui.search_placeholder') }}" value="{{ $value ?? request($name) }}">
</div>
