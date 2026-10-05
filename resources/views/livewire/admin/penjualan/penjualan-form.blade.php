<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.penjualan.index') }}" wire:navigate />
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-3">
                {{ $penjualan && $penjualan->exists ? 'Detail Penjualan' : 'Transaksi Penjualan Baru' }}
                @if($penjualan && $penjualan->exists && $penjualan->status === 'batal')
                    <flux:badge color="red" size="sm">Status: Batal (Telah Direversal)</flux:badge>
                @endif
            </h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg max-w-5xl mx-auto mb-6">
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                
                <!-- Master Info -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <flux:field>
                        <flux:label>Kode Penjualan</flux:label>
                        <flux:input wire:model="kode" placeholder="KODE TRANSAKSI" :disabled="$penjualan && $penjualan->exists" />
                        <flux:error name="kode" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Siswa Pembeli</flux:label>
                        <flux:select wire:model.live="siswa_id" searchable placeholder="Pilih Siswa..." :disabled="$penjualan && $penjualan->exists">
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($siswas as $siswa)
                                <option value="{{ $siswa->id }}">{{ $siswa->nis }} - {{ $siswa->nama }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="siswa_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Metode Pembayaran</flux:label>
                        <flux:select wire:model="pembayaran" :disabled="$penjualan && $penjualan->exists">
                            <option value="tun">KAS / Tunai</option>
                            <option value="tab">Tabungan Siswa</option>
                            <option value="tag">Tagihan (Hutang)</option>
                        </flux:select>
                        <flux:error name="pembayaran" />
                    </flux:field>
                </div>

                <!-- Detail Items -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Detail Barang</h3>
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
                                    <th class="px-4 py-3">Harga Jual</th>
                                    <th class="px-4 py-3">Total</th>
                                    @if(!($penjualan && $penjualan->exists))
                                        <th class="px-4 py-3 w-16">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $index => $item)
                                    <tr class="border-b dark:border-gray-700" wire:key="item-{{ $index }}">
                                        <td class="px-4 py-2">
                                            <flux:select wire:model.live="items.{{ $index }}.barang_id" searchable placeholder="Pilih..." :disabled="$penjualan && $penjualan->exists">
                                                <option value="">-- Barang --</option>
                                                @foreach($barangs as $b)
                                                    <option value="{{ $b->id }}">{{ $b->nama }} (Stok: {{ $b->stok }})</option>
                                                @endforeach
                                            </flux:select>
                                            @error('items.'.$index.'.barang_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </td>
                                        <td class="px-4 py-2">
                                            <flux:input type="number" wire:model.live="items.{{ $index }}.jumlah" min="1" :disabled="$penjualan && $penjualan->exists" />
                                            @error('items.'.$index.'.jumlah') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </td>
                                        <td class="px-4 py-2">
                                            <flux:input type="number" wire:model.live="items.{{ $index }}.harga" min="0" :disabled="$penjualan && $penjualan->exists" />
                                            @error('items.'.$index.'.harga') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </td>
                                        <td class="px-4 py-2 font-medium">
                                            Rp {{ number_format($item['total'], 0, ',', '.') }}
                                        </td>
                                        @if(!($penjualan && $penjualan->exists))
                                            <td class="px-4 py-2 text-center">
                                                <flux:button variant="danger" size="sm" icon="trash" wire:click="removeItem({{ $index }})" />
                                            </td>
                                        @endif
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
                    <flux:button variant="ghost" href="{{ route('admin.penjualan.index') }}" wire:navigate>Kembali</flux:button>
                    @if(!($penjualan && $penjualan->exists))
                        <flux:button type="submit" variant="primary">Simpan Transaksi Penjualan</flux:button>
                    @endif
                </div>

            </form>
        </div>
    </div>
</div>
