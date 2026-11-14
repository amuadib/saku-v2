<div>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.tabungan.index') }}" wire:navigate />
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Riwayat Tabungan: {{ optional($tabungan->siswa)->nama ?? '-' }}
                    </h2>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                        @if (optional($tabungan->siswa)->nis)
                            <span>NIS: <strong class="text-gray-700 dark:text-gray-300 font-medium">{{ $tabungan->siswa->nis }}</strong></span>
                        @endif
                        @if (optional($tabungan->kas)->nama)
                            <span>• Kas: <strong class="text-gray-700 dark:text-gray-300 font-medium">{{ $tabungan->kas->nama }}</strong></span>
                        @endif
                        <span>• Saldo Saat Ini: <strong class="font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format((float) $tabungan->saldo, 0, ',', '.') }}</strong></span>
                    </div>
                </div>
            </div>
            <flux:button href="{{ route('admin.tabungan.index') }}" wire:navigate icon="arrow-left" variant="ghost">Kembali</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700 mb-6">
        <div class="p-6">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Tanggal</flux:table.column>
                    <flux:table.column>Kode</flux:table.column>
                    <flux:table.column>Jumlah</flux:table.column>
                    <flux:table.column>Keterangan</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($transaksis as $transaksi)
                        <flux:table.row>
                            <flux:table.cell>
                                <span class="text-gray-700 dark:text-gray-300">{{ $transaksi->created_at->format('d F Y H:i:s') }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="font-mono text-xs text-gray-600 dark:text-gray-400">{{ $transaksi->kode }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $isMasuk = str_starts_with((string) $transaksi->kode, 'M')
                                        || str_starts_with((string) $transaksi->kode, 'STB')
                                        || str_starts_with((string) $transaksi->kode, 'TRX');
                                @endphp
                                <div class="flex items-center gap-1.5 font-medium {{ $isMasuk ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    <flux:icon name="{{ $isMasuk ? 'arrow-trending-up' : 'arrow-trending-down' }}" class="size-4" />
                                    <span>{{ $isMasuk ? '+' : '-' }}Rp {{ number_format(abs((float) $transaksi->jumlah), 0, ',', '.') }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="text-gray-700 dark:text-gray-300">{{ $transaksi->keterangan ?? '-' }}</span>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                Belum ada riwayat transaksi.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $transaksis->links() }}
            </div>
        </div>
    </div>
</div>
