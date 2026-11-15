@props([
    'active' => false,
    'color' => 'indigo',
])

@php
$activeStyles = match ($color) {
    'emerald' => 'bg-emerald-600 text-white shadow-xs',
    'rose'    => 'bg-rose-600 text-white shadow-xs',
    'amber'   => 'bg-amber-600 text-white shadow-xs',
    'zinc'    => 'bg-zinc-800 text-white shadow-xs dark:bg-zinc-200 dark:text-zinc-900',
    default   => 'bg-indigo-600 text-white shadow-xs',
};

$inactiveStyles = 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700';
@endphp

<button
    type="button"
    {{ $attributes->merge(['class' => 'px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-colors ' . ($active ? $activeStyles : $inactiveStyles)]) }}
>
    {{ $slot }}
</button>
