@props(['headers' => []])

<div class="table-responsive">
    <table {{ $attributes->merge(['class' => 'table table-hover align-middle mb-0']) }}>
        @if(count($headers) > 0)
            <thead class="table-light">
                <tr>
                    @foreach($headers as $header)
                        <th scope="col" class="fw-bold text-secondary">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
