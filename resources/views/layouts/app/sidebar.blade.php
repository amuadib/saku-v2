<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }
    </style>
    <body class="min-h-screen bg-gray-50 dark:bg-zinc-950 selection:bg-emerald-500 selection:text-white">
        @php
            $periode_aktif = \App\Models\Periode::where('aktif', true)->first();
        @endphp
        <!-- Background Effects -->
        <div class="fixed inset-0 z-[-1] pointer-events-none overflow-hidden">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
            <div class="absolute top-1/2 -right-24 w-96 h-96 bg-teal-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-24 left-1/3 w-96 h-96 bg-emerald-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-[0.03]"></div>
        </div>

        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-white/60 dark:border-zinc-800 dark:bg-zinc-950/60 backdrop-blur-xl">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                @if($periode_aktif)
                    <div class="px-2 mb-2 mt-2">
                        <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/20 px-3 py-2 text-center text-sm font-medium text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50">
                            <span class="block text-xs uppercase tracking-wider text-emerald-500/70 mb-0.5">Tahun Akademik</span>
                            {{ $periode_aktif->nama }}
                        </div>
                    </div>
                @endif

                <flux:sidebar.group :heading="__('Platform')" class="grid mt-2">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Manajemen')" class="grid mt-4">
                    @can('viewAny', \App\Models\Siswa::class)
                        <flux:sidebar.item icon="users" :href="route('admin.siswa.index')" :current="request()->routeIs('admin.siswa.*')" wire:navigate>{{ __('Siswa') }}</flux:sidebar.item>
                    @endcan

                    @can('viewAny', \App\Models\Kelas::class)
                        <flux:sidebar.item icon="building-library" :href="route('admin.kelas.index')" :current="request()->routeIs('admin.kelas.*')" wire:navigate>{{ __('Kelas') }}</flux:sidebar.item>
                    @endcan
                    @can('viewAny', \App\Models\Periode::class)
                        <flux:sidebar.item icon="calendar" :href="route('admin.periode.index')" :current="request()->routeIs('admin.periode.*')" wire:navigate>{{ __('Periode') }}</flux:sidebar.item>
                    @endcan
                    @can('viewAny', \App\Models\User::class)
                        <flux:sidebar.item icon="user-group" :href="route('admin.user.index')" :current="request()->routeIs('admin.user.*')" wire:navigate>{{ __('Pengguna') }}</flux:sidebar.item>
                    @endcan
                    
                    @if(auth()->user()->isAdmin())
                        <flux:sidebar.item icon="clock" :href="route('admin.log-aktivitas.index')" :current="request()->routeIs('admin.log-aktivitas.*')" wire:navigate>{{ __('Log Aktivitas') }}</flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
                
                <flux:sidebar.group :heading="__('Keuangan')" class="grid mt-4">
                    @can('viewAny', \App\Models\Kas::class)
                        <flux:sidebar.item icon="wallet" :href="route('admin.kas.index')" :current="request()->routeIs('admin.kas.*')" wire:navigate>{{ __('Kas') }}</flux:sidebar.item>
                    @endcan
                    @can('viewAny', \App\Models\Tabungan::class)
                        <flux:sidebar.item icon="banknotes" :href="route('admin.tabungan.index')" :current="request()->routeIs('admin.tabungan.*')" wire:navigate>{{ __('Tabungan') }}</flux:sidebar.item>
                    @endcan
                    @can('viewAny', \App\Models\Tagihan::class)
                        <flux:sidebar.item icon="document-text" :href="route('admin.tagihan.index')" :current="request()->routeIs('admin.tagihan.*')" wire:navigate>{{ __('Tagihan') }}</flux:sidebar.item>
                    @endcan
                    <flux:sidebar.item icon="shopping-cart" :href="route('admin.penjualan.index')" :current="request()->routeIs('admin.penjualan.*')" wire:navigate>{{ __('Penjualan') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="shopping-bag" :href="route('admin.pembelian.index')" :current="request()->routeIs('admin.pembelian.*')" wire:navigate>{{ __('Pembelian') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('admin.transaksi.index')" :current="request()->routeIs('admin.transaksi.*')" wire:navigate>{{ __('Transaksi') }}</flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Inventaris & Lainnya')" class="grid mt-4">
                    @can('viewAny', \App\Models\Barang::class)
                        <flux:sidebar.item icon="cube" :href="route('admin.barang.index')" :current="request()->routeIs('admin.barang.*')" wire:navigate>{{ __('Barang') }}</flux:sidebar.item>
                    @endcan
                    @can('viewAny', \App\Models\Supplier::class)
                        <flux:sidebar.item icon="truck" :href="route('admin.supplier.index')" :current="request()->routeIs('admin.supplier.*')" wire:navigate>{{ __('Supplier') }}</flux:sidebar.item>
                    @endcan
                    <flux:sidebar.item icon="chat-bubble-left-ellipsis" :href="route('admin.pengaduan.index')" :current="request()->routeIs('admin.pengaduan.*')" wire:navigate>{{ __('Pengaduan') }}</flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <div class="px-2 pb-2" x-data>
                    <flux:button 
                        variant="subtle" 
                        class="w-full justify-start text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100"
                        x-on:click="$flux.appearance = $flux.appearance === 'light' ? 'dark' : ($flux.appearance === 'dark' ? 'system' : 'light')"
                    >
                        <flux:icon.sun x-show="$flux.appearance === 'light'" class="size-5 mr-2" />
                        <flux:icon.moon x-show="$flux.appearance === 'dark'" class="size-5 mr-2" />
                        <flux:icon.computer-desktop x-show="$flux.appearance === 'system'" class="size-5 mr-2" />
                        <span x-text="$flux.appearance === 'light' ? 'Light Theme' : ($flux.appearance === 'dark' ? 'Dark Theme' : 'System Theme')"></span>
                    </flux:button>
                </div>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden bg-white/60 dark:bg-zinc-950/60 backdrop-blur-xl border-b border-zinc-200 dark:border-zinc-800">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <div class="flex-1 flex flex-col items-center justify-center truncate text-zinc-800 dark:text-zinc-200">
                <div class="text-sm font-semibold">{{ $title ?? '' }}</div>
                @if($periode_aktif)
                    <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-normal leading-none mt-1">TA: {{ $periode_aktif->nama }}</div>
                @endif
            </div>

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <div x-data class="w-full">
                        <flux:menu.item
                            icon="sun"
                            x-show="$flux.appearance === 'light'"
                            x-on:click="$flux.appearance = 'dark'"
                        >
                            Light Theme
                        </flux:menu.item>
                        <flux:menu.item
                            icon="moon"
                            x-show="$flux.appearance === 'dark'"
                            x-on:click="$flux.appearance = 'system'"
                        >
                            Dark Theme
                        </flux:menu.item>
                        <flux:menu.item
                            icon="computer-desktop"
                            x-show="$flux.appearance === 'system'"
                            x-on:click="$flux.appearance = 'light'"
                        >
                            System Theme
                        </flux:menu.item>
                    </div>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
