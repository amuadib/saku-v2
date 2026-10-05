<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.periode.index') }}" wire:navigate />
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $periode && $periode->exists ? 'Edit Periode' : 'Tambah Periode' }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg max-w-2xl mx-auto mb-6">
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                
                <flux:field>
                    <flux:label>Nama Periode</flux:label>
                    <flux:input wire:model="nama" placeholder="Contoh: Tahun Ajaran 2024/2025" />
                    <flux:error name="nama" />
                </flux:field>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Tanggal Mulai</flux:label>
                        <flux:input type="date" wire:model="mulai" />
                        <flux:error name="mulai" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tanggal Selesai</flux:label>
                        <flux:input type="date" wire:model="selesai" />
                        <flux:error name="selesai" />
                    </flux:field>
                </div>

                <div class="flex flex-col gap-4 pt-2">
                    <flux:checkbox wire:model="aktif" label="Aktif?" description="Tandai jika periode ini sedang berjalan" />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <flux:button variant="ghost" href="{{ route('admin.periode.index') }}" wire:navigate>Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>

            </form>
        </div>
    </div>
</div>
