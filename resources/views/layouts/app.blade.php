<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        @if(session()->has('impersonate_by'))
            <livewire:admin.impersonate-ui />
        @endif
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
