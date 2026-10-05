@props(['title' => null])
@php
    $routeName = request()->route()?->getName() ?? '';
    $segments = explode('.', $routeName);
    
    $defaultTitle = 'Saku v2';
    if ($routeName === 'dashboard' || $routeName === 'admin.dashboard') {
        $defaultTitle = 'Dashboard';
    } elseif (count($segments) >= 2 && $segments[0] === 'admin') {
        $defaultTitle = Str::headline($segments[1]);
    } elseif (count($segments) >= 1 && $segments[0] !== '') {
        $defaultTitle = Str::headline($segments[0]);
    }

    $pageTitle = $title ?? (isset($header) && is_string($header) && !str_contains($header, '<') ? $header : $defaultTitle);
@endphp
<x-layouts::app :title="$pageTitle">
    @if (isset($header))
        <div class="mb-6">
            @if($header instanceof \Illuminate\View\ComponentSlot || (is_string($header) && str_contains($header, '<')))
                {{ $header }}
            @else
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $header }}
                </h2>
            @endif
        </div>
    @endif

    {{ $slot }}
</x-layouts::app>
