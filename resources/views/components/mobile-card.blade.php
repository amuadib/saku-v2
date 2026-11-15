@props([
    'title' => null,
    'subtitle' => null,
    'initials' => null,
    'icon' => null,
    'avatarColor' => 'emerald',
    'badge' => null,
    'badgeColor' => null,
])

@php
$colorGradients = [
    'emerald' => 'from-emerald-500 to-teal-600 text-white',
    'indigo'  => 'from-indigo-500 to-blue-600 text-white',
    'rose'    => 'from-rose-500 to-pink-600 text-white',
    'amber'   => 'from-amber-500 to-orange-600 text-white',
    'zinc'    => 'from-zinc-600 to-zinc-800 text-white',
    'sky'     => 'from-sky-500 to-cyan-600 text-white',
    'violet'  => 'from-violet-500 to-purple-600 text-white',
];

$gradient = $colorGradients[$avatarColor] ?? $colorGradients['emerald'];

if (! $initials && $title) {
    $initials = collect(explode(' ', trim((string) $title)))
        ->filter()
        ->map(fn($w) => mb_substr($w, 0, 1))
        ->take(2)
        ->join('');
}
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-zinc-800/80 rounded-2xl border border-zinc-200 dark:border-zinc-700/80 p-4 shadow-xs space-y-3']) }}>
    @if (isset($header))
        {{ $header }}
    @elseif ($title)
        <div class="flex items-start justify-between gap-2.5">
            <div class="flex items-center gap-2.5 min-w-0">
                @if ($icon)
                    <div class="size-9 rounded-xl bg-gradient-to-br {{ $gradient }} flex items-center justify-center shrink-0 shadow-xs">
                        <flux:icon name="{{ $icon }}" class="size-4 text-white" />
                    </div>
                @elseif ($initials)
                    <div class="size-9 rounded-xl bg-gradient-to-br {{ $gradient }} flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                        {{ strtoupper($initials) }}
                    </div>
                @endif

                <div class="min-w-0">
                    <h4 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 truncate leading-tight">
                        {{ $title }}
                    </h4>
                    @if ($subtitle)
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate mt-0.5">
                            {{ $subtitle }}
                        </div>
                    @endif
                </div>
            </div>

            @if ($badge)
                <div class="shrink-0">
                    @if ($badgeColor)
                        <flux:badge :color="$badgeColor" size="sm">{{ $badge }}</flux:badge>
                    @else
                        {{ $badge }}
                    @endif
                </div>
            @endif
        </div>
    @endif

    {{-- Body slot --}}
    @if ($slot->isNotEmpty())
        <div>
            {{ $slot }}
        </div>
    @endif

    {{-- Actions / Footer slot --}}
    @if (isset($actions))
        <div class="pt-2 border-t border-zinc-100 dark:border-zinc-700/80 flex items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
