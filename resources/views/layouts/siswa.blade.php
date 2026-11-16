<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Saku v2') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
    <script>
        document.documentElement.classList.remove('dark');
        document.documentElement.classList.add('light');
    </script>
    <style>
        body { background-color: #f1f5f9; }
        .pb-safe-bottom { padding-bottom: env(safe-area-inset-bottom); }
        dialog::backdrop {
            background-color: rgba(15, 23, 42, 0.45) !important;
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
        }
    </style>
</head>
<body class="antialiased min-h-screen text-gray-800 bg-slate-100 dark:bg-zinc-950">

    @if(session()->has('impersonate_by'))
        <!-- Persistent Fixed Impersonation Top Bar (Always on top with z-[99999]) -->
        <div class="fixed top-0 left-0 right-0 z-[9999] bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 text-white shadow-lg border-b border-amber-400/50" style="z-index: 99999 !important;">
            <div class="max-w-4xl mx-auto px-4 py-2 flex items-center justify-between gap-3 text-xs sm:text-sm">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="relative flex size-2.5 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                        <span class="relative inline-flex rounded-full size-2.5 bg-white"></span>
                    </span>
                    <div class="truncate">
                        <span class="text-amber-100 text-[10px] sm:text-xs uppercase font-bold tracking-wider block">Mode Impersonasi Siswa</span>
                        <span class="font-bold text-white text-xs sm:text-sm truncate block leading-tight">
                            {{ auth()->user()->authable->nama ?? auth()->user()->username }}
                            @if(optional(auth()->user()->authable)->nis)
                                <span class="opacity-80 font-normal text-xs">(NIS: {{ auth()->user()->authable->nis }})</span>
                            @endif
                        </span>
                    </div>
                </div>
                <a href="{{ route('leave-impersonate') }}" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-amber-900 hover:bg-amber-50 rounded-lg font-bold text-xs sm:text-sm shadow-sm active:scale-95 transition-all cursor-pointer">
                    <flux:icon name="arrow-left-start-on-rectangle" class="size-4 text-amber-700" />
                    <span>Kembali ke Admin</span>
                </a>
            </div>
        </div>
    @endif

    @php
        $isChatRoute = request()->routeIs('siswa.chat') || request()->is('chat*');
        $unreadChatCount = \App\Models\ChatMessage::unreadCountForUser();
    @endphp

    <div class="min-h-screen flex flex-col justify-start items-center {{ session()->has('impersonate_by') ? 'pt-12 sm:pt-14 sm:pb-8' : '' }}">
        <main class="w-full max-w-md mx-auto relative bg-[#f8f9fc] shadow-2xl {{ session()->has('impersonate_by') ? 'sm:rounded-[36px] sm:border-[6px] sm:border-slate-800 sm:ring-1 sm:ring-black/10' : '' }} {{ $isChatRoute ? 'h-[100dvh] flex flex-col overflow-hidden pb-0' : 'min-h-screen pb-24 overflow-x-hidden' }}">
            {{ $slot }}

            <!-- Bottom Navigation -->
            <div class="{{ $isChatRoute ? 'shrink-0 z-40' : 'fixed bottom-0 left-0 right-0 z-50 rounded-t-3xl shadow-[0_-4px_20px_-10px_rgba(0,0,0,0.1)]' }} w-full max-w-md mx-auto bg-white border-t border-gray-100 flex justify-between items-center px-6 py-2.5 pb-safe-bottom">
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

                <a href="{{ route('siswa.chat') }}" wire:navigate class="flex flex-col items-center gap-1 {{ request()->routeIs('siswa.chat') ? 'text-emerald-600' : 'text-gray-400' }}">
                    <div class="relative {{ request()->routeIs('siswa.chat') ? 'bg-emerald-50 text-emerald-600 rounded-full px-5 py-1' : 'px-5 py-1 text-gray-400' }} flex items-center justify-center transition-colors">
                        <flux:icon name="chat-bubble-oval-left-ellipsis" class="size-6" variant="{{ request()->routeIs('siswa.chat') ? 'solid' : 'outline' }}" />
                        @if($unreadChatCount > 0)
                            <span class="absolute top-1 right-3 size-2.5 rounded-full bg-rose-500 ring-2 ring-white animate-pulse"></span>
                        @endif
                    </div>
                    <span class="text-[10px] font-semibold {{ request()->routeIs('siswa.chat') ? 'text-emerald-600' : 'text-gray-400' }}">Chat</span>
                </a>

                <a href="{{ route('siswa.profil') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('siswa.profil') ? 'text-emerald-600' : 'text-gray-400' }}">
                    <div class="{{ request()->routeIs('siswa.profil') ? 'bg-emerald-50 text-emerald-600 rounded-full px-5 py-1' : 'px-5 py-1 text-gray-400' }} flex items-center justify-center transition-colors">
                        <flux:icon name="user" class="size-6" variant="{{ request()->routeIs('siswa.profil') ? 'solid' : 'outline' }}" />
                    </div>
                    <span class="text-[10px] font-semibold {{ request()->routeIs('siswa.profil') ? 'text-emerald-600' : 'text-gray-400' }}">Profil</span>
                </a>
            </div>
        </main>
    </div>

    <flux:toast />
    @fluxScripts
</body>
</html>
