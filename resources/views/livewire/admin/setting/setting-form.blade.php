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
                <div class="flex gap-2">
                    <flux:modal.trigger name="import-json-modal">
                        <flux:button size="sm" variant="outline" icon="cloud-arrow-down">
                            Impor JSON
                        </flux:button>
                    </flux:modal.trigger>
                    <flux:button wire:click="syncLembaga" size="sm" variant="outline" icon="arrow-path" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="syncLembaga">Sinkronisasi</span>
                        <span wire:loading wire:target="syncLembaga">Menyinkronkan...</span>
                    </flux:button>
                </div>
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
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-start">
                            <flux:input wire:model="lembaga_items.{{ $id }}.singkatan" label="Singkatan / Alias" readonly disabled />
                            <flux:input wire:model="lembaga_items.{{ $id }}.telp" label="Telepon/WA" readonly disabled />
                            <flux:input wire:model="lembaga_items.{{ $id }}.email" label="Email Lembaga" placeholder="info@sekolah.sch.id" readonly disabled />
                            <flux:input wire:model="lembaga_items.{{ $id }}.tingkat_string" label="Tingkat" readonly disabled />
                            <flux:input wire:model="lembaga_items.{{ $id }}.kontak" label="Kontak Person" readonly disabled />
                            <flux:input wire:model="lembaga_items.{{ $id }}.kepala" label="Nama Kepala Lembaga" readonly disabled />
                            <flux:input wire:model="lembaga_items.{{ $id }}.nip_kepala" label="NIP / NIY Kepala" readonly disabled />
                            <flux:input wire:model="lembaga_items.{{ $id }}.lat" label="Latitude (Lintang)" placeholder="-7.536065" readonly disabled />
                            <flux:input wire:model="lembaga_items.{{ $id }}.lon" label="Longitude (Bujur)" placeholder="110.592812" readonly disabled />
                            <flux:textarea wire:model="lembaga_items.{{ $id }}.jam_kerja_string" label="Jam Kerja" rows="3" class="sm:col-span-2 lg:col-span-3" readonly disabled />
                            <flux:textarea wire:model="lembaga_items.{{ $id }}.alamat" label="Alamat Lengkap" rows="3" class="sm:col-span-2 lg:col-span-3" readonly disabled />
                            <flux:input wire:model="lembaga_items.{{ $id }}.maps_embed" label="Google Maps Embed (Iframe / URL)" class="sm:col-span-2 lg:col-span-3" readonly disabled />

                            <!-- Bagian Lokasi & Google Maps Preview -->
                            <div class="sm:col-span-2 lg:col-span-3 pt-3 border-t border-gray-200/60 dark:border-white/10 space-y-2">
                                @php
                                    $mapEmbedUrl = $this->getMapsEmbedUrl($id);
                                @endphp
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="map-pin" class="size-4 text-emerald-600" />
                                        <span class="text-sm font-semibold text-gray-800 dark:text-zinc-200">Pratinjau Peta Google Maps</span>
                                    </div>
                                    @if (!empty($lembaga['lat']) && !empty($lembaga['lon']) && (float)$lembaga['lat'] != 0)
                                        <a href="https://www.google.com/maps?q={{ $lembaga['lat'] }},{{ $lembaga['lon'] }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1">
                                            <span>Buka di Google Maps</span>
                                            <flux:icon name="arrow-top-right-on-square" class="size-3" />
                                        </a>
                                    @endif
                                </div>

                                @if ($mapEmbedUrl)
                                    <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-zinc-800 shadow-sm h-56">
                                        <iframe src="{{ $mapEmbedUrl }}"
                                                width="100%"
                                                height="100%"
                                                style="border:0; display:block;"
                                                allowfullscreen=""
                                                loading="lazy"
                                                referrerpolicy="no-referrer-when-downgrade">
                                        </iframe>
                                    </div>
                                @else
                                    <div class="p-4 rounded-xl border border-dashed border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-zinc-800/50 text-center text-xs text-gray-400">
                                        Belum ada koordinat GPS atau kode embed peta Google Maps yang disinkronkan.
                                    </div>
                                @endif
                            </div>

                            <!-- Logo, Kop, TTD & Stempel Preview (Read-only) -->
                            <div class="sm:col-span-2 lg:col-span-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-3 border-t border-gray-200/60 dark:border-white/10">
                                @php
                                    $resolveMediaUrl = function (?string $rawPath) {
                                        if (empty($rawPath)) {
                                            return null;
                                        }
                                        if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
                                            return $rawPath;
                                        }
                                        $cleanPath = ltrim($rawPath, '/');
                                        if (file_exists(public_path($cleanPath))) {
                                            return asset($cleanPath);
                                        }
                                        if (file_exists(public_path('storage/' . $cleanPath))) {
                                            return asset('storage/' . $cleanPath);
                                        }
                                        $apiBase = config('custom.api_akademik.url');
                                        if ($apiBase) {
                                            $path = str_starts_with($cleanPath, 'storage/') ? $cleanPath : 'storage/' . $cleanPath;
                                            return rtrim($apiBase, '/') . '/' . $path;
                                        }
                                        return asset($rawPath);
                                    };

                                    $rawLogo = $lembaga['logo_url'] ?? ($lembaga['logo'] ?? null);
                                    $logoUrl = $resolveMediaUrl($rawLogo);

                                    $rawKop = $lembaga['kop_url'] ?? ($lembaga['kop'] ?? null);
                                    $kopUrl = $resolveMediaUrl($rawKop);

                                    $rawTtd = $lembaga['ttd_url'] ?? ($lembaga['ttd'] ?? null);
                                    $ttdUrl = $resolveMediaUrl($rawTtd);

                                    $rawStempel = $lembaga['stempel_url'] ?? ($lembaga['stempel'] ?? null);
                                    $stempelUrl = $resolveMediaUrl($rawStempel);
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
                                        <div class="text-xs text-gray-500 dark:text-zinc-400 truncate max-w-[140px]" title="{{ $rawLogo }}">
                                            {{ $rawLogo ?? 'Belum ada' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Kop Surat -->
                                <div class="space-y-1.5">
                                    <flux:label>Kop Surat</flux:label>
                                    <div class="flex items-center gap-3">
                                        <div class="h-14 w-20 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-zinc-800 flex items-center justify-center overflow-hidden shrink-0 shadow-sm">
                                            @if ($kopUrl)
                                                <img src="{{ $kopUrl }}" alt="Kop" class="size-full object-contain p-1" />
                                            @else
                                                <flux:icon name="document-text" class="size-6 text-gray-400 dark:text-zinc-500" />
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-zinc-400 truncate max-w-[140px]" title="{{ $rawKop }}">
                                            {{ $rawKop ?? 'Belum ada' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- TTD Kepala -->
                                <div class="space-y-1.5">
                                    <flux:label>TTD Kepala</flux:label>
                                    <div class="flex items-center gap-3">
                                        <div class="h-14 w-16 rounded-lg border border-gray-200 dark:border-white/10 flex items-center justify-center overflow-hidden shrink-0 shadow-sm"
                                             style="background: repeating-conic-gradient(rgba(128,128,128,0.12) 0% 25%, transparent 0% 50%) 50% / 10px 10px;">
                                            @if ($ttdUrl)
                                                <img src="{{ $ttdUrl }}" alt="TTD" class="size-full object-contain p-1" />
                                            @else
                                                <flux:icon name="pencil" class="size-6 text-gray-400 dark:text-zinc-500" />
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-zinc-400 truncate max-w-[140px]" title="{{ $rawTtd }}">
                                            {{ $rawTtd ?? 'Belum ada' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Stempel Lembaga -->
                                <div class="space-y-1.5">
                                    <flux:label>Stempel Lembaga</flux:label>
                                    <div class="flex items-center gap-3">
                                        <div class="size-14 rounded-lg border border-gray-200 dark:border-white/10 flex items-center justify-center overflow-hidden shrink-0 shadow-sm"
                                             style="background: repeating-conic-gradient(rgba(128,128,128,0.12) 0% 25%, transparent 0% 50%) 50% / 10px 10px;">
                                            @if ($stempelUrl)
                                                <img src="{{ $stempelUrl }}" alt="Stempel" class="size-full object-contain p-1" />
                                            @else
                                                <flux:icon name="check-badge" class="size-6 text-gray-400 dark:text-zinc-500" />
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-zinc-400 truncate max-w-[140px]" title="{{ $rawStempel }}">
                                            {{ $rawStempel ?? 'Belum ada' }}
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

    <!-- Modal Impor JSON Lembaga -->
    <flux:modal name="import-json-modal" wire:model="showImportModal" class="md:w-[620px] space-y-6">
        <div>
            <flux:heading size="lg">Impor Data Lembaga (JSON)</flux:heading>
            <flux:subheading>Unggah berkas <code class="px-1 py-0.5 bg-gray-100 dark:bg-zinc-800 rounded text-xs">.json</code> atau tempel teks JSON dari respon master lembaga.</flux:subheading>
        </div>

        <div class="space-y-4">
            <!-- Upload File via Alpine FileReader (Instant client-side load, avoids Livewire upload endpoint latency & race conditions) -->
            <div x-data="{ fileName: '', fileError: '' }">
                <flux:label>Unggah Berkas JSON</flux:label>
                <input type="file" accept=".json,application/json"
                    x-on:change="
                        fileError = '';
                        const file = $event.target.files[0];
                        if (!file) return;
                        if (!file.name.toLowerCase().endsWith('.json') && file.type !== 'application/json') {
                            fileError = 'Hanya berkas berekstensi .json yang didukung.';
                            return;
                        }
                        if (file.size > 5 * 1024 * 1024) {
                            fileError = 'Ukuran berkas JSON maksimal 5MB.';
                            return;
                        }
                        fileName = file.name;
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            $wire.set('json_text', e.target.result);
                        };
                        reader.onerror = () => {
                            fileError = 'Gagal membaca berkas.';
                        };
                        reader.readAsText(file);
                    "
                    class="mt-1 block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-zinc-800 dark:file:text-emerald-400 border border-gray-300 dark:border-zinc-700 rounded-lg cursor-pointer bg-white dark:bg-zinc-900 focus:outline-none" />
                <template x-if="fileName">
                    <div class="mt-1.5 text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-1 font-medium">
                        <flux:icon name="check-circle" class="size-3.5" />
                        <span>Berkas <strong x-text="fileName"></strong> berhasil dimuat ke editor teks di bawah.</span>
                    </div>
                </template>
                <template x-if="fileError">
                    <span class="text-xs text-rose-500 mt-1 block" x-text="fileError"></span>
                </template>
            </div>

            <div class="relative flex py-1 items-center">
                <div class="flex-grow border-t border-gray-200 dark:border-zinc-700"></div>
                <span class="flex-shrink mx-3 text-[11px] text-gray-400 uppercase tracking-wider font-semibold">Atau Tempel Teks JSON</span>
                <div class="flex-grow border-t border-gray-200 dark:border-zinc-700"></div>
            </div>

            <!-- Paste JSON -->
            <div>
                <flux:textarea wire:model="json_text" label="Teks JSON" placeholder='{"status": "success", "data": {"lembaga": { ... }}}' rows="6" font-mono />
                @error('json_text')
                    <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            @if ($errors->has('import_error'))
                <div class="p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-lg text-xs text-rose-700 dark:text-rose-300 flex items-start gap-2">
                    <flux:icon name="exclamation-triangle" class="size-4 shrink-0 text-rose-600 dark:text-rose-400 mt-0.5" />
                    <span>{{ $errors->first('import_error') }}</span>
                </div>
            @endif
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button wire:click="processImportJson" variant="primary" icon="arrow-up-tray" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="processImportJson">Proses Impor</span>
                <span wire:loading wire:target="processImportJson">Memproses...</span>
            </flux:button>
        </div>
    </flux:modal>
</div>
