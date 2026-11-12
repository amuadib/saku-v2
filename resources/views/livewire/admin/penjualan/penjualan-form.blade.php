<div>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.penjualan.index') }}" wire:navigate />
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-3">
                    {{ $isReadOnly ? 'Detail Penjualan' : 'Transaksi Penjualan Baru' }}
                    @if($penjualan && $penjualan->exists && $penjualan->status === 'batal')
                        <flux:badge color="red" size="sm">Status: Batal (Telah Direversal)</flux:badge>
                    @endif
                </h2>
            </div>

            @if(! $isReadOnly)
                <div class="hidden md:flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                    <span class="inline-flex items-center gap-1 rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 px-2 py-1">
                        <kbd class="font-mono font-semibold text-zinc-700 dark:text-zinc-200">F1</kbd> Siswa
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 px-2 py-1">
                        <kbd class="font-mono font-semibold text-zinc-700 dark:text-zinc-200">F2</kbd> Cari Barang
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 px-2 py-1">
                        <kbd class="font-mono font-semibold text-zinc-700 dark:text-zinc-200">Ctrl+Enter</kbd> Simpan
                    </span>
                </div>
            @endif
        </div>
    </x-slot>

    <script>
        window.createPenjualanPos = function (config) {
            return {
                isReadOnly: Boolean(config.isReadOnly),
                siswas: Array.isArray(config.siswas) ? config.siswas : [],
                allBarangs: Array.isArray(config.barangs) ? config.barangs : [],
                kode: config.initialKode || '',
                siswa_id: config.initialSiswaId || '',
                pembayaran: config.initialPembayaran || 'tun',
                items: [],
                grandTotal: 0,
                submitting: false,
                errors: {},
                uidCounter: 1,
                highlightedUid: null,
                siswaTs: null,
                barangTsMap: new Map(),
                pendingFocusUid: null,
                _syncingBarang: false,
                _highlightTimer: null,

                init() {
                    const initialItems = Array.isArray(config.initialItems) ? config.initialItems : [];
                    if (initialItems.length > 0) {
                        this.items = initialItems.map((it) => {
                            const qty = parseInt(it.jumlah, 10) || 1;
                            const harga = parseInt(it.harga, 10) || 0;
                            return {
                                _uid: this.nextUid(),
                                id: it.id || null,
                                barang_id: it.barang_id ? String(it.barang_id) : '',
                                jumlah: qty,
                                harga: harga,
                                total: qty * harga,
                            };
                        });
                    }

                    if (!this.isReadOnly) {
                        const hasEmptyTrailing = this.items.length > 0 && !this.items[this.items.length - 1].barang_id;
                        if (!hasEmptyTrailing) {
                            this.addEmptyRow(false);
                        }
                    }

                    this.recalculateGrandTotal();
                },

                nextUid() {
                    return 'row-' + (this.uidCounter++);
                },

                get selectedSiswa() {
                    if (!this.siswa_id) {
                        return null;
                    }
                    return this.siswas.find((s) => String(s.id) === String(this.siswa_id)) || null;
                },

                get filledItems() {
                    return this.items.filter((it) => Boolean(it.barang_id));
                },

                get totalQty() {
                    return this.filledItems.reduce((sum, it) => sum + (parseInt(it.jumlah, 10) || 0), 0);
                },

                get isTabunganInsufficient() {
                    if (this.pembayaran !== 'tab' || !this.selectedSiswa) {
                        return false;
                    }
                    return (parseInt(this.selectedSiswa.tabungan_saldo, 10) || 0) < this.grandTotal;
                },

                filteredBarangs() {
                    const siswa = this.selectedSiswa;
                    if (siswa && siswa.lembaga_id !== null && siswa.lembaga_id !== undefined && siswa.lembaga_id !== '') {
                        return this.allBarangs.filter((b) => String(b.lembaga_id) === String(siswa.lembaga_id));
                    }
                    return this.allBarangs;
                },

                getBarangById(id) {
                    if (!id) {
                        return null;
                    }
                    return this.allBarangs.find((b) => String(b.id) === String(id)) || null;
                },

                getBarangOptionsForRow(currentBarangId) {
                    return this.filteredBarangs().map((b) => ({
                        ...b,
                        disabled: (parseInt(b.stok, 10) || 0) <= 0 && String(b.id) !== String(currentBarangId || ''),
                    }));
                },

                formatRupiah(amount) {
                    const num = Math.round(Number(amount) || 0);
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
                },

                escapeHtml(str) {
                    return String(str ?? '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
                },

                notify(message, variant = 'success') {
                    if (window.Flux && typeof window.Flux.toast === 'function') {
                        window.Flux.toast(message, variant);
                    }
                },

                flashRow(uid) {
                    this.highlightedUid = uid;
                    if (this._highlightTimer) {
                        clearTimeout(this._highlightTimer);
                    }
                    this._highlightTimer = setTimeout(() => {
                        this.highlightedUid = null;
                    }, 900);
                },

                initSiswaSelect(el) {
                    if (typeof TomSelect === 'undefined') {
                        return;
                    }
                    this.$nextTick(() => {
                        if (!el.isConnected) {
                            return;
                        }
                        if (el.tomselect) {
                            el.tomselect.destroy();
                        }
                        if (this.siswaTs) {
                            this.siswaTs.destroy();
                            this.siswaTs = null;
                        }

                        const plugins = this.isReadOnly ? [] : ['dropdown_input', 'clear_button'];

                        this.siswaTs = new TomSelect(el, {
                            plugins: plugins,
                            dropdownParent: 'body',
                            options: this.siswas,
                            valueField: 'id',
                            labelField: 'nama',
                            searchField: ['nama', 'nis', 'kelas'],
                            maxOptions: 60,
                            openOnFocus: true,
                            selectOnTab: true,
                            placeholder: 'Cari nama, NIS, atau kelas siswa...',
                            items: this.siswa_id ? [String(this.siswa_id)] : [],
                            render: {
                                option: (data, escape) => {
                                    const kelasBadge = data.kelas
                                        ? `<span class="inline-flex items-center rounded bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 text-[11px] font-medium text-zinc-600 dark:text-zinc-300">${escape(data.kelas)}</span>`
                                        : '';
                                    const saldo = parseInt(data.tabungan_saldo, 10) || 0;
                                    const saldoBadge = saldo > 0
                                        ? `<span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Tab: ${escape(this.formatRupiah(saldo))}</span>`
                                        : '';

                                    return `
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="font-medium truncate">${escape(data.nama)}</div>
                                                <div class="mt-0.5 flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                                    <span>NIS: ${escape(data.nis || '-')}</span>
                                                    ${kelasBadge}
                                                </div>
                                            </div>
                                            <div class="shrink-0 text-right">${saldoBadge}</div>
                                        </div>
                                    `;
                                },
                                item: (data, escape) => {
                                    return `<div class="truncate"><span class="font-medium">${escape(data.nama)}</span> <span class="text-zinc-500 dark:text-zinc-400 text-xs">(${escape(data.nis || '-')})</span></div>`;
                                },
                                no_results: () => '<div class="no-results">Siswa tidak ditemukan</div>',
                            },
                            onChange: (value) => {
                                this.handleSiswaChange(value ? String(value) : '');
                            },
                        });

                        if (this.isReadOnly) {
                            this.siswaTs.disable();
                        }
                    });
                },

                handleSiswaChange(newSiswaId) {
                    const prevSiswaId = this.siswa_id;
                    this.siswa_id = newSiswaId;
                    delete this.errors.siswa_id;
                    delete this.errors.pembayaran;

                    if (this.isReadOnly) {
                        return;
                    }

                    const siswa = this.selectedSiswa;
                    const newLembagaId = siswa ? siswa.lembaga_id : null;

                    if (prevSiswaId !== newSiswaId && newLembagaId !== null && newLembagaId !== undefined && newLembagaId !== '') {
                        let removedCount = 0;
                        const kept = [];
                        for (const row of this.items) {
                            if (!row.barang_id) {
                                continue;
                            }
                            const barang = this.getBarangById(row.barang_id);
                            if (barang && String(barang.lembaga_id) === String(newLembagaId)) {
                                kept.push(row);
                            } else {
                                this.destroyBarangSelect(row._uid);
                                removedCount++;
                            }
                        }

                        if (removedCount > 0) {
                            this.items = kept;
                            this.addEmptyRow(false);
                            this.recalculateGrandTotal();
                            this.notify('Item dari lembaga berbeda dihapus otomatis.', 'warning');
                        } else if (this.items.length === 0) {
                            this.addEmptyRow(false);
                        }
                    }

                    this.refreshAllBarangSelects();

                    if (newSiswaId) {
                        this.$nextTick(() => {
                            this.focusLastBarangSelect(false);
                        });
                    }
                },

                initBarangSelect(el, item) {
                    if (typeof TomSelect === 'undefined') {
                        return;
                    }

                    this.$nextTick(() => {
                        if (!el.isConnected) {
                            return;
                        }
                        if (el.tomselect) {
                            el.tomselect.destroy();
                        }
                        this.destroyBarangSelect(item._uid);

                        const plugins = this.isReadOnly ? [] : ['dropdown_input'];
                        const ts = new TomSelect(el, {
                            plugins: plugins,
                            dropdownParent: 'body',
                            options: this.getBarangOptionsForRow(item.barang_id),
                            valueField: 'id',
                            labelField: 'nama',
                            searchField: ['nama', 'kode'],
                            disabledField: 'disabled',
                            maxOptions: 60,
                            openOnFocus: true,
                            selectOnTab: true,
                            placeholder: 'Cari nama atau kode barang...',
                            items: item.barang_id ? [String(item.barang_id)] : [],
                            render: {
                                option: (data, escape) => {
                                    const stok = parseInt(data.stok, 10) || 0;
                                    const stokClass = stok <= 0
                                        ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300'
                                        : (stok <= 5
                                            ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300'
                                            : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300');
                                    const stokLabel = stok <= 0 ? 'Habis' : `Stok: ${stok} ${escape(data.satuan || 'PCS')}`;
                                    const kodeHtml = data.kode
                                        ? `<span class="rounded bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 font-mono text-[11px] text-zinc-600 dark:text-zinc-300">${escape(data.kode)}</span>`
                                        : '';

                                    return `
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-medium truncate">${escape(data.nama)}</span>
                                                    ${kodeHtml}
                                                </div>
                                                <div class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                                    ${escape(this.formatRupiah(data.harga))}
                                                </div>
                                            </div>
                                            <span class="shrink-0 rounded-md px-2 py-0.5 text-xs font-medium ${stokClass}">
                                                ${stokLabel}
                                            </span>
                                        </div>
                                    `;
                                },
                                item: (data, escape) => {
                                    const stok = parseInt(data.stok, 10) || 0;
                                    return `<div class="flex items-center justify-between gap-2 w-full pr-2 truncate"><span class="truncate font-medium">${escape(data.nama)}</span><span class="shrink-0 text-xs text-zinc-500 dark:text-zinc-400">(Stok: ${stok})</span></div>`;
                                },
                                no_results: () => '<div class="no-results">Barang tidak ditemukan</div>',
                            },
                            onChange: (value) => {
                                if (this._syncingBarang) {
                                    return;
                                }
                                this.onSelectBarang(item._uid, value ? String(value) : '');
                            },
                        });

                        if (this.isReadOnly) {
                            ts.disable();
                        }

                        this.barangTsMap.set(item._uid, ts);

                        if (this.pendingFocusUid === item._uid) {
                            this.pendingFocusUid = null;
                            ts.focus();
                        }
                    });
                },

                destroyBarangSelect(uid) {
                    const ts = this.barangTsMap.get(uid);
                    if (ts) {
                        ts.destroy();
                        this.barangTsMap.delete(uid);
                    }
                },

                refreshAllBarangSelects() {
                    this._syncingBarang = true;
                    try {
                        for (const row of this.items) {
                            const ts = this.barangTsMap.get(row._uid);
                            if (!ts) {
                                continue;
                            }
                            const currentVal = row.barang_id ? String(row.barang_id) : '';
                            ts.clear(true);
                            ts.clearOptions();
                            ts.addOptions(this.getBarangOptionsForRow(currentVal));
                            if (currentVal) {
                                ts.setValue(currentVal, true);
                            }
                            ts.refreshOptions(false);
                        }
                    } finally {
                        this._syncingBarang = false;
                    }
                },

                onSelectBarang(uid, barangId) {
                    const index = this.items.findIndex((it) => it._uid === uid);
                    if (index === -1) {
                        return;
                    }

                    const row = this.items[index];
                    const ts = this.barangTsMap.get(uid);
                    delete this.errors.items;
                    delete this.errors[`items.${index}.barang_id`];

                    if (!barangId) {
                        row.barang_id = '';
                        row.jumlah = 1;
                        row.harga = 0;
                        row.total = 0;
                        this.recalculateGrandTotal();
                        return;
                    }

                    const barang = this.getBarangById(barangId);
                    if (!barang) {
                        return;
                    }

                    const stok = parseInt(barang.stok, 10) || 0;
                    if (stok <= 0) {
                        this.notify('Stok barang habis (0).', 'danger');
                        this._syncingBarang = true;
                        ts?.clear(true);
                        this._syncingBarang = false;
                        row.barang_id = '';
                        row.jumlah = 1;
                        row.harga = 0;
                        row.total = 0;
                        this.recalculateGrandTotal();
                        return;
                    }

                    const existing = this.items.find((it, k) => k !== index && it.barang_id && String(it.barang_id) === String(barangId));
                    if (existing) {
                        const nextQty = (parseInt(existing.jumlah, 10) || 0) + 1;
                        if (nextQty > stok) {
                            this.notify(`Jumlah ${barang.nama} melebihi stok yang tersedia (${stok}).`, 'danger');
                        } else {
                            existing.jumlah = nextQty;
                            existing.total = nextQty * (parseInt(existing.harga, 10) || 0);
                            this.flashRow(existing._uid);
                            this.notify('Barang sudah ada di daftar. Jumlah ditambahkan.', 'success');
                        }

                        this._syncingBarang = true;
                        ts?.clear(true);
                        this._syncingBarang = false;
                        row.barang_id = '';
                        row.jumlah = 1;
                        row.harga = 0;
                        row.total = 0;
                        this.recalculateGrandTotal();

                        this.$nextTick(() => {
                            ts?.open();
                        });
                        return;
                    }

                    row.barang_id = String(barang.id);
                    row.harga = parseInt(barang.harga, 10) || 0;
                    row.jumlah = 1;
                    row.total = row.harga;
                    this.recalculateGrandTotal();

                    if (index === this.items.length - 1) {
                        this.addEmptyRow(false);
                    }

                    this.$nextTick(() => {
                        const qtyInput = document.getElementById('qty-' + row._uid);
                        if (qtyInput) {
                            qtyInput.focus();
                            qtyInput.select();
                        }
                    });
                },

                addEmptyRow(shouldFocus = false) {
                    const row = {
                        _uid: this.nextUid(),
                        id: null,
                        barang_id: '',
                        jumlah: 1,
                        harga: 0,
                        total: 0,
                    };
                    if (shouldFocus) {
                        this.pendingFocusUid = row._uid;
                    }
                    this.items.push(row);
                },

                removeItem(index) {
                    const row = this.items[index];
                    if (!row) {
                        return;
                    }
                    this.destroyBarangSelect(row._uid);
                    this.items.splice(index, 1);

                    if (this.items.length === 0 && !this.isReadOnly) {
                        this.addEmptyRow(false);
                    }
                    this.recalculateGrandTotal();
                },

                stepQty(item, delta) {
                    if (this.isReadOnly) {
                        return;
                    }
                    const next = (parseInt(item.jumlah, 10) || 1) + delta;
                    item.jumlah = next;
                    this.clampAndRecalcRow(item, true);
                },

                clampAndRecalcRow(item, enforceMin = false) {
                    let qty = parseInt(item.jumlah, 10);
                    if (isNaN(qty) || qty < 1) {
                        if (enforceMin) {
                            qty = 1;
                            item.jumlah = 1;
                        } else {
                            qty = 0;
                        }
                    }

                    if (qty > 0 && item.barang_id) {
                        const barang = this.getBarangById(item.barang_id);
                        if (barang) {
                            const stok = parseInt(barang.stok, 10) || 0;
                            if (qty > stok) {
                                this.notify(`Jumlah melebihi stok yang tersedia (${stok}).`, 'danger');
                                qty = stok > 0 ? stok : 1;
                                item.jumlah = qty;
                            }
                        }
                    }

                    const harga = Math.max(0, parseInt(item.harga, 10) || 0);
                    item.total = qty * harga;
                    this.recalculateGrandTotal();
                },

                recalcPriceRow(item, enforceMin = false) {
                    const qty = Math.max(1, parseInt(item.jumlah, 10) || 1);
                    let harga = parseInt(item.harga, 10);
                    if (isNaN(harga) || harga < 0) {
                        harga = 0;
                        if (enforceMin) {
                            item.harga = 0;
                        }
                    }
                    item.total = qty * harga;
                    this.recalculateGrandTotal();
                },

                recalculateGrandTotal() {
                    this.grandTotal = this.items.reduce((sum, it) => sum + (parseInt(it.total, 10) || 0), 0);
                },

                focusLastBarangSelect(openDropdown = true) {
                    const emptyRow = [...this.items].reverse().find((it) => !it.barang_id) || this.items[this.items.length - 1];
                    if (!emptyRow) {
                        return;
                    }
                    const ts = this.barangTsMap.get(emptyRow._uid);
                    if (ts) {
                        ts.focus();
                        if (openDropdown) {
                            ts.open();
                        }
                    }
                },

                handleQtyEnter(item) {
                    this.clampAndRecalcRow(item, true);
                    this.focusLastBarangSelect(true);
                },

                handleShortcut(event) {
                    if (this.isReadOnly) {
                        return;
                    }

                    if (event.key === 'F1') {
                        event.preventDefault();
                        this.siswaTs?.focus();
                        this.siswaTs?.open();
                    } else if (event.key === 'F2') {
                        event.preventDefault();
                        this.focusLastBarangSelect(true);
                    } else if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
                        event.preventDefault();
                        this.submitForm();
                    }
                },

                getError(field) {
                    const err = this.errors?.[field];
                    if (Array.isArray(err) && err.length > 0) {
                        return err[0];
                    }
                    if (typeof err === 'string') {
                        return err;
                    }
                    return null;
                },

                async submitForm() {
                    if (this.isReadOnly || this.submitting) {
                        return;
                    }

                    this.errors = {};

                    if (!this.kode || !this.kode.trim()) {
                        this.errors.kode = ['Kode penjualan wajib diisi.'];
                        this.$refs.kodeInput?.focus();
                        return;
                    }

                    if (!this.siswa_id) {
                        this.errors.siswa_id = ['Siswa pembeli wajib dipilih.'];
                        this.siswaTs?.focus();
                        this.siswaTs?.open();
                        return;
                    }

                    const payloadItems = this.filledItems.map((it) => ({
                        id: it.id || null,
                        barang_id: String(it.barang_id),
                        jumlah: parseInt(it.jumlah, 10) || 1,
                        harga: parseInt(it.harga, 10) || 0,
                    }));

                    if (payloadItems.length === 0) {
                        this.errors.items = ['Minimal pilih 1 barang.'];
                        this.focusLastBarangSelect(true);
                        return;
                    }

                    if (this.isTabunganInsufficient) {
                        this.errors.pembayaran = ['Saldo tabungan tidak mencukupi untuk total belanja.'];
                        this.notify('Saldo tabungan siswa tidak mencukupi.', 'danger');
                        return;
                    }

                    this.submitting = true;
                    try {
                        const result = await this.$wire.saveTransaction({
                            kode: this.kode.trim(),
                            siswa_id: String(this.siswa_id),
                            pembayaran: this.pembayaran,
                            items: payloadItems,
                        });

                        if (result && result.ok === false && result.errors) {
                            this.errors = result.errors;
                        }
                    } finally {
                        this.submitting = false;
                    }
                },
            };
        };
    </script>

    @if ($errors->any())
        <div class="max-w-5xl mx-auto mb-4">
            <flux:callout variant="danger">
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </flux:callout>
        </div>
    @endif

    <div
        wire:ignore
        x-data="createPenjualanPos({
            isReadOnly: @js($isReadOnly),
            siswas: @js($siswasData),
            barangs: @js($barangsData),
            initialKode: @js($kode),
            initialSiswaId: @js((string) $siswa_id),
            initialPembayaran: @js((string) $pembayaran),
            initialItems: @js($items),
        })"
        x-on:keydown.window="handleShortcut($event)"
        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl max-w-5xl mx-auto mb-6 border border-zinc-200/70 dark:border-zinc-700/70"
    >
        <div class="p-6">
            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Master Info -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-5 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <!-- Kode -->
                    <div class="md:col-span-3 space-y-1.5">
                        <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            Kode Penjualan
                        </label>
                        <input
                            x-ref="kodeInput"
                            type="text"
                            x-model="kode"
                            :disabled="isReadOnly"
                            placeholder="KODE TRANSAKSI"
                            class="w-full rounded-lg border border-zinc-200 dark:border-white/10 bg-white dark:bg-white/10 px-3 py-2 text-sm font-mono text-zinc-800 dark:text-zinc-100 shadow-xs focus:border-zinc-400 focus:outline-hidden focus:ring-2 focus:ring-accent disabled:opacity-60 disabled:cursor-not-allowed"
                        />
                        <template x-if="getError('kode')">
                            <p class="text-xs text-red-500" x-text="getError('kode')"></p>
                        </template>
                    </div>

                    <!-- Siswa Pembeli (Tom Select) -->
                    <div class="md:col-span-5 space-y-1.5">
                        <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            Siswa Pembeli
                        </label>
                        <select
                            x-ref="siswaSelect"
                            x-init="initSiswaSelect($el)"
                            :disabled="isReadOnly"
                            class="w-full"
                        >
                            <option value="">Cari nama, NIS, atau kelas siswa...</option>
                        </select>

                        <!-- Info Siswa Terpilih -->
                        <template x-if="selectedSiswa">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 rounded-lg bg-zinc-50 dark:bg-zinc-900/60 border border-zinc-200/80 dark:border-zinc-700/80 px-3 py-1.5 text-xs text-zinc-600 dark:text-zinc-300">
                                <span>NIS: <strong class="font-medium text-zinc-800 dark:text-zinc-100" x-text="selectedSiswa.nis"></strong></span>
                                <template x-if="selectedSiswa.kelas">
                                    <span>• Kelas: <strong class="font-medium text-zinc-800 dark:text-zinc-100" x-text="selectedSiswa.kelas"></strong></span>
                                </template>
                                <span>• Tabungan: <strong class="font-semibold text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(selectedSiswa.tabungan_saldo)"></strong></span>
                            </div>
                        </template>

                        <template x-if="getError('siswa_id')">
                            <p class="text-xs text-red-500" x-text="getError('siswa_id')"></p>
                        </template>
                    </div>

                    <!-- Metode Pembayaran -->
                    <div class="md:col-span-4 space-y-1.5">
                        <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            Metode Pembayaran
                        </label>
                        <div class="grid grid-cols-3 gap-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-900/70 p-1 border border-zinc-200/80 dark:border-zinc-700/80">
                            <button
                                type="button"
                                :disabled="isReadOnly"
                                @click="pembayaran = 'tun'; delete errors.pembayaran"
                                :class="pembayaran === 'tun'
                                    ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-xs font-semibold'
                                    : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200'"
                                class="rounded-md px-2 py-1.5 text-xs transition-all cursor-pointer disabled:cursor-not-allowed disabled:opacity-70"
                            >
                                Tunai
                            </button>
                            <button
                                type="button"
                                :disabled="isReadOnly"
                                @click="pembayaran = 'tab'; delete errors.pembayaran"
                                :class="pembayaran === 'tab'
                                    ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-xs font-semibold'
                                    : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200'"
                                class="rounded-md px-2 py-1.5 text-xs transition-all cursor-pointer disabled:cursor-not-allowed disabled:opacity-70"
                            >
                                Tabungan
                            </button>
                            <button
                                type="button"
                                :disabled="isReadOnly"
                                @click="pembayaran = 'tag'; delete errors.pembayaran"
                                :class="pembayaran === 'tag'
                                    ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-xs font-semibold'
                                    : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200'"
                                class="rounded-md px-2 py-1.5 text-xs transition-all cursor-pointer disabled:cursor-not-allowed disabled:opacity-70"
                            >
                                Tagihan
                            </button>
                        </div>

                        <template x-if="isTabunganInsufficient">
                            <p class="text-xs font-medium text-amber-600 dark:text-amber-400">
                                Saldo tabungan kurang dari total belanja.
                            </p>
                        </template>
                        <template x-if="getError('pembayaran')">
                            <p class="text-xs text-red-500" x-text="getError('pembayaran')"></p>
                        </template>
                    </div>
                </div>

                <!-- Detail Items -->
                <div>
                    <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Detail Barang</h3>
                            <span class="inline-flex items-center rounded-full bg-zinc-100 dark:bg-zinc-700 px-2.5 py-0.5 text-xs font-medium text-zinc-700 dark:text-zinc-300 tabular-nums">
                                <span x-text="filledItems.length"></span>&nbsp;item •&nbsp;<span x-text="totalQty"></span>&nbsp;pcs
                            </span>
                        </div>

                        @if(! $isReadOnly)
                            <flux:button type="button" size="sm" variant="filled" icon="plus" x-on:click="addEmptyRow(true)">
                                Tambah Baris
                            </flux:button>
                        @endif
                    </div>

                    <template x-if="getError('items')">
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 dark:border-red-900/60 dark:bg-red-950/40 px-4 py-3 text-sm text-red-700 dark:text-red-300" x-text="getError('items')"></div>
                    </template>

                    <div class="overflow-x-auto md:overflow-visible rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700/70 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3 min-w-[260px] w-2/5">Barang</th>
                                    <th class="px-4 py-3 w-36">Jumlah</th>
                                    <th class="px-4 py-3 w-44">Harga Jual</th>
                                    <th class="px-4 py-3 w-40 text-right">Subtotal</th>
                                    @if(! $isReadOnly)
                                        <th class="px-4 py-3 w-16 text-center">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="(item, index) in items" :key="item._uid">
                                    <tr
                                        :class="highlightedUid === item._uid ? 'bg-emerald-50/90 dark:bg-emerald-950/40' : 'hover:bg-zinc-50/60 dark:hover:bg-zinc-700/20'"
                                        class="transition-colors duration-300 align-top"
                                    >
                                        <!-- Barang (Tom Select) -->
                                        <td class="px-4 py-2.5">
                                            <select
                                                :data-uid="item._uid"
                                                x-init="initBarangSelect($el, item)"
                                                :disabled="isReadOnly"
                                                class="w-full"
                                            >
                                                <option value="">Cari nama atau kode barang...</option>
                                            </select>
                                            <template x-if="item.barang_id && !isReadOnly">
                                                <div class="mt-1 flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                                    <span>Stok tersedia: <strong x-text="getBarangById(item.barang_id)?.stok ?? 0"></strong> <span x-text="getBarangById(item.barang_id)?.satuan ?? 'PCS'"></span></span>
                                                </div>
                                            </template>
                                            <template x-if="getError(`items.${index}.barang_id`)">
                                                <p class="mt-1 text-xs text-red-500" x-text="getError(`items.${index}.barang_id`)"></p>
                                            </template>
                                        </td>

                                        <!-- Jumlah -->
                                        <td class="px-4 py-2.5">
                                            <div class="inline-flex items-center rounded-lg border border-zinc-200 dark:border-white/10 bg-white dark:bg-white/10 overflow-hidden shadow-xs">
                                                <button
                                                    type="button"
                                                    x-show="!isReadOnly"
                                                    @click="stepQty(item, -1)"
                                                    :disabled="!item.barang_id || item.jumlah <= 1"
                                                    class="px-2.5 py-2 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 disabled:opacity-40 cursor-pointer disabled:cursor-not-allowed transition-colors"
                                                    title="Kurangi"
                                                >
                                                    &minus;
                                                </button>
                                                <input
                                                    :id="'qty-' + item._uid"
                                                    type="number"
                                                    min="1"
                                                    x-model.number="item.jumlah"
                                                    @input="clampAndRecalcRow(item, false)"
                                                    @blur="clampAndRecalcRow(item, true)"
                                                    @keydown.enter.prevent="handleQtyEnter(item)"
                                                    :disabled="isReadOnly || !item.barang_id"
                                                    class="w-14 bg-transparent py-2 text-center text-sm font-medium text-zinc-900 dark:text-zinc-100 focus:outline-hidden tabular-nums [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none disabled:opacity-60"
                                                />
                                                <button
                                                    type="button"
                                                    x-show="!isReadOnly"
                                                    @click="stepQty(item, 1)"
                                                    :disabled="!item.barang_id"
                                                    class="px-2.5 py-2 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 disabled:opacity-40 cursor-pointer disabled:cursor-not-allowed transition-colors"
                                                    title="Tambah"
                                                >
                                                    +
                                                </button>
                                            </div>
                                            <template x-if="getError(`items.${index}.jumlah`)">
                                                <p class="mt-1 text-xs text-red-500" x-text="getError(`items.${index}.jumlah`)"></p>
                                            </template>
                                        </td>

                                        <!-- Harga Jual -->
                                        <td class="px-4 py-2.5">
                                            <div class="relative flex items-center">
                                                <span class="pointer-events-none absolute left-3 text-xs text-zinc-400">Rp</span>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    x-model.number="item.harga"
                                                    @input="recalcPriceRow(item, false)"
                                                    @blur="recalcPriceRow(item, true)"
                                                    @keydown.enter.prevent="handleQtyEnter(item)"
                                                    :disabled="isReadOnly || !item.barang_id"
                                                    class="w-full rounded-lg border border-zinc-200 dark:border-white/10 bg-white dark:bg-white/10 pl-8 pr-3 py-2 text-sm text-zinc-800 dark:text-zinc-100 shadow-xs focus:border-zinc-400 focus:outline-hidden focus:ring-2 focus:ring-accent tabular-nums disabled:opacity-60 disabled:cursor-not-allowed"
                                                />
                                            </div>
                                            <template x-if="getError(`items.${index}.harga`)">
                                                <p class="mt-1 text-xs text-red-500" x-text="getError(`items.${index}.harga`)"></p>
                                            </template>
                                        </td>

                                        <!-- Subtotal -->
                                        <td class="px-4 py-3.5 text-right font-semibold text-zinc-900 dark:text-zinc-100 tabular-nums whitespace-nowrap">
                                            <span x-text="formatRupiah(item.total)"></span>
                                        </td>

                                        <!-- Aksi -->
                                        @if(! $isReadOnly)
                                            <td class="px-4 py-2.5 text-center">
                                                <flux:button
                                                    type="button"
                                                    variant="danger"
                                                    size="sm"
                                                    icon="trash"
                                                    x-on:click="removeItem(index)"
                                                    x-bind:disabled="items.length === 1 && !item.barang_id"
                                                />
                                            </td>
                                        @endif
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-zinc-50/80 dark:bg-zinc-900/40 border-t border-zinc-200 dark:border-zinc-700">
                                <tr>
                                    <td colspan="3" class="px-4 py-3.5 text-right font-semibold text-gray-900 dark:text-white">
                                        Grand Total
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-bold text-xl text-emerald-600 dark:text-emerald-400 tabular-nums whitespace-nowrap" x-text="formatRupiah(grandTotal)">
                                    </td>
                                    @if(! $isReadOnly)
                                        <td></td>
                                    @endif
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap items-center justify-between gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                        @if(! $isReadOnly)
                            <span>Tekan <kbd class="rounded border border-zinc-300 dark:border-zinc-600 px-1.5 py-0.5 font-mono">Enter</kbd> di kolom jumlah untuk lanjut ke barang berikutnya.</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-3 ml-auto">
                        <flux:button variant="ghost" href="{{ route('admin.penjualan.index') }}" wire:navigate>Kembali</flux:button>
                        @if(! $isReadOnly)
                            <flux:button
                                type="submit"
                                variant="primary"
                                x-bind:disabled="submitting"
                            >
                                <span x-show="!submitting">Simpan Transaksi Penjualan</span>
                                <span x-show="submitting" x-cloak>Menyimpan...</span>
                            </flux:button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
