<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.tabungan.index') }}" wire:navigate />
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $tabungan && $tabungan->exists ? 'Edit Tabungan' : 'Tambah Tabungan' }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl max-w-2xl mx-auto mb-6 border border-zinc-200/70 dark:border-zinc-700/70">
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">

                <!-- Siswa Selection with Tom Select -->
                <flux:field>
                    <flux:label>Siswa</flux:label>
                    <div
                        wire:ignore
                        x-data="{
                            ts: null,
                            val: @entangle('siswa_id').live,
                            options: @js($siswaOptions),
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

                    @if ($selectedSiswa)
                        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 rounded-lg bg-zinc-50 dark:bg-zinc-900/60 border border-zinc-200/80 dark:border-zinc-700/80 px-3 py-2 text-xs text-zinc-600 dark:text-zinc-300">
                            <span>Siswa: <strong class="font-medium text-zinc-800 dark:text-zinc-100">{{ $selectedSiswa->nama }}</strong></span>
                            <span>• NIS: <strong class="font-medium text-zinc-800 dark:text-zinc-100">{{ $selectedSiswa->nis ?? '-' }}</strong></span>
                            @if ($selectedSiswa->kelas)
                                <span>• Kelas: <strong class="font-medium text-zinc-800 dark:text-zinc-100">{{ $selectedSiswa->kelas->nama }}</strong></span>
                            @endif
                            @php
                                $lembagaConfig = config('custom.lembaga.'.$selectedSiswa->lembaga_id);
                                $lembagaNama = is_array($lembagaConfig)
                                    ? ($lembagaConfig['nama'] ?? ($lembagaConfig['singkatan'] ?? ''))
                                    : (string) ($lembagaConfig ?? '');
                            @endphp
                            @if ($lembagaNama)
                                <span>• Lembaga: <strong class="font-semibold text-blue-600 dark:text-blue-400">{{ $lembagaNama }}</strong></span>
                            @endif
                        </div>
                    @endif
                </flux:field>

                <!-- Kas Tabungan (Filtered by Selected Student's Lembaga, Disabled if no Student Selected) -->
                <flux:field>
                    <div class="flex items-center justify-between">
                        <flux:label>Kas Tabungan</flux:label>
                        @if (empty($siswa_id))
                            <span class="text-xs text-zinc-400 dark:text-zinc-500 italic">Pilih siswa terlebih dahulu</span>
                        @elseif ($selectedSiswa)
                            @php
                                $lembagaConfig = config('custom.lembaga.'.$selectedSiswa->lembaga_id);
                                $lembagaNama = is_array($lembagaConfig)
                                    ? ($lembagaConfig['singkatan'] ?? ($lembagaConfig['nama'] ?? ''))
                                    : (string) ($lembagaConfig ?? '');
                            @endphp
                            @if ($lembagaNama)
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Lembaga: <strong class="font-medium text-zinc-700 dark:text-zinc-200">{{ $lembagaNama }}</strong>
                                </span>
                            @endif
                        @endif
                    </div>

                    <flux:select
                        wire:model="kas_id"
                        wire:key="kas-select-{{ $siswa_id ? $siswa_id : 'none' }}"
                        :disabled="empty($siswa_id)"
                    >
                        <option value="">{{ empty($siswa_id) ? '-- Pilih Siswa Terlebih Dahulu --' : '-- Pilih Kas Tabungan --' }}</option>
                        @foreach ($kas_list as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="kas_id" />

                    @if (! empty($siswa_id) && $kas_list->isEmpty())
                        <flux:callout variant="warning" class="mt-2 text-xs">
                            Belum ada kas tabungan yang terdaftar untuk lembaga siswa ini. Silakan tambahkan atau aktifkan kas tabungan di menu Kas terlebih dahulu.
                        </flux:callout>
                    @endif
                </flux:field>

                <!-- Saldo Field with automatic currency formatting -->
                <flux:field>
                    <flux:label>
                        {{ $existingTabungan ? 'Nominal Tambahan Saldo' : 'Saldo Awal' }} (Rp)
                    </flux:label>
                    <flux:input
                        type="text"
                        wire:model="saldo"
                        x-mask:dynamic="$money($input, ',', '.', 0)"
                        icon="banknotes"
                        placeholder="0"
                    />
                    <flux:error name="saldo" />

                    @if ($existingTabungan)
                        <flux:callout variant="info" icon="information-circle" class="mt-2 text-xs">
                            Siswa ini sudah memiliki rekening tabungan pada kas ini dengan saldo saat ini:
                            <strong class="font-semibold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format((float) $existingTabungan->saldo, 0, ',', '.') }}
                            </strong>.
                            Nominal yang Anda masukkan akan otomatis <strong>ditambahkan</strong> ke saldo tabungan yang ada.
                        </flux:callout>
                    @endif
                </flux:field>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <flux:button variant="ghost" href="{{ route('admin.tabungan.index') }}" wire:navigate>Batal</flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $existingTabungan ? 'Tambah Saldo Tabungan' : 'Simpan' }}
                    </flux:button>
                </div>

            </form>
        </div>
    </div>
</div>
