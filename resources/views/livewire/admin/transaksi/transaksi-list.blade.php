<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Data Transaksi (Buku Besar)
            </h2>
            <flux:button variant="primary" href="{{ route('admin.transaksi.create') }}" wire:navigate>Tambah Manual</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4 flex gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari kode atau keterangan..." icon="magnifying-glass" class="w-full sm:w-1/3" />
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Kode / Waktu</flux:table.column>
                    <flux:table.column>Sumber (Jenis)</flux:table.column>
                    <flux:table.column>Nominal</flux:table.column>
                    <flux:table.column>Keterangan</flux:table.column>
                    <flux:table.column>Petugas</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($transaksis as $transaksi)
                        <flux:table.row>
                            <flux:table.cell>
                                <div class="font-medium">{{ $transaksi->kode ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $transaksi->created_at->format('d/m/Y H:i') }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if (class_basename($transaksi->transable_type) === 'Tabungan')
                                    <flux:badge color="green">Tabungan</flux:badge>
                                @elseif (class_basename($transaksi->transable_type) === 'Tagihan')
                                    <flux:badge color="red">Tagihan</flux:badge>
                                @elseif (class_basename($transaksi->transable_type) === 'Penjualan')
                                    <flux:badge color="blue">Penjualan</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>
                                    {{ $transaksi->keterangan }}
                            </flux:table.cell>
                            <flux:table.cell>{{ optional($transaksi->petugas)->authable->nama ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @if(!str_starts_with($transaksi->keterangan, '[REVERSAL]') && !in_array($transaksi->kode, $reversedOriginalKodes))
                                    <flux:button size="sm" variant="danger" wire:click="reversal('{{ $transaksi->id }}')" wire:confirm="Apakah Anda yakin ingin melakukan reversal transaksi ini? Saldo akan dikembalikan." icon="arrow-uturn-left"></flux:button>
                                @elseif(in_array($transaksi->kode, $reversedOriginalKodes))
                                    <flux:badge color="red" size="sm">Reversed</flux:badge>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center">Data tidak ditemukan.</flux:table.cell>
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
