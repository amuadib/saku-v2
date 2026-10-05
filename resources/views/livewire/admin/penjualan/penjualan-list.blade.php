<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Data Penjualan
            </h2>
            <flux:button variant="primary" href="{{ route('admin.penjualan.create') }}" wire:navigate>Transaksi Penjualan Baru</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4 flex gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari kode transaksi, atau nama siswa..." icon="magnifying-glass" class="w-full sm:w-1/3" />
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Kode / Tanggal</flux:table.column>
                    <flux:table.column>Siswa</flux:table.column>
                    <flux:table.column>Total Transaksi</flux:table.column>
                    <flux:table.column>Tipe Pembayaran</flux:table.column>
                    <flux:table.column>Petugas</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($penjualans as $penjualan)
                        <flux:table.row>
                            <flux:table.cell>
                                <div class="font-medium flex items-center gap-2">
                                    {{ $penjualan->kode ?? '-' }}
                                    @if(isset($penjualan->status) && $penjualan->status === 'batal')
                                        <flux:badge color="red" size="sm">Batal</flux:badge>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">{{ $penjualan->created_at->format('d/m/Y H:i') }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="font-medium">{{ optional($penjualan->siswa)->nama ?? '-' }}</div>
                                <div class="text-xs text-gray-500">NIS: {{ optional($penjualan->siswa)->nis ?? '-' }}</div>
                            </flux:table.cell>
                            <flux:table.cell>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>
                                @if($penjualan->pembayaran == 'tun')
                                    <flux:badge color="green">Tunai</flux:badge>
                                @elseif($penjualan->pembayaran == 'tab')
                                    <flux:badge color="blue">Tabungan</flux:badge>
                                @elseif($penjualan->pembayaran == 'tag')
                                    <flux:badge color="red">Tagihan</flux:badge>
                                @else
                                    <flux:badge color="gray">{{ $penjualan->pembayaran }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ optional($penjualan->petugas)->authable->nama ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" href="{{ route('admin.penjualan.edit', $penjualan->id) }}" wire:navigate icon="eye" tooltip="Detail Penjualan"></flux:button>
                                <flux:button size="sm" variant="danger" wire:click="delete('{{ $penjualan->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus transaksi ini beserta detailnya?" icon="trash"></flux:button>
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
                {{ $penjualans->links() }}
            </div>
        </div>
    </div>
</div>
