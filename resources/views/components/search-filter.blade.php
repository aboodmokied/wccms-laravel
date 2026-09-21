@props(['placeholder' => null, 'action' => '#', 'method' => 'GET'])

<form method="{{ $method }}" action="{{ $action }}" {{ $attributes->merge(['class' => 'd-flex align-items-center gap-2 mb-4']) }}>
    <div class="input-group">
        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
        <input type="text" name="search" class="form-control border-start-0" placeholder="{{ $placeholder ?? __('ui.search_placeholder') }}" value="{{ request('search') }}">
    </div>
    <button type="submit" class="btn btn-primary px-4">
        {{ __('ui.buttons.search') }}
    </button>
</form>
