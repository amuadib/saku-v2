@props([
    'title' => '',
    'value' => '',
    'icon' => null,
    'color' => 'indigo',
    'description' => null,
    'progress' => null,
    'progressLabel' => null,
    'progressSub' => null,
])

@php
$colorStyles = [
    'indigo' => [
        'bg' => 'bg-indigo-50 dark:bg-indigo-950/60',
        'text' => 'text-indigo-600 dark:text-indigo-400',
        'val' => 'text-zinc-900 dark:text-zinc-100',
        'bar' => 'bg-indigo-500',
    ],
    'emerald' => [
        'bg' => 'bg-emerald-50 dark:bg-emerald-950/60',
        'text' => 'text-emerald-600 dark:text-emerald-400',
        'val' => 'text-emerald-600 dark:text-emerald-400',
        'bar' => 'bg-emerald-500',
    ],
    'rose' => [
        'bg' => 'bg-rose-50 dark:bg-rose-950/60',
        'text' => 'text-rose-600 dark:text-rose-400',
        'val' => 'text-rose-600 dark:text-rose-400',
        'bar' => 'bg-rose-600',
    ],
    'amber' => [
        'bg' => 'bg-amber-50 dark:bg-amber-950/60',
        'text' => 'text-amber-600 dark:text-amber-400',
        'val' => 'text-amber-600 dark:text-amber-400',
        'bar' => 'bg-amber-500',
    ],
    'zinc' => [
        'bg' => 'bg-zinc-100 dark:bg-zinc-800',
        'text' => 'text-zinc-600 dark:text-zinc-300',
        'val' => 'text-zinc-900 dark:text-zinc-100',
        'bar' => 'bg-zinc-500',
    ],
    'sky' => [
        'bg' => 'bg-sky-50 dark:bg-sky-950/60',
        'text' => 'text-sky-600 dark:text-sky-400',
        'val' => 'text-sky-600 dark:text-sky-400',
        'bar' => 'bg-sky-500',
    ],
];

$scheme = $colorStyles[$color] ?? $colorStyles['indigo'];
@endphp

<div {{ $attributes->merge(['class' => 'min-w-[200px] sm:min-w-0 flex-1 bg-white dark:bg-zinc-900/70 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs snap-start backdrop-blur-xl']) }}>
    <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $title }}</span>
        @if ($icon)
            <div class="size-8 rounded-xl {{ $scheme['bg'] }} {{ $scheme['text'] }} flex items-center justify-center shrink-0">
                <flux:icon name="{{ $icon }}" class="size-4" />
            </div>
        @endif
    </div>

    <div class="text-lg sm:text-xl font-bold {{ $scheme['val'] }} truncate">
        {{ $value }}
    </div>

    @if ($progress !== null)
        <div class="mt-2 w-full h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
            <div class="h-full {{ $scheme['bar'] }} rounded-full transition-all duration-500" style="width: {{ min(100, max(0, (float) $progress)) }}%"></div>
        </div>
        @if ($progressLabel || $progressSub)
            <div class="mt-1 flex items-center justify-between text-[11px] text-zinc-500 dark:text-zinc-400">
                @if ($progressLabel) <span>{{ $progressLabel }}</span> @endif
                @if ($progressSub) <span>{{ $progressSub }}</span> @endif
            </div>
        @endif
    @elseif ($description)
        <p class="mt-1 text-[11px] text-zinc-500 dark:text-zinc-400">
            {{ $description }}
        </p>
    @endif

    {{ $slot }}
</div>
