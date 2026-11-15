@props([
    'cols' => 4,
])

@php
$gridCols = match ((int) $cols) {
    2 => 'sm:grid-cols-2',
    3 => 'sm:grid-cols-2 lg:grid-cols-3',
    default => 'sm:grid-cols-2 lg:grid-cols-4',
};
@endphp

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    <div class="flex sm:grid {{ $gridCols }} gap-3 sm:gap-4 overflow-x-auto pb-2 sm:pb-0 snap-x">
        {{ $slot }}
    </div>
</div>
