@props([
    'desktopClass' => '',
    'mobileClass' => '',
])

<div {{ $attributes }}>
    <!-- Desktop View (md ke atas) -->
    <div class="hidden md:block overflow-x-auto {{ $desktopClass }}">
        {{ $desktop }}
    </div>

    <!-- Mobile View (di bawah md) -->
    <div class="block md:hidden space-y-3 {{ $mobileClass }}">
        {{ $mobile }}
    </div>
</div>
