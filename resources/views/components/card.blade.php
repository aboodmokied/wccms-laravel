@props(['title' => null, 'icon' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm rounded-3 mb-4']) }}>
    @if($title || $icon)
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                @if($icon)
                    <i class="{{ $icon }} text-primary fs-5"></i>
                @endif
                <div>
                    <h5 class="card-title fw-bold mb-0 text-dark">{{ $title }}</h5>
                    @if($subtitle)
                        <small class="text-muted">{{ $subtitle }}</small>
                    @endif
                </div>
            </div>
            @if(isset($actions))
                <div>{{ $actions }}</div>
            @endif
        </div>
    @endif
    
    <div class="card-body">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="card-footer bg-white border-top py-2">
            {{ $footer }}
        </div>
    @endif
</div>
