<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.kas.index') }}" wire:navigate />
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $kas && $kas->exists ? 'Edit Kas' : 'Tambah Kas' }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg max-w-2xl mx-auto mb-6">
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                
                <flux:field>
                    <flux:label>Nama Kas</flux:label>
                    <flux:input wire:model="nama" placeholder="Masukkan nama kas" />
                    <flux:error name="nama" />
                </flux:field>

                <flux:field>
                    <flux:label>Keterangan</flux:label>
                    <flux:textarea wire:model="keterangan" rows="3" placeholder="Tambahkan keterangan (opsional)" />
                    <flux:error name="keterangan" />
                </flux:field>

                <flux:field>
                    <flux:label>Saldo Awal (Rp)</flux:label>
                    <flux:input type="number" wire:model="saldo" />
                    <flux:error name="saldo" />
                </flux:field>

                <div class="flex flex-col gap-4 pt-2">
                    <flux:checkbox wire:model="ada_tagihan" label="Ada Tagihan?" description="Kas ini memiliki integrasi tagihan" />
                    <flux:checkbox wire:model="tabungan" label="Tabungan?" description="Kas ini digunakan untuk tabungan siswa" />
                    <flux:checkbox wire:model="penjualan" label="Penjualan?" description="Kas ini digunakan untuk modul penjualan" />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <flux:button variant="ghost" href="{{ route('admin.kas.index') }}" wire:navigate>Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>

            </form>
        </div>
    </div>
</div>
