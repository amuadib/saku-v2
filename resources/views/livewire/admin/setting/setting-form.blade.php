<div>
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan Sistem</h1>
            <p class="text-sm text-gray-500 dark:text-zinc-400">Ubah konfigurasi aplikasi dan lembaga</p>
        </div>
        <flux:button wire:click="save" variant="primary">Simpan Pengaturan</flux:button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Pengaturan Aplikasi -->
        <div class="bg-white dark:bg-white/10 p-6 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm backdrop-blur-md">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <flux:icon name="cog-8-tooth" class="size-5 text-gray-400 dark:text-zinc-400" />
                Informasi Aplikasi
            </h2>
            <div class="space-y-4">
                <flux:input wire:model="app_nama" label="Nama Aplikasi" placeholder="Masukkan nama aplikasi" />
                <flux:input wire:model="app_singkatan" label="Singkatan Aplikasi" placeholder="Misal: SAKU" />
                <flux:textarea wire:model="app_keterangan" label="Keterangan Lengkap" placeholder="Deskripsi aplikasi..." rows="3" />
            </div>
        </div>

        <!-- Pengaturan Lembaga -->
        <div class="bg-white dark:bg-white/10 p-6 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm backdrop-blur-md">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <flux:icon name="building-office-2" class="size-5 text-gray-400 dark:text-zinc-400" />
                        Daftar Lembaga
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">Data lembaga dari API Master (Read-only)</p>
                </div>
                <flux:button wire:click="syncLembaga" size="sm" variant="outline" icon="arrow-path" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="syncLembaga">Sinkronisasi</span>
                    <span wire:loading wire:target="syncLembaga">Menyinkronkan...</span>
                </flux:button>
            </div>
            
            <div class="space-y-4">
                @forelse ($lembaga_items as $id => $lembaga)
                    <div class="flex flex-col gap-4 bg-gray-50 dark:bg-zinc-900/50 p-4 rounded-lg border border-gray-100 dark:border-white/5" wire:key="lembaga-{{ $id }}">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-200 text-gray-800 dark:bg-zinc-700 dark:text-zinc-300">
                                #{{ $id }}
                            </span>
                            <div class="flex-1">
                                <flux:input wire:model="lembaga_items.{{ $id }}.nama" placeholder="Nama lembaga..." readonly disabled />
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                            <flux:input wire:model="lembaga_items.{{ $id }}.singkatan" label="Singkatan / Alias" readonly disabled />
                            <flux:input wire:model="lembaga_items.{{ $id }}.telp" label="Telepon/WA" readonly disabled />
                            <flux:input wire:model="lembaga_items.{{ $id }}.tingkat_string" label="Tingkat" readonly disabled />
                            <flux:input wire:model="lembaga_items.{{ $id }}.kontak" label="Kontak Person" readonly disabled />
                            <flux:textarea wire:model="lembaga_items.{{ $id }}.jam_kerja_string" label="Jam Kerja" rows="3" class="sm:col-span-2" readonly disabled />
                            <flux:textarea wire:model="lembaga_items.{{ $id }}.alamat" label="Alamat Lengkap" rows="3" class="sm:col-span-2" readonly disabled />

                            <!-- Logo & Kop Preview (Read-only) -->
                            <div class="sm:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-gray-200/60 dark:border-white/10">
                                @php
                                    $rawLogo = $lembaga['logo_url'] ?? ($lembaga['logo'] ?? null);
                                    $logoUrl = null;
                                    if (!empty($rawLogo)) {
                                        if (str_starts_with($rawLogo, 'http://') || str_starts_with($rawLogo, 'https://')) {
                                            $logoUrl = $rawLogo;
                                        } elseif (file_exists(public_path(ltrim($rawLogo, '/')))) {
                                            $logoUrl = asset(ltrim($rawLogo, '/'));
                                        } elseif (file_exists(public_path('storage/' . ltrim($rawLogo, '/')))) {
                                            $logoUrl = asset('storage/' . ltrim($rawLogo, '/'));
                                        } else {
                                            $apiBase = config('custom.api_akademik.url');
                                            $cleanPath = ltrim($rawLogo, '/');
                                            $path = str_starts_with($cleanPath, 'storage/') ? $cleanPath : 'storage/' . $cleanPath;
                                            $logoUrl = $apiBase ? rtrim($apiBase, '/') . '/' . $path : asset($rawLogo);
                                        }
                                    }

                                    $rawKop = $lembaga['kop_url'] ?? ($lembaga['kop'] ?? null);
                                    $kopUrl = null;
                                    if (!empty($rawKop)) {
                                        if (str_starts_with($rawKop, 'http://') || str_starts_with($rawKop, 'https://')) {
                                            $kopUrl = $rawKop;
                                        } elseif (file_exists(public_path(ltrim($rawKop, '/')))) {
                                            $kopUrl = asset(ltrim($rawKop, '/'));
                                        } elseif (file_exists(public_path('storage/' . ltrim($rawKop, '/')))) {
                                            $kopUrl = asset('storage/' . ltrim($rawKop, '/'));
                                        } else {
                                            $apiBase = config('custom.api_akademik.url');
                                            $cleanPath = ltrim($rawKop, '/');
                                            $path = str_starts_with($cleanPath, 'storage/') ? $cleanPath : 'storage/' . $cleanPath;
                                            $kopUrl = $apiBase ? rtrim($apiBase, '/') . '/' . $path : asset($rawKop);
                                        }
                                    }
                                @endphp

                                <!-- Logo -->
                                <div class="space-y-1.5">
                                    <flux:label>Logo Lembaga</flux:label>
                                    <div class="flex items-center gap-3">
                                        <div class="size-14 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-zinc-800 flex items-center justify-center overflow-hidden shrink-0 shadow-sm">
                                            @if ($logoUrl)
                                                <img src="{{ $logoUrl }}" alt="Logo" class="size-full object-contain p-1" />
                                            @else
                                                <flux:icon name="photo" class="size-6 text-gray-400 dark:text-zinc-500" />
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-zinc-400 truncate max-w-[200px]" title="{{ $rawLogo }}">
                                            {{ $rawLogo ?? 'Belum ada logo' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Kop Surat -->
                                <div class="space-y-1.5">
                                    <flux:label>Kop Surat</flux:label>
                                    <div class="flex items-center gap-3">
                                        <div class="h-14 w-24 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-zinc-800 flex items-center justify-center overflow-hidden shrink-0 shadow-sm">
                                            @if ($kopUrl)
                                                <img src="{{ $kopUrl }}" alt="Kop" class="size-full object-contain p-1" />
                                            @else
                                                <flux:icon name="document-text" class="size-6 text-gray-400 dark:text-zinc-500" />
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-zinc-400 truncate max-w-[200px]" title="{{ $rawKop }}">
                                            {{ $rawKop ?? 'Belum ada kop' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-sm text-gray-500 dark:text-zinc-400 py-4">Belum ada lembaga. Silakan klik tombol Sinkronisasi untuk mengambil data dari API Master.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
