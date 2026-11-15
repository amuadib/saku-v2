<div>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Data Tagihan
            </h2>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <flux:button variant="outline" href="{{ route('admin.tagihan.rekap') }}" wire:navigate icon="document-chart-bar" class="flex-1 sm:flex-none">
                    Rekap Tagihan
                </flux:button>
                <flux:button variant="primary" href="{{ route('admin.tagihan.create') }}" wire:navigate icon="plus" class="flex-1 sm:flex-none">
                    Buat Tagihan
                </flux:button>
            </div>
        </div>
    </x-slot>

    <!-- Ringkasan Keuangan (Stat Cards: Mobile Carousel & Desktop Grid) -->
    <x-stat-carousel :cols="4">
        <!-- Card 1: Total Tagihan -->
        <x-stat-card
            title="Total Tagihan"
            :value="'Rp ' . number_format($stats['totalNominal'], 0, ',', '.')"
            icon="banknotes"
            color="indigo"
            :description="number_format($stats['countTotal'], 0, ',', '.') . ' total tagihan diterbitkan'"
        />

        <!-- Card 2: Sudah Terbayar -->
        <x-stat-card
            title="Sudah Terbayar"
            :value="'Rp ' . number_format($stats['bayarNominal'], 0, ',', '.')"
            icon="check-circle"
            color="emerald"
            :progress="$stats['percentLunas']"
            :progress-label="$stats['percentLunas'] . '% Terkumpul'"
            :progress-sub="number_format($stats['countLunas'], 0, ',', '.') . ' Lunas'"
        />

        <!-- Card 3: Sisa Tunggakan -->
        <x-stat-card
            title="Sisa Tunggakan"
            :value="'Rp ' . number_format($stats['sisaNominal'], 0, ',', '.')"
            icon="exclamation-circle"
            color="rose"
            :description="number_format($stats['countBelumLunas'], 0, ',', '.') . ' tagihan belum lunas'"
        />

        <!-- Card 4: Capaian Pelunasan -->
        <x-stat-card
            title="Capaian Pelunasan"
            :value="$stats['percentLunas'] . '%'"
            icon="chart-pie"
            color="sky"
            description="Rasio pelunasan seluruh kas"
        />
    </x-stat-carousel>

    <!-- Main Container -->
    <div class="bg-white dark:bg-zinc-900/70 shadow-xs sm:rounded-2xl border border-zinc-200 dark:border-zinc-800 mb-6 backdrop-blur-xl">
        <div class="p-4 sm:p-6">

            <!-- Filter & Search Controls -->
            <div class="mb-4 space-y-3">
                <div class="flex flex-col sm:flex-row gap-3">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama, NIS, kode tagihan, atau keterangan..."
                        icon="magnifying-glass"
                        class="w-full sm:flex-1"
                    />

                    <flux:select wire:model.live="filter_kas_id" class="w-full sm:w-1/3" placeholder="Semua Kas">
                        <flux:select.option value="">Semua Kas</flux:select.option>
                        @foreach($kasList as $kas)
                            <flux:select.option value="{{ $kas->id }}">
                                {{ $kas->nama }} {{ isset($lembagaList[$kas->lembaga_id]) ? '- ' . ($lembagaList[$kas->lembaga_id]['singkatan'] ?? $lembagaList[$kas->lembaga_id]['nama']) : '' }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    <div class="hidden sm:block sm:w-1/4">
                        <flux:select wire:model.live="filter_status" placeholder="Semua Status">
                            <flux:select.option value="">Semua Status</flux:select.option>
                            <flux:select.option value="lunas">Lunas</flux:select.option>
                            <flux:select.option value="belum">Belum Lunas</flux:select.option>
                        </flux:select>
                    </div>
                </div>

                <!-- Status Pills untuk Mobile (Mudah di-tap satu tangan) -->
                <x-filter-pills>
                    <x-filter-pill wire:click="$set('filter_status', '')" :active="$filter_status === ''" color="indigo">
                        Semua Status
                    </x-filter-pill>
                    <x-filter-pill wire:click="$set('filter_status', 'belum')" :active="$filter_status === 'belum'" color="rose">
                        Belum Lunas
                    </x-filter-pill>
                    <x-filter-pill wire:click="$set('filter_status', 'lunas')" :active="$filter_status === 'lunas'" color="emerald">
                        Lunas
                    </x-filter-pill>
                </x-filter-pills>
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
                            <flux:table.column>Kode / Tanggal</flux:table.column>
                            <flux:table.column>Siswa</flux:table.column>
                            <flux:table.column>Jenis Tagihan / Keterangan</flux:table.column>
                            <flux:table.column align="end">Nominal</flux:table.column>
                            <flux:table.column align="center">Status</flux:table.column>
                            <flux:table.column align="end">Aksi</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse ($tagihans as $tagihan)
                                <flux:table.row>
                                    <flux:table.cell>
                                        <div class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">{{ $tagihan->kode ?? '-' }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $tagihan->created_at->format('d/m/Y') }}</div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $tagihan->siswa?->nama ?? '-' }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                            {{ $tagihan->siswa?->kelas?->nama ?? '-' }} • NIS: {{ $tagihan->siswa?->nis ?? '-' }}
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="font-medium text-zinc-800 dark:text-zinc-200">{{ $tagihan->kas?->nama ?? '-' }}</div>
                                        @if ($tagihan->keterangan)
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $tagihan->keterangan }}</div>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell align="end">
                                        <div class="font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}</div>
                                        @if ($tagihan->bayar > 0 && ! $tagihan->isLunas())
                                            <div class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">Sisa: Rp {{ number_format($tagihan->jumlah - $tagihan->bayar, 0, ',', '.') }}</div>
                                        @elseif ($tagihan->isLunas())
                                            <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">Lunas terbayar</div>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell align="center">
                                        @if($tagihan->isLunas())
                                            <flux:badge color="green" size="sm">Lunas</flux:badge>
                                        @elseif($tagihan->bayar > 0)
                                            <flux:badge color="yellow" size="sm">Sebagian</flux:badge>
                                        @else
                                            <flux:badge color="red" size="sm">Belum Lunas</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell align="end">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if(!$tagihan->isLunas())
                                                <flux:button size="sm" wire:click="openPayModal('{{ $tagihan->id }}')" icon="banknotes" color="green" tooltip="Bayar Tagihan">
                                                    Bayar
                                                </flux:button>
                                            @else
                                                <flux:button size="sm" variant="outline" href="{{ route('admin.siswa.cetak-kwitansi', ['siswa' => $tagihan->siswa_id, 'ids' => $tagihan->id]) }}" target="_blank" icon="printer" tooltip="Cetak Kwitansi">
                                                    Kwitansi
                                                </flux:button>
                                            @endif

                                            <flux:button size="sm" variant="ghost" href="{{ route('admin.tagihan.edit', $tagihan->id) }}" wire:navigate icon="pencil-square" tooltip="Edit Tagihan" />

                                            <flux:button size="sm" variant="danger" wire:click="delete('{{ $tagihan->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus tagihan ini?" icon="trash" tooltip="Hapus Tagihan" />
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="6" class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                                        Data tagihan tidak ditemukan.
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </x-slot>

                <x-slot name="mobile">
                    @forelse ($tagihans as $tagihan)
                        <x-mobile-card
                            :title="$tagihan->siswa?->nama ?? '-'"
                            :subtitle="($tagihan->siswa?->kelas?->nama ?? '-') . ' • NIS: ' . ($tagihan->siswa?->nis ?? '-')"
                            :badge="$tagihan->isLunas() ? 'Lunas' : ($tagihan->bayar > 0 ? 'Sebagian' : 'Belum Lunas')"
                            :badge-color="$tagihan->isLunas() ? 'green' : ($tagihan->bayar > 0 ? 'yellow' : 'red')"
                            avatar-color="indigo"
                        >
                            <!-- Rincian Kas & Nominal -->
                            <div class="pt-2.5 border-t border-zinc-200 dark:border-zinc-700/80 flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200 block">{{ $tagihan->kas?->nama ?? '-' }}</span>
                                    <span class="text-zinc-500 dark:text-zinc-400 text-[11px] font-mono">{{ $tagihan->kode ?? '-' }} • {{ $tagihan->created_at->format('d/m/Y') }}</span>
                                </div>
                                <div class="text-right">
                                    <div class="font-extrabold text-sm text-zinc-900 dark:text-zinc-100">
                                        Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}
                                    </div>
                                    @if($tagihan->bayar > 0 && ! $tagihan->isLunas())
                                        <div class="text-[11px] text-amber-600 dark:text-amber-400 font-medium">
                                            Sisa: Rp {{ number_format($tagihan->jumlah - $tagihan->bayar, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if ($tagihan->keterangan)
                                <div class="text-xs text-zinc-600 dark:text-zinc-300 bg-zinc-50 dark:bg-zinc-800/80 px-3 py-1.5 rounded-xl border border-zinc-200/80 dark:border-zinc-700 mt-2">
                                    {{ $tagihan->keterangan }}
                                </div>
                            @endif

                            <!-- Aksi Cepat Ramah Jempol -->
                            <x-slot name="actions">
                                @if(!$tagihan->isLunas())
                                    <flux:button
                                        size="sm"
                                        color="green"
                                        icon="banknotes"
                                        wire:click="openPayModal('{{ $tagihan->id }}')"
                                        class="flex-1 text-xs"
                                    >
                                        Bayar
                                    </flux:button>
                                @else
                                    <a
                                        href="{{ route('admin.siswa.cetak-kwitansi', ['siswa' => $tagihan->siswa_id, 'ids' => $tagihan->id]) }}"
                                        target="_blank"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 transition-colors"
                                    >
                                        <flux:icon name="printer" class="size-3.5" />
                                        <span>Kwitansi</span>
                                    </a>
                                @endif

                                @if($tagihan->siswa?->telepon)
                                    @php
                                        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $tagihan->siswa->telepon);
                                        if (str_starts_with($cleanPhone, '0')) {
                                            $cleanPhone = '62'.substr($cleanPhone, 1);
                                        }
                                        $sisaNominal = $tagihan->jumlah - ($tagihan->bayar ?? 0);
                                        $namaSiswa = $tagihan->siswa?->nama ?? 'Siswa';
                                        $namaKas = $tagihan->kas?->nama ?? 'Tagihan';
                                        $pesanWa = "Halo {$namaSiswa}, informasi tagihan {$namaKas} sebesar Rp " . number_format($sisaNominal, 0, ',', '.') . " " . ($tagihan->isLunas() ? 'telah LUNAS. Terima kasih.' : 'menunggu pembayaran. Terima kasih.');
                                        $waUrl = 'https://api.whatsapp.com/send?phone=' . $cleanPhone . '&text=' . rawurlencode($pesanWa);
                                    @endphp
                                    <a
                                        href="{{ $waUrl }}"
                                        target="_blank"
                                        title="Kirim Pesan WhatsApp"
                                        class="p-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 transition-colors"
                                    >
                                        <flux:icon name="chat-bubble-left-ellipsis" class="size-4" />
                                    </a>
                                @endif

                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    href="{{ route('admin.tagihan.edit', $tagihan->id) }}"
                                    wire:navigate
                                    icon="pencil-square"
                                    class="text-xs"
                                />

                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    wire:click="delete('{{ $tagihan->id }}')"
                                    wire:confirm="Apakah Anda yakin ingin menghapus tagihan ini?"
                                    icon="trash"
                                    class="text-xs"
                                />
                            </x-slot>
                        </x-mobile-card>
                    @empty
                        <div class="bg-zinc-50 dark:bg-zinc-900/40 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 text-center">
                            <flux:icon name="magnifying-glass" class="size-8 text-zinc-400 mx-auto mb-2" />
                            <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Data tagihan tidak ditemukan</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Ubah kata kunci pencarian atau filter status.</p>
                        </div>
                    @endforelse
                </x-slot>
            </x-responsive-view>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $tagihans->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Pembayaran Tagihan -->
    <flux:modal wire:model="showPayModal" class="md:w-[500px]">
        @if($this->selectedTagihan)
            <flux:heading size="lg">Konfirmasi Pembayaran Tagihan</flux:heading>
            
            <div class="mt-4 bg-gray-50 dark:bg-gray-800 rounded-xl p-4 space-y-3 border border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-3 text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Siswa</span>
                    <span class="col-span-2 font-medium">{{ optional($this->selectedTagihan->siswa)->nama }} ({{ optional($this->selectedTagihan->siswa)->nis }})</span>
                </div>
                <div class="grid grid-cols-3 text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Jenis Tagihan</span>
                    <span class="col-span-2 font-medium">{{ optional($this->selectedTagihan->kas)->nama }}</span>
                </div>
                <div class="grid grid-cols-3 text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Kode Tagihan</span>
                    <span class="col-span-2 font-mono text-xs font-semibold">{{ $this->selectedTagihan->kode ?? '-' }}</span>
                </div>
                <div class="grid grid-cols-3 text-sm pt-2 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Sisa Tagihan</span>
                    <span class="col-span-2 font-bold text-red-600 dark:text-red-400">Rp {{ number_format($this->selectedTagihan->jumlah - $this->selectedTagihan->bayar, 0, ',', '.') }}</span>
                </div>
                <div class="grid grid-cols-3 text-sm border-gray-200 dark:border-gray-700">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Total Dibayar</span>
                    <span class="col-span-2 font-bold text-green-600 dark:text-green-400">Rp {{ number_format($this->selectedTagihan->jumlah - $this->selectedTagihan->bayar, 0, ',', '.') }}</span>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end gap-2">
                <flux:button wire:click="$set('showPayModal', false)" variant="outline">Batal</flux:button>
                <flux:button wire:click="prosesPembayaran" color="green">Proses Pembayaran</flux:button>
            </div>
        @endif
    </flux:modal>
</div>
