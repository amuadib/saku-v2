<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Data Pembelian
            </h2>
            <flux:button variant="primary" href="{{ route('admin.pembelian.create') }}" wire:navigate>Tambah Pembelian</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4 flex gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari kode transaksi atau supplier..." icon="magnifying-glass" class="w-full sm:w-1/3" />
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Kode / Tanggal</flux:table.column>
                    <flux:table.column>Supplier</flux:table.column>
                    <flux:table.column>Total Transaksi</flux:table.column>
                    <flux:table.column>Petugas</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($pembelians as $pembelian)
                        <flux:table.row>
                            <flux:table.cell>
                                <div class="font-medium">{{ $pembelian->kode }}</div>
                                <div class="text-xs text-gray-500">{{ $pembelian->created_at->format('d/m/Y H:i') }}</div>
                            </flux:table.cell>
                            <flux:table.cell>{{ optional($pembelian->supplier)->nama ?? '-' }}</flux:table.cell>
                            <flux:table.cell>Rp {{ number_format($pembelian->total, 0, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>{{ optional($pembelian->petugas)->username ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" href="{{ route('admin.pembelian.edit', $pembelian->id) }}" wire:navigate icon="pencil" color="yellow"></flux:button>
                                <flux:button size="sm" variant="danger" wire:click="delete('{{ $pembelian->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus transaksi ini beserta seluruh detailnya?" icon="trash"></flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center">Data tidak ditemukan.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $pembelians->links() }}
            </div>
        </div>
    </div>
</div>
