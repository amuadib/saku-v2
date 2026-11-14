<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.tagihan.index') }}" wire:navigate />
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $tagihan && $tagihan->exists ? 'Edit Tagihan' : 'Buat Tagihan Baru' }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700 max-w-4xl mx-auto mb-6">
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">

                <!-- Row 1: Lembaga & Jenis Tagihan (Kas) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Pilihan Lembaga</flux:label>
                        @if(auth()->user()->isAdmin())
                            <flux:select wire:model.live="lembaga_id" placeholder="Pilih Lembaga">
                                <option value="">-- Pilih Lembaga --</option>
                                @foreach($lembagas as $id => $val)
                                    <option value="{{ $id }}">{{ is_array($val) ? ($val['nama'] ?? $val['singkatan'] ?? $id) : $val }}</option>
                                @endforeach
                            </flux:select>
                        @else
                            @php
                                $lembagaConfig = config('custom.lembaga.'.$lembaga_id);
                                $lembagaNama = is_array($lembagaConfig)
                                    ? ($lembagaConfig['nama'] ?? ($lembagaConfig['singkatan'] ?? $lembaga_id))
                                    : (string) $lembaga_id;
                            @endphp
                            <flux:input value="{{ $lembagaNama }}" disabled readonly />
                        @endif
                        <flux:error name="lembaga_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Jenis Tagihan (Kas)</flux:label>
                        <flux:select wire:model.live="kas_id" :disabled="empty($lembaga_id)">
                            <option value="">-- {{ empty($lembaga_id) ? 'Pilih lembaga terlebih dahulu' : 'Pilih Kas Tagihan' }} --</option>
                            @foreach($kas_list as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="kas_id" />

                        @if (! empty($lembaga_id) && $kas_list->isEmpty())
                            <flux:callout variant="warning" class="mt-2 text-xs">
                                Belum ada kas tagihan yang terdaftar untuk lembaga ini. Silakan atur atau aktifkan fitur tagihan di menu Kas.
                            </flux:callout>
                        @endif
                    </flux:field>
                </div>

                <!-- Row 2: Opsi Peserta Tagihan (Radio Box) -->
                @if (! $tagihan || ! $tagihan->exists)
                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/30 p-5 space-y-4">
                        <flux:field>
                            <flux:label class="font-semibold text-zinc-900 dark:text-zinc-100">Peserta Tagihan</flux:label>
                            <flux:radio.group wire:model.live="peserta_tipe" class="flex flex-col sm:flex-row gap-4 mt-2">
                                <flux:radio value="semua" label="Semua Siswa (di lembaga terpilih)" />
                                <flux:radio value="kelas" label="Kelas tertentu" />
                                <flux:radio value="siswa" label="Siswa" />
                            </flux:radio.group>
                            <flux:error name="peserta_tipe" />
                        </flux:field>

                        <!-- 1. Semua Siswa -->
                        @if ($peserta_tipe === 'semua')
                            <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4 text-sm text-blue-800 dark:text-blue-300">
                                <div class="flex items-center gap-2 font-medium">
                                    <flux:icon name="user-group" class="size-5 text-blue-600 dark:text-blue-400" />
                                    <span>Tagihan Seluruh Siswa</span>
                                </div>
                                <p class="mt-1 text-xs text-blue-700 dark:text-blue-400">
                                    Tagihan akan otomatis dibuatkan untuk seluruh <strong>{{ $semuaSiswaCount }}</strong> siswa aktif pada lembaga terpilih.
                                </p>
                            </div>
                        @endif

                        <!-- 2. Kelas tertentu -->
                        @if ($peserta_tipe === 'kelas')
                            <div class="space-y-3 pt-1">
                                <flux:field>
                                    <flux:label>Pilih Kelas</flux:label>
                                    <flux:select wire:model.live="kelas_id" :disabled="empty($lembaga_id)">
                                        <option value="">-- {{ empty($lembaga_id) ? 'Pilih lembaga terlebih dahulu' : 'Pilih Kelas' }} --</option>
                                        @foreach($kelas_list as $k)
                                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="kelas_id" />
                                </flux:field>

                                @if ($kelas_id)
                                    <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-3 text-xs text-blue-800 dark:text-blue-300 flex items-center justify-between">
                                        <span>Tagihan akan dibuatkan untuk <strong>{{ $kelasSiswaCount }}</strong> siswa aktif di kelas terpilih.</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- 3. Siswa (Tom Select) -->
                        @if ($peserta_tipe === 'siswa')
                            <div class="space-y-3 pt-1">
                                <flux:field>
                                    <flux:label>Pilih Siswa</flux:label>
                                    <div
                                        wire:ignore
                                        wire:key="tomselect-tagihan-siswa-{{ $lembaga_id }}"
                                        x-data="{
                                            val: @entangle('siswa_id'),
                                            options: {{ Js::from($siswaOptions) }},
                                            ts: null,
                                            init() {
                                                this.$nextTick(() => {
                                                    if (typeof TomSelect === 'undefined') return;
                                                    if (this.$refs.select?.tomselect) {
                                                        this.$refs.select.tomselect.destroy();
                                                    }

                                                    this.ts = new TomSelect(this.$refs.select, {
                                                        plugins: ['dropdown_input', 'clear_button'],
                                                        dropdownParent: 'body',
                                                        options: this.options,
                                                        valueField: 'id',
                                                        labelField: 'nama',
                                                        searchField: ['nama', 'nis', 'kelas'],
                                                        maxOptions: 100,
                                                        openOnFocus: true,
                                                        selectOnTab: true,
                                                        placeholder: 'Cari nama, NIS, atau kelas siswa...',
                                                        items: this.val ? [String(this.val)] : [],
                                                        render: {
                                                            option: (data, escape) => {
                                                                const kelasBadge = data.kelas
                                                                    ? `<span class='inline-flex items-center rounded bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 text-[11px] font-medium text-zinc-600 dark:text-zinc-300'>${escape(data.kelas)}</span>`
                                                                    : '';
                                                                const lembagaBadge = data.lembaga
                                                                    ? `<span class='inline-flex items-center rounded bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 px-1.5 py-0.5 text-[11px] font-medium'>${escape(data.lembaga)}</span>`
                                                                    : '';
                                                                return `
                                                                    <div class='flex items-center justify-between gap-3 py-1'>
                                                                        <div class='min-w-0'>
                                                                            <div class='font-medium text-zinc-800 dark:text-zinc-100 truncate'>${escape(data.nama)}</div>
                                                                            <div class='mt-0.5 flex flex-wrap items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400'>
                                                                                <span>NIS: ${escape(data.nis || '-')}</span>
                                                                                ${kelasBadge}
                                                                                ${lembagaBadge}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                `;
                                                            },
                                                            item: (data, escape) => {
                                                                return `<div class='truncate'><span class='font-medium'>${escape(data.nama)}</span> <span class='text-zinc-500 dark:text-zinc-400 text-xs'>(${escape(data.nis || '-')})</span></div>`;
                                                            },
                                                            no_results: () => '<div class=\'no-results\'>Siswa tidak ditemukan</div>',
                                                        },
                                                        onChange: (value) => {
                                                            this.val = value ? String(value) : '';
                                                        },
                                                    });

                                                    this.$watch('val', (newVal) => {
                                                        const target = newVal ? String(newVal) : '';
                                                        if (this.ts && this.ts.getValue() !== target) {
                                                            this.ts.setValue(target, true);
                                                        }
                                                    });
                                                });
                                            }
                                        }"
                                    >
                                        <select x-ref="select" class="w-full">
                                            <option value="">Cari nama, NIS, atau kelas siswa...</option>
                                        </select>
                                    </div>
                                    <flux:error name="siswa_id" />
                                </flux:field>

                                @if ($selectedSiswa)
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 rounded-lg bg-zinc-50 dark:bg-zinc-900/60 border border-zinc-200/80 dark:border-zinc-700/80 px-3 py-2 text-xs text-zinc-600 dark:text-zinc-300">
                                        <span>Siswa: <strong class="font-medium text-zinc-800 dark:text-zinc-100">{{ $selectedSiswa->nama }}</strong></span>
                                        <span>• NIS: <strong class="font-medium text-zinc-800 dark:text-zinc-100">{{ $selectedSiswa->nis ?? '-' }}</strong></span>
                                        @if ($selectedSiswa->kelas)
                                            <span>• Kelas: <strong class="font-medium text-zinc-800 dark:text-zinc-100">{{ $selectedSiswa->kelas->nama }}</strong></span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @else
                    <!-- Mode Edit: Info Siswa -->
                    <flux:field>
                        <flux:label>Siswa</flux:label>
                        <flux:input value="{{ optional($tagihan->siswa)->nama }} (NIS: {{ optional($tagihan->siswa)->nis ?? '-' }})" disabled readonly />
                    </flux:field>
                @endif

                <!-- Row 3: Kode Tagihan & Tanggal Kadaluarsa -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Kode Tagihan</flux:label>
                        <flux:input wire:model="kode" placeholder="Otomatis atau manual" />
                        <flux:error name="kode" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tanggal Kadaluarsa (Opsional)</flux:label>
                        <flux:input type="date" wire:model="tanggal_kadaluarsa" />
                        <flux:error name="tanggal_kadaluarsa" />
                    </flux:field>
                </div>

                <!-- Row 4: Jumlah Tagihan & Dibayar -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Jumlah Tagihan (Rp)</flux:label>
                        <flux:input
                            type="text"
                            wire:model="jumlah"
                            x-mask:dynamic="$money($input, ',', '.', 0)"
                            icon="banknotes"
                            placeholder="0"
                        />
                        <flux:error name="jumlah" />
                    </flux:field>

                    @if ($tagihan && $tagihan->exists || $peserta_tipe === 'siswa')
                        <flux:field>
                            <flux:label>Jumlah Dibayar (Rp)</flux:label>
                            <flux:input
                                type="text"
                                wire:model="bayar"
                                x-mask:dynamic="$money($input, ',', '.', 0)"
                                icon="banknotes"
                                placeholder="0"
                            />
                            <flux:error name="bayar" />
                        </flux:field>
                    @else
                        <div class="flex items-center text-xs text-zinc-500 dark:text-zinc-400 pt-7">
                            <span>* Pembayaran untuk tagihan massal (Semua Siswa / Kelas) dicatat saat siswa melakukan pembayaran.</span>
                        </div>
                    @endif
                </div>

                <!-- Row 5: Keterangan -->
                <flux:field>
                    <flux:label>Keterangan Tambahan</flux:label>
                    <flux:textarea wire:model="keterangan" rows="3" placeholder="Contoh: Tagihan SPP Bulan Juli 2026, Uang Seragam, dll." />
                    <flux:error name="keterangan" />
                </flux:field>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <flux:button variant="ghost" href="{{ route('admin.tagihan.index') }}" wire:navigate>Batal</flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $tagihan && $tagihan->exists ? 'Perbarui Tagihan' : 'Simpan Tagihan' }}
                    </flux:button>
                </div>

            </form>
        </div>
    </div>
</div>
