<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.informasi.index') }}" wire:navigate />
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $informasi && $informasi->exists ? 'Edit Informasi' : 'Tambah Informasi Baru' }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-zinc-400">
                    {{ $informasi && $informasi->exists ? 'Perbarui konten informasi atau pengumuman' : 'Buat berita atau pengumuman yang dapat dilihat oleh siswa' }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg max-w-4xl mx-auto mb-6">
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                <!-- Judul -->
                <flux:field>
                    <flux:label>Judul Informasi <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="judul" placeholder="Contoh: Jadwal Pembayaran SPP Bulan Juli 2026" />
                    <flux:error name="judul" />
                </flux:field>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Kategori -->
                    <flux:field>
                        <flux:label>Kategori <span class="text-red-500">*</span></flux:label>
                        <div class="space-y-1.5">
                            <flux:input wire:model="kategori" list="kategori-list" placeholder="Pilih atau ketik kategori..." />
                            <datalist id="kategori-list">
                                @foreach($kategori_options as $opt)
                                    <option value="{{ $opt }}">
                                @endforeach
                            </datalist>
                        </div>
                        <flux:error name="kategori" />
                    </flux:field>

                    <!-- Tanggal -->
                    <flux:field>
                        <flux:label>Tanggal Terbit <span class="text-red-500">*</span></flux:label>
                        <flux:input type="date" wire:model="tanggal" />
                        <flux:error name="tanggal" />
                    </flux:field>

                    <!-- Sasaran Lembaga -->
                    <flux:field>
                        <flux:label>Sasaran Lembaga</flux:label>
                        <flux:select wire:model="lembaga_id">
                            <flux:select.option value="">Semua Lembaga (Umum)</flux:select.option>
                            @foreach($lembaga_list as $key => $val)
                                <flux:select.option value="{{ $key }}">{{ $val['nama'] ?? $val }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="lembaga_id" />
                    </flux:field>
                </div>

                <!-- Gambar Banner / Thumbnail -->
                <div class="space-y-2">
                    <flux:label>Gambar / Banner Informasi (Opsional)</flux:label>
                    
                    <div class="flex items-start gap-5 flex-wrap sm:flex-nowrap">
                        <!-- Preview Box -->
                        <div class="w-40 h-28 rounded-2xl border-2 border-dashed border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-900 flex items-center justify-center overflow-hidden shrink-0 relative group">
                            @if ($gambar)
                                <img src="{{ $gambar->temporaryUrl() }}" class="size-full object-cover" alt="Preview Gambar" />
                            @elseif ($existingGambar)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($existingGambar) }}" class="size-full object-cover" alt="Gambar Saat Ini" />
                            @else
                                <div class="text-center p-3 text-gray-400 dark:text-zinc-500">
                                    <flux:icon name="photo" class="size-8 mx-auto mb-1 opacity-60" />
                                    <span class="text-[10px]">Belum ada gambar</span>
                                </div>
                            @endif
                        </div>

                        <!-- Upload Actions -->
                        <div class="flex-1 space-y-2">
                            <input type="file" wire:model="gambar" accept="image/png,image/jpeg,image/jpg,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer" />
                            
                            <div class="text-xs text-gray-500 dark:text-zinc-400">
                                Disarankan gambar rasio horizontal/persegi, format JPG/PNG/WebP, maksimal ukuran 2MB.
                            </div>

                            <div wire:loading wire:target="gambar" class="text-xs text-emerald-600 font-semibold flex items-center gap-1.5">
                                <svg class="animate-spin size-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                                Sedang mengunggah gambar...
                            </div>

                            @if ($existingGambar || $gambar)
                                <button type="button" wire:click="removeGambar" class="text-xs text-red-600 hover:text-red-700 font-semibold flex items-center gap-1 mt-1 transition">
                                    <flux:icon name="trash" class="size-3.5" />
                                    Hapus gambar
                                </button>
                            @endif

                            <flux:error name="gambar" />
                        </div>
                    </div>
                </div>

                <!-- Konten Informasi -->
                <flux:field>
                    <flux:label>Isi / Konten Pengumuman <span class="text-red-500">*</span></flux:label>
                    <flux:textarea wire:model="konten" rows="8" placeholder="Tuliskan isi pengumuman atau berita secara lengkap..." />
                    <flux:error name="konten" />
                </flux:field>

                <!-- Status Publikasi & Sticky Switch -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-zinc-900 border border-gray-100 dark:border-white/5 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-sm text-gray-800 dark:text-zinc-200">Status Publikasi</div>
                            <div class="text-xs text-gray-500 dark:text-zinc-400">Tampilkan di portal dan aplikasi siswa.</div>
                        </div>
                        <flux:checkbox wire:model="is_aktif" label="Aktifkan" />
                    </div>

                    <div class="p-4 rounded-xl bg-red-50/50 dark:bg-red-950/20 border border-red-100 dark:border-red-900/30 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-sm text-red-900 dark:text-red-300 flex items-center gap-1.5">
                                <flux:icon name="bookmark" class="size-4 text-red-600" />
                                Berita Utama (Sticky)
                            </div>
                            <div class="text-xs text-red-700/70 dark:text-red-400/80">Sematkan di banner paling atas dashboard siswa.</div>
                        </div>
                        <flux:checkbox wire:model="is_sticky" label="Sticky" />
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-zinc-700">
                    <flux:button variant="ghost" href="{{ route('admin.informasi.index') }}" wire:navigate>
                        Batal
                    </flux:button>
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            {{ $informasi && $informasi->exists ? 'Simpan Perubahan' : 'Terbitkan Informasi' }}
                        </span>
                        <span wire:loading wire:target="save">
                            Menyimpan...
                        </span>
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
