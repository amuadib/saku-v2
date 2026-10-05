<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.supplier.index') }}" wire:navigate />
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $supplier && $supplier->exists ? 'Edit Supplier' : 'Tambah Supplier' }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg max-w-2xl mx-auto mb-6">
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                
                <flux:field>
                    <flux:label>Nama Supplier</flux:label>
                    <flux:input wire:model="nama" placeholder="Masukkan nama supplier" />
                    <flux:error name="nama" />
                </flux:field>

                <flux:field>
                    <flux:label>No. HP (Opsional)</flux:label>
                    <flux:input wire:model="hp" placeholder="Contoh: 081234567890" />
                    <flux:error name="hp" />
                </flux:field>

                <flux:field>
                    <flux:label>Alamat (Opsional)</flux:label>
                    <flux:textarea wire:model="alamat" rows="3" placeholder="Masukkan alamat supplier" />
                    <flux:error name="alamat" />
                </flux:field>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <flux:button variant="ghost" href="{{ route('admin.supplier.index') }}" wire:navigate>Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>

            </form>
        </div>
    </div>
</div>
