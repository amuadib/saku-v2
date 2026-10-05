<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.barang.index') }}" wire:navigate />
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $barang && $barang->exists ? 'Edit Barang' : 'Tambah Barang' }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg max-w-2xl mx-auto mb-6">
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Kode Barang</flux:label>
                        <flux:input wire:model="kode" placeholder="Masukkan kode barang" />
                        <flux:error name="kode" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Jenis</flux:label>
                        <flux:input wire:model="jenis" placeholder="Contoh: INV" />
                        <flux:error name="jenis" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Nama Barang</flux:label>
                    <flux:input wire:model="nama" placeholder="Masukkan nama barang" />
                    <flux:error name="nama" />
                </flux:field>

                <flux:field>
                    <flux:label>Keterangan</flux:label>
                    <flux:textarea wire:model="keterangan" rows="3" placeholder="Tambahkan keterangan (opsional)" />
                    <flux:error name="keterangan" />
                </flux:field>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Harga Jual (Rp)</flux:label>
                        <flux:input type="number" wire:model="harga" />
                        <flux:error name="harga" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Harga Beli (Rp)</flux:label>
                        <flux:input type="number" wire:model="harga_beli" />
                        <flux:error name="harga_beli" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label>Stok Saat Ini</flux:label>
                        <flux:input type="number" wire:model="stok" />
                        <flux:error name="stok" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Satuan</flux:label>
                        <flux:input wire:model="satuan" placeholder="PCS" />
                        <flux:error name="satuan" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Stok Minimal</flux:label>
                        <flux:input type="number" wire:model="stok_minimal" />
                        <flux:error name="stok_minimal" />
                    </flux:field>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <flux:button variant="ghost" href="{{ route('admin.barang.index') }}" wire:navigate>Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>

            </form>
        </div>
    </div>
</div>
