<div>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.tabungan.index') }}" wire:navigate />
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Riwayat Tabungan: {{ optional($tabungan->siswa)->nama ?? '-' }}
                    </h2>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                        @if (optional($tabungan->siswa)->nis)
                            <span>NIS: <strong class="text-zinc-700 dark:text-zinc-300 font-medium">{{ $tabungan->siswa->nis }}</strong></span>
                        @endif
                        @if (optional($tabungan->kas)->nama)
                            <span>• Kas: <strong class="text-zinc-700 dark:text-zinc-300 font-medium">{{ $tabungan->kas->nama }}</strong></span>
                        @endif
                        <span>• Saldo Saat Ini: <strong class="font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format((float) $tabungan->saldo, 0, ',', '.') }}</strong></span>
                    </div>
                </div>
            </div>
            <flux:button href="{{ route('admin.tabungan.index') }}" wire:navigate icon="arrow-left" variant="ghost">Kembali</flux:button>
        </div>
    </x-slot>

    <!-- Stat Card Saldo (Compact banner) -->
    <div class="bg-white dark:bg-zinc-900/70 shadow-xs sm:rounded-2xl border border-zinc-200 dark:border-zinc-800 p-4 sm:p-5 mb-6 flex items-center justify-between backdrop-blur-xl">
        <div class="flex items-center gap-4">
            <div class="size-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <flux:icon name="banknotes" class="size-6" />
            </div>
            <div>
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Saldo Tabungan Saat Ini</span>
                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                    Rp {{ number_format((float) $tabungan->saldo, 0, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="hidden sm:block text-right text-xs text-zinc-400 dark:text-zinc-500">
            Total Transaksi: <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $transaksis->total() }}</span>
        </div>
    </div>

    <!-- Main Table Container -->
    <div class="bg-white dark:bg-gray-800 dark:bg-zinc-900/70 shadow-xs sm:rounded-2xl border border-gray-200 dark:border-gray-700 dark:border-zinc-800 mb-6 backdrop-blur-xl">
        <div class="p-4 sm:p-6">
            <!-- Dual View: Table vs Mobile Cards -->
            <x-responsive-view>
                <x-slot name="desktop">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Tanggal</flux:table.column>
                            <flux:table.column>Kode</flux:table.column>
                            <flux:table.column>Jumlah</flux:table.column>
                            <flux:table.column>Keterangan</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($transaksis as $transaksi)
                                @php
                                    $isMasuk = str_starts_with((string) $transaksi->kode, 'M')
                                        || str_starts_with((string) $transaksi->kode, 'STB')
                                        || str_starts_with((string) $transaksi->kode, 'TRX');
                                @endphp
                                <flux:table.row>
                                    <flux:table.cell>
                                        <span class="text-zinc-700 dark:text-zinc-300">{{ $transaksi->created_at->format('d F Y H:i:s') }}</span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span class="font-mono text-xs text-zinc-600 dark:text-zinc-400">{{ $transaksi->kode }}</span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex items-center gap-1.5 font-medium {{ $isMasuk ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                            <flux:icon name="{{ $isMasuk ? 'arrow-trending-up' : 'arrow-trending-down' }}" class="size-4" />
                                            <span>{{ $isMasuk ? '+' : '-' }}Rp {{ number_format(abs((float) $transaksi->jumlah), 0, ',', '.') }}</span>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span class="text-zinc-700 dark:text-zinc-300">{{ $transaksi->keterangan ?? '-' }}</span>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4" class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                                        Belum ada riwayat transaksi.
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </x-slot>

                <x-slot name="mobile">
                    @forelse ($transaksis as $transaksi)
                        @php
                            $isMasuk = str_starts_with((string) $transaksi->kode, 'M')
                                || str_starts_with((string) $transaksi->kode, 'STB')
                                || str_starts_with((string) $transaksi->kode, 'TRX');
                        @endphp
                        <x-mobile-card
                            :title="$transaksi->keterangan ?: ($isMasuk ? 'Setoran Tabungan' : 'Penarikan Tabungan')"
                            :subtitle="$transaksi->kode . ' • ' . $transaksi->created_at->format('d M Y, H:i')"
                            :icon="$isMasuk ? 'arrow-trending-up' : 'arrow-trending-down'"
                            :avatar-color="$isMasuk ? 'emerald' : 'rose'"
                        >
                            <div class="flex items-center justify-between pt-1 border-t border-zinc-100 dark:border-zinc-700/80">
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">Nominal Transaksi</span>
                                <span class="font-bold text-sm {{ $isMasuk ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $isMasuk ? '+' : '-' }}Rp {{ number_format(abs((float) $transaksi->jumlah), 0, ',', '.') }}
                                </span>
                            </div>
                        </x-mobile-card>
                    @empty
                        <div class="text-center py-8 text-zinc-500 dark:text-zinc-400 text-sm">
                            Belum ada riwayat transaksi.
                        </div>
                    @endforelse
                </x-slot>
            </x-responsive-view>

            <div class="mt-4">
                {{ $transaksis->links() }}
            </div>
        </div>
    </div>
</div>
