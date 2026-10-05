<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Data Supplier
            </h2>
            <flux:button variant="primary" href="{{ route('admin.supplier.create') }}" wire:navigate>Tambah Supplier</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4 flex gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama supplier..." icon="magnifying-glass" class="w-full sm:w-1/3" />
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Nama</flux:table.column>
                    <flux:table.column>No. HP</flux:table.column>
                    <flux:table.column>Alamat</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($suppliers as $supplier)
                        <flux:table.row>
                            <flux:table.cell>{{ $supplier->nama }}</flux:table.cell>
                            <flux:table.cell>{{ $supplier->hp ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $supplier->alamat ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" href="{{ route('admin.supplier.edit', $supplier->id) }}" wire:navigate icon="pencil" color="yellow"></flux:button>
                                <flux:button size="sm" variant="danger" wire:click="delete('{{ $supplier->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus supplier ini?" icon="trash"></flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center">Data tidak ditemukan.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>
</div>
