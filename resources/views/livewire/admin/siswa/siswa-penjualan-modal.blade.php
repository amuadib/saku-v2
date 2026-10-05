<div>
    <flux:modal wire:model="showPenjualanModal" class="md:w-full max-w-5xl">
        <flux:heading>Transaksi Penjualan Baru - {{ $penjualan_siswa_nama }}</flux:heading>
        
        <form id="penjualan-form" wire:submit="savePenjualan" class="relative space-y-6 mt-4">
            <!-- Master Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <flux:field>
                    <flux:label>Kode Penjualan</flux:label>
                    <flux:input wire:model="penjualan_kode" placeholder="KODE TRANSAKSI" />
                    <flux:error name="penjualan_kode" />
                </flux:field>

                <flux:field>
                    <flux:label>Metode Pembayaran</flux:label>
                    <flux:select wire:model="penjualan_pembayaran">
                        <option value="tun">KAS / Tunai</option>
                        <option value="tab">Tabungan Siswa</option>
                        <option value="tag">Tagihan (Hutang)</option>
                    </flux:select>
                    <flux:error name="penjualan_pembayaran" />
                </flux:field>
            </div>

            <!-- Detail Items -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Detail Barang</h3>
                </div>

                @if($errors->has('penjualan_items'))
                    <flux:callout variant="danger" class="mb-4">
                        {{ $errors->first('penjualan_items') }}
                    </flux:callout>
                @endif

                <div class="overflow-visible">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3 w-1/3">Barang</th>
                                <th class="px-4 py-3 w-24">Jumlah</th>
                                <th class="px-4 py-3">Harga Jual</th>
                                <th class="px-4 py-3">Total</th>
                                <th class="px-4 py-3 w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($penjualan_items as $index => $item)
                                <tr class="border-b dark:border-gray-700" wire:key="item-{{ $index }}">
                                    <td class="px-4 py-2">
                                        <div wire:ignore>
                                            <select x-data="{
                                                    ts: null,
                                                    val: @entangle('penjualan_items.'.$index.'.barang_id').live
                                                }" 
                                                x-init="
                                                    ts = new TomSelect($el, { plugins: ['dropdown_input'] });
                                                    ts.on('change', function(v) { val = v; });
                                                    $watch('val', value => {
                                                        if(ts.getValue() !== value) {
                                                            ts.setValue(value, true);
                                                        }
                                                    });
                                                " 
                                                placeholder="Pilih...">
                                                <option value="">-- Barang --</option>
                                                @foreach($barangs as $b)
                                                    <option value="{{ $b->id }}">{{ $b->nama }} (Stok: {{ $b->stok }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('penjualan_items.'.$index.'.barang_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="px-4 py-2">
                                        <flux:input type="number" wire:model.live="penjualan_items.{{ $index }}.jumlah" min="1" />
                                        @error('penjualan_items.'.$index.'.jumlah') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="px-4 py-2">
                                        <flux:input type="number" wire:model.live="penjualan_items.{{ $index }}.harga" min="0" />
                                        @error('penjualan_items.'.$index.'.harga') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="px-4 py-2 font-medium">
                                        Rp {{ number_format($item['total'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <flux:button variant="danger" size="sm" icon="trash" wire:click="removePenjualanItem({{ $index }})" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Grand Total</td>
                                <td colspan="2" class="px-4 py-3 font-bold text-lg text-green-600 dark:text-green-400">
                                    Rp {{ number_format($penjualan_total, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Simpan Transaksi</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Peringatan -->
    <flux:modal wire:model="showWarningModal" class="max-w-md">
        <div class="flex flex-col items-center justify-center text-center space-y-4 py-4">
            <div class="rounded-full bg-orange-100 p-3 text-orange-500 dark:bg-orange-900/50 dark:text-orange-400">
                <flux:icon.exclamation-triangle class="h-8 w-8" />
            </div>
            <div>
                <flux:heading size="lg">Data Barang Kosong</flux:heading>
                <p class="text-sm text-gray-500 mt-2">
                    Belum ada data barang untuk lembaga siswa ini. Transaksi Penjualan tidak dapat dilakukan.
                </p>
            </div>
            <div class="pt-4">
                <flux:modal.close>
                    <flux:button variant="primary">Mengerti</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
