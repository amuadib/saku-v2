<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Saku v2') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
    <style>
        body { background-color: #f8fafc; }
        .pb-safe-bottom { padding-bottom: env(safe-area-inset-bottom); }
    </style>
</head>
<body class="antialiased min-h-screen text-gray-800">

    <main class="w-full max-w-md mx-auto relative min-h-screen bg-[#f8f9fc] overflow-x-hidden shadow-2xl pb-24">
        {{ $slot }}

        <!-- Bottom Navigation -->
        <div class="fixed bottom-0 w-full max-w-md mx-auto bg-white border-t border-gray-100 flex justify-between items-center px-6 py-3 pb-safe-bottom z-50 rounded-t-3xl shadow-[0_-4px_20px_-10px_rgba(0,0,0,0.1)]">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('dashboard') ? 'text-emerald-600' : 'text-gray-400' }}">
                <div class="{{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-600 rounded-full px-5 py-1' : 'px-5 py-1 text-gray-400' }} flex items-center justify-center transition-colors">
                    <flux:icon name="home" class="size-6" variant="{{ request()->routeIs('dashboard') ? 'solid' : 'outline' }}" />
                </div>
                <span class="text-[10px] font-semibold {{ request()->routeIs('dashboard') ? 'text-emerald-600' : 'text-gray-400' }}">Beranda</span>
            </a>
            
            <a href="{{ route('siswa.informasi') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('siswa.informasi') ? 'text-emerald-600' : 'text-gray-400' }}">
                <div class="{{ request()->routeIs('siswa.informasi') ? 'bg-emerald-50 text-emerald-600 rounded-full px-5 py-1' : 'px-5 py-1 text-gray-400' }} flex items-center justify-center transition-colors">
                    <flux:icon name="chart-bar" class="size-6" variant="{{ request()->routeIs('siswa.informasi') ? 'solid' : 'outline' }}" />
                </div>
                <span class="text-[10px] font-semibold {{ request()->routeIs('siswa.informasi') ? 'text-emerald-600' : 'text-gray-400' }}">Informasi</span>
            </a>

            <a href="#" class="flex flex-col items-center gap-1 text-gray-400">
                <div class="px-5 py-1 flex items-center justify-center">
                    <flux:icon name="chat-bubble-oval-left-ellipsis" class="size-6" variant="outline" />
                </div>
                <span class="text-[10px] font-semibold text-gray-400">Chat</span>
            </a>

            <a href="{{ route('siswa.profil') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('siswa.profil') ? 'text-emerald-600' : 'text-gray-400' }}">
                <div class="{{ request()->routeIs('siswa.profil') ? 'bg-emerald-50 text-emerald-600 rounded-full px-5 py-1' : 'px-5 py-1 text-gray-400' }} flex items-center justify-center transition-colors">
                    <flux:icon name="user" class="size-6" variant="{{ request()->routeIs('siswa.profil') ? 'solid' : 'outline' }}" />
                </div>
                <span class="text-[10px] font-semibold {{ request()->routeIs('siswa.profil') ? 'text-emerald-600' : 'text-gray-400' }}">Profil</span>
            </a>
        </div>
    </main>

    @fluxScripts
</body>
</html>
