<div>
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Riwayat Tabungan: {{ optional($tabungan->siswa)->nama ?? '-' }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Saldo Saat Ini: <span class="font-medium text-emerald-600">Rp {{ number_format($tabungan->saldo, 0, ',', '.') }}</span>
            </p>
        </div>
        <flux:button href="{{ route('admin.tabungan.index') }}" wire:navigate icon="arrow-left" variant="primary">Kembali</flux:button>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
        <div class="p-6 text-gray-900">
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
                                {{ $transaksi->created_at->format('d F Y H:i:s') }}
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $transaksi->kode }}
                            </flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $isMasuk = str_starts_with($transaksi->kode, 'M');
                                @endphp
                                <div class="flex items-center gap-1.5 {{ $isMasuk ? 'text-emerald-600' : 'text-red-600' }}">
                                    <flux:icon name="{{ $isMasuk ? 'arrow-trending-up' : 'arrow-trending-down' }}" class="size-4" />
                                    <span>Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $transaksi->keterangan ?? '-' }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center py-8 text-gray-500">
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
