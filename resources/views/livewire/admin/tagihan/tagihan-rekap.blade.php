<div>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.tagihan.index') }}" wire:navigate />
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Rekap Tagihan Siswa
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Daftar siswa yang memiliki tanggungan tagihan dan pengiriman rekap ke WhatsApp.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if ($siswaList->total() > 0)
                    <flux:button
                        variant="primary"
                        icon="paper-airplane"
                        wire:click="kirimSemuaWa"
                        wire:confirm="Kirimkan rekap tagihan via WhatsApp ke seluruh siswa berhutang pada filter ini?"
                        class="!bg-emerald-600 hover:!bg-emerald-700 !text-white"
                    >
                        Kirim WA Semua ({{ $siswaList->total() }})
                    </flux:button>
                @endif
                <flux:button variant="ghost" href="{{ route('admin.tagihan.index') }}" wire:navigate icon="arrow-left">
                    Kembali
                </flux:button>
            </div>
        </div>
    </x-slot>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Siswa Memiliki Tagihan</span>
                <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ number_format($siswaList->total(), 0, ',', '.') }}
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">Siswa</span>
                </div>
            </div>
            <div class="size-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                <flux:icon name="users" class="size-6" />
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Sisa Tunggakan</span>
                <div class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">
                    Rp {{ number_format($grandTotalTunggakan, 0, ',', '.') }}
                </div>
            </div>
            <div class="size-12 rounded-xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center text-red-600 dark:text-red-400">
                <flux:icon name="banknotes" class="size-6" />
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700 mb-6">
        <div class="p-6">

            <!-- Search Filter -->
            <div class="mb-6 max-w-md">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama atau NIS siswa..." icon="magnifying-glass" />
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <!-- Matrix Rekap Tagihan Table -->
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="w-12">No</flux:table.column>
                        <flux:table.column
                            sortable
                            :sorted="$sortField === 'nama'"
                            :direction="$sortField === 'nama' ? $sortDirection : null"
                            wire:click="sortBy('nama')"
                            class="min-w-[180px] cursor-pointer"
                        >
                            Nama Siswa
                        </flux:table.column>
                        <flux:table.column
                            sortable
                            :sorted="$sortField === 'kelas'"
                            :direction="$sortField === 'kelas' ? $sortDirection : null"
                            wire:click="sortBy('kelas')"
                            class="w-24 cursor-pointer"
                        >
                            Kelas
                        </flux:table.column>
                        
                        <!-- Header List Tagihan (Kolom untuk masing-masing Kas Tagihan) -->
                        @foreach($kasList as $kas)
                            @php
                                $kasField = 'kas_'.$kas->id;
                            @endphp
                            <flux:table.column
                                align="end"
                                sortable
                                :sorted="$sortField === $kasField"
                                :direction="$sortField === $kasField ? $sortDirection : null"
                                wire:click="sortBy('{{ $kasField }}')"
                                class="min-w-[140px] cursor-pointer"
                            >
                                {{ $kas->nama }}
                            </flux:table.column>
                        @endforeach

                        <flux:table.column
                            align="end"
                            sortable
                            :sorted="$sortField === 'total'"
                            :direction="$sortField === 'total' ? $sortDirection : null"
                            wire:click="sortBy('total')"
                            class="min-w-[150px] font-bold cursor-pointer"
                        >
                            Total Tagihan
                        </flux:table.column>
                        <flux:table.column align="center" class="w-36">Kirim WhatsApp</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($siswaList as $index => $siswa)
                            @php
                                $totalSisaSiswa = $siswa->tagihan->sum(fn ($t) => (float) $t->jumlah - (float) ($t->bayar ?? 0));
                            @endphp
                            <flux:table.row>
                                <flux:table.cell class="text-gray-500 dark:text-gray-400">
                                    {{ $siswaList->firstItem() + $index }}
                                </flux:table.cell>

                                <flux:table.cell>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->nama }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5 mt-0.5">
                                        <span>NIS: {{ $siswa->nis ?? '-' }}</span>
                                        <span>•</span>
                                        <span>{{ $siswa->telepon ? $siswa->telepon : 'No WA: -' }}</span>
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell>
                                    @if ($siswa->kelas)
                                        <flux:badge size="sm" color="zinc">{{ $siswa->kelas->nama }}</flux:badge>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">-</span>
                                    @endif
                                </flux:table.cell>

                                <!-- Nilai per Kas Tagihan -->
                                @foreach($kasList as $kas)
                                    @php
                                        $sisaKas = $siswa->tagihan
                                            ->where('kas_id', $kas->id)
                                            ->sum(fn ($t) => (float) $t->jumlah - (float) ($t->bayar ?? 0));
                                    @endphp
                                    <flux:table.cell align="end">
                                        @if ($sisaKas > 0)
                                            <span class="font-medium text-red-600 dark:text-red-400">
                                                Rp {{ number_format($sisaKas, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500 text-xs">-</span>
                                        @endif
                                    </flux:table.cell>
                                @endforeach

                                <!-- Total Tagihan Siswa -->
                                <flux:table.cell align="end">
                                    <span class="font-bold text-red-600 dark:text-red-400">
                                        Rp {{ number_format($totalSisaSiswa, 0, ',', '.') }}
                                    </span>
                                </flux:table.cell>

                                <!-- Kolom Terakhir: Tombol Kirim Rekap Tagihan ke WhatsApp -->
                                <flux:table.cell align="center">
                                    @if (! empty($siswa->telepon))
                                        <flux:button
                                            size="sm"
                                            icon="paper-airplane"
                                            class="!bg-emerald-600 hover:!bg-emerald-700 !text-white text-xs whitespace-nowrap"
                                            wire:click="kirimWa('{{ $siswa->id }}')"
                                            wire:loading.attr="disabled"
                                            title="Kirim rincian tagihan via WhatsApp ke {{ $siswa->nama }}"
                                        >
                                            Kirim WA
                                        </flux:button>
                                    @else
                                        <flux:badge size="sm" color="red" class="cursor-not-allowed text-xs" title="Nomor WhatsApp belum terdaftar di data siswa">
                                            Tanpa WA
                                        </flux:badge>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="{{ count($kasList) + 5 }}" class="text-center py-10 text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <flux:icon name="check-circle" class="size-8 text-emerald-500" />
                                        <span class="font-medium">Tidak ada siswa yang mempunyai sisa tagihan pada filter ini.</span>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $siswaList->links() }}
            </div>

        </div>
    </div>
</div>

