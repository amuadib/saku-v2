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

    <!-- Summary Stats (Mobile Carousel & Desktop Grid) -->
    <x-stat-carousel :cols="2">
        <x-stat-card
            title="Siswa Berhutang"
            :value="number_format($siswaList->total(), 0, ',', '.') . ' Siswa'"
            icon="users"
            color="amber"
        />

        <x-stat-card
            title="Total Tunggakan"
            :value="'Rp ' . number_format($grandTotalTunggakan, 0, ',', '.')"
            icon="banknotes"
            color="rose"
        />
    </x-stat-carousel>

    <!-- Main Container -->
    <div class="bg-white dark:bg-zinc-900/70 shadow-xs sm:rounded-2xl border border-zinc-200 dark:border-zinc-800 mb-6 backdrop-blur-xl">
        <div class="p-4 sm:p-6">

            <!-- Search Filter -->
            <div class="mb-5 max-w-md">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama atau NIS siswa..." icon="magnifying-glass" />
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <!-- Dual View: Desktop Table vs Mobile Cards -->
            <x-responsive-view>
                <x-slot name="desktop">
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
                                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                                        {{ $siswaList->firstItem() + $index }}
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $siswa->nama }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5 mt-0.5">
                                            <span>NIS: {{ $siswa->nis ?? '-' }}</span>
                                            <span>•</span>
                                            <span>{{ $siswa->telepon ? $siswa->telepon : 'No WA: -' }}</span>
                                        </div>
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        @if ($siswa->kelas)
                                            <flux:badge size="sm" color="zinc">{{ $siswa->kelas->nama }}</flux:badge>
                                        @else
                                            <span class="text-zinc-400 dark:text-zinc-500">-</span>
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
                                                <span class="text-zinc-400 dark:text-zinc-500 text-xs">-</span>
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
                                    <flux:table.cell colspan="{{ count($kasList) + 5 }}" class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <flux:icon name="check-circle" class="size-8 text-emerald-500" />
                                            <span class="font-medium">Tidak ada siswa yang mempunyai sisa tagihan pada filter ini.</span>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </x-slot>

                <x-slot name="mobile">
                    @forelse ($siswaList as $siswa)
                        @php
                            $totalSisaSiswa = $siswa->tagihan->sum(fn ($t) => (float) $t->jumlah - (float) ($t->bayar ?? 0));
                        @endphp
                        <x-mobile-card
                            :title="$siswa->nama"
                            avatar-color="emerald"
                        >
                            <x-slot name="header">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="size-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm shadow-xs shrink-0">
                                            {{ strtoupper(substr($siswa->nama, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm truncate leading-tight">{{ $siswa->nama }}</h4>
                                            <div class="flex items-center gap-2 mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                <span>NIS: {{ $siswa->nis ?? '-' }}</span>
                                                @if ($siswa->kelas)
                                                    <span>•</span>
                                                    <flux:badge size="sm" color="zinc" class="!px-1.5 !py-0 text-[10px]">{{ $siswa->kelas->nama }}</flux:badge>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </x-slot>

                            <!-- Rincian Kas Tagihan -->
                            <div class="rounded-xl bg-zinc-50 dark:bg-zinc-900/60 p-3 space-y-2 border border-zinc-100 dark:border-zinc-800/60 text-xs">
                                <div class="text-[10px] font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Rincian Kas Tagihan</div>
                                @php $hasDebt = false; @endphp
                                @foreach($kasList as $kas)
                                    @php
                                        $sisaKas = $siswa->tagihan
                                            ->where('kas_id', $kas->id)
                                            ->sum(fn ($t) => (float) $t->jumlah - (float) ($t->bayar ?? 0));
                                    @endphp
                                    @if ($sisaKas > 0)
                                        @php $hasDebt = true; @endphp
                                        <div class="flex items-center justify-between py-0.5">
                                            <span class="text-zinc-600 dark:text-zinc-300">{{ $kas->nama }}</span>
                                            <span class="font-medium text-red-600 dark:text-red-400">Rp {{ number_format($sisaKas, 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                @endforeach
                                @if (! $hasDebt)
                                    <div class="text-zinc-400 italic text-[11px]">Tidak ada tunggakan kas</div>
                                @endif
                            </div>

                            <!-- Total Tagihan -->
                            <div class="flex items-center justify-between pt-1 border-t border-zinc-100 dark:border-zinc-800">
                                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Total Sisa Tagihan</span>
                                <span class="text-base font-bold text-red-600 dark:text-red-400">
                                    Rp {{ number_format($totalSisaSiswa, 0, ',', '.') }}
                                </span>
                            </div>

                            <!-- Action Button WhatsApp -->
                            <x-slot name="actions">
                                @if (! empty($siswa->telepon))
                                    <flux:button
                                        size="sm"
                                        icon="paper-airplane"
                                        class="w-full !bg-emerald-600 hover:!bg-emerald-700 !text-white text-xs font-semibold justify-center py-2"
                                        wire:click="kirimWa('{{ $siswa->id }}')"
                                        wire:loading.attr="disabled"
                                    >
                                        Kirim Rekap WA ({{ $siswa->telepon }})
                                    </flux:button>
                                @else
                                    <div class="w-full py-2 text-center text-xs text-zinc-400 bg-zinc-100 dark:bg-zinc-800 rounded-xl">
                                        Nomor WhatsApp belum terdaftar
                                    </div>
                                @endif
                            </x-slot>
                        </x-mobile-card>
                    @empty
                        <div class="text-center py-12 px-4 bg-zinc-50 dark:bg-zinc-800/40 rounded-2xl border border-dashed border-zinc-200 dark:border-zinc-700">
                            <flux:icon name="check-circle" class="size-10 text-emerald-500 mx-auto mb-2" />
                            <p class="font-medium text-zinc-800 dark:text-zinc-200 text-sm">Tidak ada siswa berhutang</p>
                            <p class="text-xs text-zinc-400 mt-1">Semua tagihan lunas atau tidak cocok dengan filter pencarian.</p>
                        </div>
                    @endforelse
                </x-slot>
            </x-responsive-view>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $siswaList->links() }}
            </div>

        </div>
    </div>
</div>

