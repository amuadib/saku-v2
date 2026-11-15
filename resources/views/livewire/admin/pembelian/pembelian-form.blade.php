<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.pembelian.index') }}" wire:navigate />
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $pembelian && $pembelian->exists ? 'Edit Pembelian' : 'Transaksi Pembelian Baru' }}
            </h2>
        </div>
    </x-slot>

    <div
        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg max-w-5xl mx-auto mb-6"
        x-data="pembelianForm({
            initialItems: {{ Js::from($items) }},
            barangs: {{ Js::from($barangsFormatted) }},
        })"
    >
        <div class="p-6">
            <form @submit.prevent="submitForm" class="space-y-6">
                
                <!-- Master Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <flux:field>
                        <flux:label>Kode Pembelian</flux:label>
                        <flux:input wire:model="kode" placeholder="KODE TRANSAKSI" />
                        <flux:error name="kode" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Supplier</flux:label>
                        <flux:select wire:model.live="supplier_id" placeholder="Pilih Supplier...">
                            <flux:select.option value="">-- Pilih Supplier --</flux:select.option>
                            @foreach($suppliers as $supplier)
                                <flux:select.option value="{{ $supplier->id }}">{{ $supplier->nama }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="supplier_id" />
                    </flux:field>
                </div>

                <!-- Detail Items -->
                <div>
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Detail Barang</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Cari barang pada daftar dibawah ini. Jika belum ada, ketik nama dan tekan <strong>Enter</strong> atau klik <strong>+ Tambah sebagai barang baru</strong>.
                            </p>
                        </div>
                        <flux:button size="sm" variant="filled" type="button" @click="addRow()" icon="plus" class="shrink-0 self-start sm:self-auto">
                            Tambah Baris
                        </flux:button>
                    </div>

                    @if($errors->any())
                        <flux:callout variant="danger" class="mb-4" icon="x-circle">
                            <ul class="list-disc list-inside text-sm space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </flux:callout>
                    @endif

                    <div wire:ignore class="overflow-x-auto md:overflow-visible rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700/60 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3 min-w-[280px]">Barang</th>
                                    <th class="px-4 py-3 w-28">Jumlah</th>
                                    <th class="px-4 py-3 w-44">Harga Beli</th>
                                    <th class="px-4 py-3 w-36 text-right">Total</th>
                                    <th class="px-4 py-3 w-16 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="(item, index) in items" :key="item._uid">
                                    <tr :data-row-uid="item._uid" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 align-top">
                                        <!-- Barang (Tom Select) -->
                                        <td class="px-4 py-2.5">
                                            <div class="relative w-full">
                                                <select
                                                    :data-uid="item._uid"
                                                    x-init="initBarangSelect($el, item, index)"
                                                    class="w-full"
                                                >
                                                    <option value="">Cari nama, kode atau tambah baru...</option>
                                                </select>
                                                <template x-if="getError('items.' + index + '.barang_id')">
                                                    <p class="text-xs text-red-500 mt-1" x-text="getError('items.' + index + '.barang_id')"></p>
                                                </template>
                                            </div>
                                        </td>

                                        <!-- Jumlah -->
                                        <td class="px-4 py-2.5">
                                            <input
                                                type="number"
                                                min="1"
                                                data-field="jumlah"
                                                x-model.number="item.jumlah"
                                                @input="updateRowTotal(item)"
                                                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 tabular-nums [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                            />
                                            <template x-if="getError('items.' + index + '.jumlah')">
                                                <p class="text-xs text-red-500 mt-1" x-text="getError('items.' + index + '.jumlah')"></p>
                                            </template>
                                        </td>

                                        <!-- Harga Beli -->
                                        <td class="px-4 py-2.5">
                                            <div class="relative flex items-center">
                                                <span class="pointer-events-none absolute left-3 text-xs text-gray-400">Rp</span>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    data-field="harga"
                                                    x-model.number="item.harga"
                                                    @input="updateRowTotal(item)"
                                                    class="w-full pl-8 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 tabular-nums [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                                />
                                            </div>
                                            <template x-if="getError('items.' + index + '.harga')">
                                                <p class="text-xs text-red-500 mt-1" x-text="getError('items.' + index + '.harga')"></p>
                                            </template>
                                        </td>

                                        <!-- Total -->
                                        <td class="px-4 py-3.5 text-right font-medium text-gray-900 dark:text-gray-100 tabular-nums">
                                            <span x-text="formatRupiah(item.total)"></span>
                                        </td>

                                        <!-- Aksi -->
                                        <td class="px-4 py-2.5 text-center">
                                            <flux:button variant="danger" size="sm" icon="trash" type="button" @click="removeRow(index, item._uid)" />
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50/50 dark:bg-gray-700/30">
                                    <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Grand Total</td>
                                    <td colspan="2" class="px-4 py-3 font-bold text-lg text-green-600 dark:text-green-400 text-right pr-6 tabular-nums">
                                        <span x-text="formatRupiah(grandTotal)"></span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <flux:button variant="ghost" href="{{ route('admin.pembelian.index') }}" wire:navigate>Batal</flux:button>
                    <flux:button type="submit" variant="primary" x-bind:disabled="isSubmitting">
                        <span x-show="!isSubmitting">Simpan Transaksi Pembelian</span>
                        <span x-show="isSubmitting">Menyimpan...</span>
                    </flux:button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        const registerPembelianForm = () => {
            if (typeof Alpine === 'undefined') return;
            if (Alpine.pembelianFormRegistered) return;
            Alpine.pembelianFormRegistered = true;

            Alpine.data('pembelianForm', (config) => ({
                items: [],
                barangs: config.barangs || [],
                barangTsMap: new Map(),
                isSubmitting: false,
                grandTotal: 0,

                init() {
                    const rawItems = config.initialItems || [];
                    if (rawItems.length === 0) {
                        this.addRow();
                    } else {
                        this.items = rawItems.map(item => ({
                            _uid: item._uid || ('row_' + Math.random().toString(36).substr(2, 9)),
                            id: item.id || null,
                            barang_id: item.barang_id ? String(item.barang_id) : '',
                            jumlah: parseInt(item.jumlah, 10) || 1,
                            harga: parseFloat(item.harga) || 0,
                            total: (parseInt(item.jumlah, 10) || 1) * (parseFloat(item.harga) || 0),
                        }));
                    }
                    this.calculateGrandTotal();
                },

                calculateGrandTotal() {
                    this.grandTotal = this.items.reduce((sum, item) => {
                        const qty = Math.max(1, parseInt(item.jumlah, 10) || 1);
                        const prc = Math.max(0, parseFloat(item.harga) || 0);
                        return sum + (qty * prc);
                    }, 0);
                },

                formatRupiah(amount) {
                    const num = Math.round(Number(amount) || 0);
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
                },

                getError(field) {
                    if (this.$wire && this.$wire.errors && this.$wire.errors[field]) {
                        return this.$wire.errors[field];
                    }
                    return null;
                },

                updateRowTotal(item) {
                    const qty = Math.max(1, parseInt(item.jumlah, 10) || 1);
                    const harga = Math.max(0, parseFloat(item.harga) || 0);
                    item.jumlah = qty;
                    item.harga = harga;
                    item.total = qty * harga;
                    this.calculateGrandTotal();
                },

                addRow() {
                    const uid = 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6);
                    this.items.push({
                        _uid: uid,
                        id: null,
                        barang_id: '',
                        jumlah: 1,
                        harga: 0,
                        total: 0,
                    });
                    this.calculateGrandTotal();

                    this.$nextTick(() => {
                        const el = document.querySelector(`[data-uid="${uid}"]`);
                        if (el && el.tomselect) {
                            el.tomselect.focus();
                        }
                    });
                },

                removeRow(index, uid) {
                    this.destroyBarangSelect(uid);
                    this.items.splice(index, 1);
                    if (this.items.length === 0) {
                        this.addRow();
                    } else {
                        this.calculateGrandTotal();
                    }
                },

                destroyBarangSelect(uid) {
                    if (this.barangTsMap.has(uid)) {
                        try {
                            this.barangTsMap.get(uid).destroy();
                        } catch (e) {}
                        this.barangTsMap.delete(uid);
                    }
                },

                addBarangToGlobalList(newBarang, currentUid = null) {
                    const formatted = {
                        id: String(newBarang.id),
                        value: String(newBarang.id),
                        nama: newBarang.nama,
                        kode: newBarang.kode || '',
                        stok: parseInt(newBarang.stok, 10) || 0,
                        satuan: newBarang.satuan || 'PCS',
                        harga_beli: parseFloat(newBarang.harga_beli) || 0,
                    };

                    if (!this.barangs.some(b => String(b.id) === formatted.id)) {
                        this.barangs.push(formatted);
                    }

                    for (const [uid, ts] of this.barangTsMap.entries()) {
                        if (uid !== currentUid && ts && !ts.options[formatted.id]) {
                            ts.addOption(formatted);
                            ts.refreshOptions(false);
                        }
                    }
                },

                initBarangSelect(el, item, index) {
                    if (typeof TomSelect === 'undefined') return;

                    this.$nextTick(() => {
                        if (!el.isConnected) return;
                        if (el.tomselect) {
                            try { el.tomselect.destroy(); } catch (e) {}
                        }
                        this.destroyBarangSelect(item._uid);

                        const self = this;
                        const ts = new TomSelect(el, {
                            plugins: ['dropdown_input', 'clear_button'],
                            dropdownParent: 'body',
                            options: this.barangs,
                            valueField: 'id',
                            labelField: 'nama',
                            searchField: ['nama', 'kode'],
                            maxOptions: 50,
                            openOnFocus: true,
                            selectOnTab: true,
                            create: true,
                            createOnBlur: false,
                            placeholder: 'Cari nama, kode atau tambah baru...',
                            items: item.barang_id ? [String(item.barang_id)] : [],
                            render: {
                                option: (data, escape) => {
                                    const stok = parseInt(data.stok, 10) || 0;
                                    const stokClass = stok <= 0
                                        ? 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400'
                                        : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300';
                                    const stokLabel = `Stok: ${stok} ${escape(data.satuan || 'PCS')}`;
                                    const kodeHtml = data.kode
                                        ? `<span class="rounded bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 font-mono text-[11px] text-zinc-600 dark:text-zinc-300">${escape(data.kode)}</span>`
                                        : '';
                                    const hargaBeli = data.harga_beli > 0
                                        ? `<span class="text-xs text-zinc-500 dark:text-zinc-400">Harga Beli: Rp ${Number(data.harga_beli).toLocaleString('id-ID')}</span>`
                                        : '<span class="text-xs text-zinc-400 italic">Belum ada harga beli</span>';

                                    return `
                                        <div class="flex items-center justify-between gap-3 py-1">
                                             <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-medium truncate text-zinc-900 dark:text-zinc-100">${escape(data.nama)}</span>
                                                    ${kodeHtml}
                                                </div>
                                                <div class="mt-0.5">${hargaBeli}</div>
                                            </div>
                                            <span class="shrink-0 rounded-md px-2 py-0.5 text-xs font-medium ${stokClass}">
                                                ${stokLabel}
                                            </span>
                                        </div>
                                    `;
                                },
                                item: (data, escape) => {
                                    const stok = parseInt(data.stok, 10) || 0;
                                    return `<div class="truncate"><span class="font-medium">${escape(data.nama)}</span> <span class="text-zinc-500 dark:text-zinc-400 text-xs">(Stok: ${stok})</span></div>`;
                                },
                                option_create: (data, escape) => {
                                    return `
                                        <div class="create cursor-pointer p-2.5 text-emerald-600 dark:text-emerald-400 font-semibold hover:bg-emerald-50 dark:hover:bg-emerald-950/40 flex items-center gap-2 border-t border-dashed border-zinc-200 dark:border-zinc-700">
                                            <svg class="size-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            <span>+ Tambah sebagai barang baru: <strong class="underline">${escape(data.input)}</strong></span>
                                        </div>
                                    `;
                                },
                                no_results: (data, escape) => {
                                    return `
                                        <div class="no-results p-3 text-xs text-zinc-500 dark:text-zinc-400">
                                            Barang tidak ditemukan. Tekan <strong>Enter</strong> atau klik opsi di atas untuk membuat barang baru secara otomatis.
                                        </div>
                                    `;
                                },
                            },
                            create: async function(input, callback) {
                                const trimmed = input.trim();
                                if (!trimmed) {
                                    callback(false);
                                    return;
                                }

                                try {
                                    const newBarang = await self.$wire.quickCreateBarang(trimmed, index);
                                    if (newBarang && newBarang.id) {
                                        self.addBarangToGlobalList(newBarang, item._uid);
                                        callback({
                                            id: String(newBarang.id),
                                            value: String(newBarang.id),
                                            text: newBarang.nama,
                                            nama: newBarang.nama,
                                            kode: newBarang.kode || '',
                                            stok: newBarang.stok || 0,
                                            satuan: newBarang.satuan || 'PCS',
                                            harga_beli: newBarang.harga_beli || 0
                                        });

                                        item.barang_id = String(newBarang.id);
                                        item.harga = Number(newBarang.harga_beli) || 0;
                                        self.updateRowTotal(item);

                                        try {
                                            ts.setValue(String(newBarang.id), true);
                                        } catch (e) {}

                                        // Auto-focus on harga input so user can type purchase price immediately
                                        setTimeout(() => {
                                            const rowEl = document.querySelector(`[data-row-uid="${item._uid}"]`);
                                            if (rowEl) {
                                                const hargaInput = rowEl.querySelector('input[data-field="harga"]');
                                                if (hargaInput) {
                                                    hargaInput.focus();
                                                    hargaInput.select();
                                                }
                                            }
                                        }, 150);
                                    } else {
                                        callback(false);
                                    }
                                } catch (err) {
                                    console.error('Error creating barang:', err);
                                    callback(false);
                                }
                            },
                            onChange: (value) => {
                                const strVal = value ? String(value) : '';
                                item.barang_id = strVal;
                                if (strVal) {
                                    const found = self.barangs.find(b => String(b.id) === strVal);
                                    if (found) {
                                        item.harga = Number(found.harga_beli) || 0;
                                    }
                                } else {
                                    item.harga = 0;
                                }
                                self.updateRowTotal(item);

                                // If harga is 0, auto-focus harga input
                                if (Number(item.harga) === 0 && strVal) {
                                    setTimeout(() => {
                                        const rowEl = document.querySelector(`[data-row-uid="${item._uid}"]`);
                                        if (rowEl) {
                                            const hargaInput = rowEl.querySelector('input[data-field="harga"]');
                                            if (hargaInput) {
                                                hargaInput.focus();
                                                hargaInput.select();
                                            }
                                        }
                                    }, 150);
                                }
                            }
                        });

                        this.barangTsMap.set(item._uid, ts);
                    });
                },

                submitForm() {
                    if (!this.$wire.supplier_id) {
                        if (window.Flux && window.Flux.toast) {
                            window.Flux.toast('Silakan pilih supplier terlebih dahulu.', { variant: 'danger' });
                        } else {
                            alert('Silakan pilih supplier terlebih dahulu.');
                        }
                        return;
                    }

                    const validItems = this.items.filter(it => it.barang_id && String(it.barang_id).trim() !== '');
                    if (validItems.length === 0) {
                        if (window.Flux && window.Flux.toast) {
                            window.Flux.toast('Silakan pilih minimal satu barang.', { variant: 'danger' });
                        } else {
                            alert('Silakan pilih minimal satu barang.');
                        }
                        return;
                    }

                    const payloadItems = validItems.map(it => {
                        const qty = Math.max(1, parseInt(it.jumlah, 10) || 1);
                        const prc = Math.max(0, parseFloat(it.harga) || 0);
                        return {
                            id: it.id || null,
                            barang_id: String(it.barang_id),
                            jumlah: qty,
                            harga: prc,
                            total: qty * prc,
                        };
                    });

                    this.isSubmitting = true;
                    this.$wire.save(payloadItems).catch((error) => {
                        this.isSubmitting = false;
                        console.error('Error saving pembelian:', error);
                        if (window.Flux && window.Flux.toast) {
                            window.Flux.toast('Gagal menyimpan transaksi. Periksa kembali form input Anda.', { variant: 'danger' });
                        }
                    });
                }
            }));
        };

        if (typeof Alpine !== 'undefined') {
            registerPembelianForm();
        } else {
            document.addEventListener('alpine:init', registerPembelianForm);
        }
    })();
</script>
