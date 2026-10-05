<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.pembelian.index') }}" wire:navigate />
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $pembelian && $pembelian->exists ? 'Edit Pembelian' : 'Transaksi Pembelian Baru' }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg max-w-5xl mx-auto mb-6">
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                
                <!-- Master Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <flux:field>
                        <flux:label>Kode Pembelian</flux:label>
                        <flux:input wire:model="kode" placeholder="KODE TRANSAKSI" />
                        <flux:error name="kode" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Supplier</flux:label>
                        <flux:select wire:model="supplier_id" searchable placeholder="Pilih Supplier...">
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->nama }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="supplier_id" />
                    </flux:field>
                </div>

                <!-- Detail Items -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Detail Barang</h3>
                        <flux:button size="sm" variant="filled" wire:click="addItem" icon="plus">Tambah Baris</flux:button>
                    </div>

                    @if($errors->has('items'))
                        <flux:callout variant="danger" class="mb-4">
                            {{ $errors->first('items') }}
                        </flux:callout>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3 w-1/3">Barang</th>
                                    <th class="px-4 py-3 w-24">Jumlah</th>
                                    <th class="px-4 py-3">Harga Beli</th>
                                    <th class="px-4 py-3">Total</th>
                                    <th class="px-4 py-3 w-16">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $index => $item)
                                    <tr class="border-b dark:border-gray-700" wire:key="item-{{ $index }}">
                                        <td class="px-4 py-2">
                                            <flux:select wire:model.live="items.{{ $index }}.barang_id" searchable placeholder="Pilih...">
                                                <option value="">-- Barang --</option>
                                                @foreach($barangs as $b)
                                                    <option value="{{ $b->id }}">{{ $b->nama }} (Stok: {{ $b->stok }})</option>
                                                @endforeach
                                            </flux:select>
                                            @error('items.'.$index.'.barang_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </td>
                                        <td class="px-4 py-2">
                                            <flux:input type="number" wire:model.live="items.{{ $index }}.jumlah" min="1" />
                                            @error('items.'.$index.'.jumlah') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </td>
                                        <td class="px-4 py-2">
                                            <flux:input type="number" wire:model.live="items.{{ $index }}.harga" min="0" />
                                            @error('items.'.$index.'.harga') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </td>
                                        <td class="px-4 py-2 font-medium">
                                            Rp {{ number_format($item['total'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <flux:button variant="danger" size="sm" icon="trash" wire:click="removeItem({{ $index }})" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Grand Total</td>
                                    <td colspan="2" class="px-4 py-3 font-bold text-lg text-green-600 dark:text-green-400">
                                        Rp {{ number_format($total_transaksi, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <flux:button variant="ghost" href="{{ route('admin.pembelian.index') }}" wire:navigate>Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan Transaksi Pembelian</flux:button>
                </div>

            </form>
        </div>
    </div>
</div>
