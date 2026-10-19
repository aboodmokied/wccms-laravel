{{-- Horizontal bars. $items: list of ['label' => string, 'count' => int]. The number is always printed. --}}
@php($max = max([1, ...array_column($items, 'count')]))

@foreach($items as $item)
    <div class="m8-report-bar-row">
        <span class="m8-report-bar-label" title="{{ $item['label'] }}">{{ $item['label'] }}</span>
        <div class="m8-report-bar-track" role="presentation">
            <div class="m8-report-bar-fill" style="width: {{ round($item['count'] / $max * 100, 1) }}%"></div>
        </div>
        <span class="m8-report-bar-value">{{ $item['count'] }}</span>
    </div>
@endforeach
