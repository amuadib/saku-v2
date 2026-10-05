<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Data Tabungan
            </h2>
            <flux:button variant="primary" href="{{ route('admin.tabungan.create') }}" wire:navigate>Tambah Tabungan</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4 flex gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama atau NIS siswa..." icon="magnifying-glass" class="w-full sm:w-1/3" />
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Siswa</flux:table.column>
                    <flux:table.column>Kas Tabungan</flux:table.column>
                    <flux:table.column>Saldo (Rp)</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($tabungans as $tabungan)
                        <flux:table.row>
                            <flux:table.cell>
                                <div class="font-medium">{{ optional($tabungan->siswa)->nama ?? '-' }}</div>
                                <div class="text-xs text-gray-500">NIS: {{ optional($tabungan->siswa)->nis ?? '-' }}</div>
                            </flux:table.cell>
                            <flux:table.cell>{{ optional($tabungan->kas)->nama ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ number_format($tabungan->saldo, 0, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" href="{{ route('admin.tabungan.edit', $tabungan->id) }}" wire:navigate icon="pencil" color="yellow"></flux:button>
                                <flux:button size="sm" variant="danger" wire:click="delete('{{ $tabungan->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus data tabungan ini?" icon="trash"></flux:button>
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
                {{ $tabungans->links() }}
            </div>
        </div>
    </div>
</div>
