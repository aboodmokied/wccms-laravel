@props(['placeholder' => null])

<div {{ $attributes->merge(['class' => 'input-group']) }}>
    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
    <input type="text" name="search" class="form-control border-start-0" placeholder="{{ $placeholder ?? __('ui.search_placeholder') }}" value="{{ request('search') }}">
</div>
