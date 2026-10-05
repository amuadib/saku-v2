<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Tambah Transaksi Manual
            </h2>
            <flux:button variant="outline" href="{{ route('admin.transaksi.index') }}" wire:navigate>Kembali</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6 max-w-2xl mx-auto mt-6">
        <div class="p-6">
            <form wire:submit="save">
                <div class="space-y-6">
                    
                    <flux:field>
                        <flux:label>Kas / Rekening</flux:label>
                        <flux:select wire:model="kas_id" placeholder="Pilih Kas">
                            @foreach($kas_items as $k)
                                <flux:select.option value="{{ $k->id }}">{{ $k->nama }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="kas_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Jumlah (Nominal)</flux:label>
                        <flux:input type="number" wire:model="jumlah" placeholder="Gunakan minus (-) untuk pengeluaran" />
                        <flux:error name="jumlah" />
                        <p class="text-xs text-gray-500 mt-1">Gunakan angka positif untuk pemasukan, dan angka negatif (contoh: -50000) untuk pengeluaran.</p>
                    </flux:field>

                    <flux:field>
                        <flux:label>Keterangan</flux:label>
                        <flux:textarea wire:model="keterangan" rows="3" placeholder="Deskripsi transaksi manual..." />
                        <flux:error name="keterangan" />
                    </flux:field>

                    <div class="flex justify-end pt-4">
                        <flux:button type="submit" variant="primary">Simpan Transaksi</flux:button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
