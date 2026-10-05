<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Detail Siswa: {{ $siswa->nama }}
            </h2>
            <flux:button variant="outline" href="{{ route('admin.siswa.index') }}" wire:navigate>Kembali</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Identitas -->
                <div>
                    <flux:heading size="lg" class="mb-4">Identitas Siswa</flux:heading>
                    
                    <div class="mb-6">
                        @if($siswa->foto)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($siswa->foto) }}" class="size-32 object-cover rounded-lg border border-gray-200 shadow-sm" alt="Foto Siswa">
                        @else
                            <img src="{{ asset($siswa->jenis_kelamin === 'p' ? 'girl.jpg' : 'boy.jpg') }}" class="size-32 object-cover rounded-lg border border-gray-200 shadow-sm" alt="Foto Default">
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div>
                            <flux:text class="text-sm text-gray-500">NIK</flux:text>
                            <flux:text class="font-medium">{{ $siswa->nik ?? '-' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-500">No. Akte Lahir</flux:text>
                            <flux:text class="font-medium">{{ $siswa->no_akte_lahir ?? '-' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-500">No. KK</flux:text>
                            <flux:text class="font-medium">{{ $siswa->no_kk ?? '-' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-500">Nama Lengkap</flux:text>
                            <flux:text class="font-medium">{{ $siswa->nama }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-500">Tempat, Tanggal Lahir</flux:text>
                            <flux:text class="font-medium">{{ $siswa->tempat_lahir ?? '-' }}, {{ $siswa->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->format('d M Y') : '-' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-500">Jenis Kelamin</flux:text>
                            <flux:text class="font-medium">{{ $siswa->jenis_kelamin === 'l' ? 'Laki-laki' : ($siswa->jenis_kelamin === 'p' ? 'Perempuan' : '-') }}</flux:text>
                        </div>
                    </div>
                </div>
                
                <!-- Kontak & Orang Tua -->
                <div>
                    <flux:heading size="lg" class="mb-4">Kontak</flux:heading>
                    <div class="space-y-4">
                        <div>
                            <flux:text class="text-sm text-gray-500">Nama Ayah</flux:text>
                            <flux:text class="font-medium">{{ $siswa->nama_ayah ?? '-' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-500">Nama Ibu</flux:text>
                            <flux:text class="font-medium">{{ $siswa->nama_ibu ?? '-' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-500">Alamat Lengkap</flux:text>
                            <flux:text class="font-medium">{{ $siswa->alamat ?? '-' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-500">No. Telepon/WhatsApp</flux:text>
                            <flux:text class="font-medium">{{ $siswa->telepon ?? '-' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-500">Email</flux:text>
                            <flux:text class="font-medium">{{ $siswa->email ?? '-' }}</flux:text>
                        </div>
                    </div>
                </div>

                <!-- Akademik -->
                <div>
                    <flux:heading size="lg" class="mb-4">Akademik</flux:heading>
                    <div class="space-y-4">
                        <div>
                            <flux:text class="text-sm text-gray-500">NIS</flux:text>
                            <flux:text class="font-medium">{{ $siswa->nis ?? '-' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-500">NISN</flux:text>
                            <flux:text class="font-medium">{{ $siswa->nisn ?? '-' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-500">Kelas</flux:text>
                            <flux:text class="font-medium">{{ $siswa->kelas->nama ?? '-' }} {{ config('custom.lembaga.'.$siswa->lembaga_id) }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-500">Status</flux:text>
                            <div class="mt-1">
                                @if($siswa->status)
                                    @if($siswa->status == 1)
                                        <flux:badge color="green" size="sm">{{ config('custom.siswa.status.'.$siswa->status, 'Aktif') }}</flux:badge>
                                    @else
                                        <flux:badge color="red" size="sm">{{ config('custom.siswa.status.'.$siswa->status, 'Nonaktif') }}</flux:badge>
                                    @endif
                                @else
                                    <flux:badge color="gray" size="sm">Belum Diatur</flux:badge>
                                @endif
                            </div>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-500">Label Siswa</flux:text>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @if($siswa->tags && $siswa->tags->count() > 0)
                                    @foreach($siswa->tags as $tag)
                                        <flux:badge color="blue" size="sm">{{ $tag->name }}</flux:badge>
                                    @endforeach
                                @else
                                    <flux:text class="font-medium">-</flux:text>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Rincian Tagihan -->
                    <div class="p-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col justify-between">
                        <div class="h-full flex flex-col">
                            <div class="flex justify-between items-center mb-4 gap-2 flex-wrap">
                                <flux:heading size="lg">Rincian Tagihan</flux:heading>
                                <div class="flex gap-2 flex-wrap">
                                    <flux:modal.trigger name="input-tagihan">
                                        <flux:button size="sm" variant="primary" icon="plus">Input Tagihan</flux:button>
                                    </flux:modal.trigger>
                                    <flux:modal.trigger name="confirm-pay-all">
                                        <flux:button size="sm" class="!bg-orange-500 hover:!bg-orange-600 !text-white !border-transparent" icon="check">Bayar Semua</flux:button>
                                    </flux:modal.trigger>
                                    <flux:button href="{{ route('admin.siswa.cetak-tagihan', $siswa->id) }}" target="_blank" size="sm" class="!bg-green-600 hover:!bg-green-700 !text-white !border-transparent" icon="printer">Cetak Tagihan</flux:button>
                                </div>
                            </div>
                        @if($siswa->tagihan->count() > 0)
                            @if(count($selectedTagihan) > 0)
                                <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg flex flex-col md:flex-row md:items-center justify-between mb-4 border border-gray-200 dark:border-gray-700 gap-3">
                                    <div class="flex items-center gap-4">
                                        <flux:modal.trigger name="confirm-pay-selected">
                                            <flux:button size="sm" class="!bg-orange-500 hover:!bg-orange-600 !text-white !border-transparent" icon="check">Bayar</flux:button>
                                        </flux:modal.trigger>
                                        <flux:modal.trigger name="confirm-delete-tagihan">
                                            <flux:button size="sm" variant="danger" icon="trash">Hapus</flux:button>
                                        </flux:modal.trigger>
                                        <flux:text class="text-sm font-medium">{{ count($selectedTagihan) }} data dipilih</flux:text>
                                    </div>
                                    <div class="flex items-center gap-3 text-sm">
                                        @if(count($selectedTagihan) === $siswa->tagihan->count())
                                            <button type="button" wire:click="deselectAllTagihan" class="text-red-500 hover:underline">Batalkan semua pilihan</button>
                                        @else
                                            <button type="button" wire:click="selectAllTagihan" class="text-green-600 dark:text-green-400 hover:underline">Pilih semua ({{ $siswa->tagihan->count() }})</button>
                                            <button type="button" wire:click="deselectAllTagihan" class="text-red-500 hover:underline">Batalkan semua pilihan</button>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="space-y-3">
                                @foreach($siswa->tagihan as $tagihan)
                                    <div wire:key="tagihan-{{ $tagihan->id }}" class="p-4 rounded-lg border {{ in_array($tagihan->id, $selectedTagihan) ? 'border-green-500 border-l-4' : 'border-gray-200 dark:border-gray-700' }} bg-white dark:bg-gray-800 flex justify-between items-center shadow-xs">
                                        <div class="flex items-center gap-3">
                                            <flux:checkbox wire:model.live="selectedTagihan" value="{{ $tagihan->id }}" />
                                            <div>
                                                <flux:text class="font-medium">{{ $tagihan->kas->nama ?? 'Tagihan' }} <span class="text-[10px]">({{ $tagihan->created_at->format('d/m/Y') }})</span></flux:text>
                                                <flux:text class="text-xs text-gray-500">{{ $tagihan->keterangan ?? '-' }}</flux:text>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-4 text-right">
                                            <div>
                                                <flux:text class="font-medium">Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}</flux:text>
                                                <flux:text class="text-xs text-red-500">Kurang: Rp {{ number_format($tagihan->jumlah - ($tagihan->bayar ?? 0), 0, ',', '.') }}</flux:text>
                                            </div>
                                            
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="mt-auto pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                                <flux:text class="font-bold text-lg">Total Tagihan: Rp {{ number_format($siswa->tagihan->sum(function($t) { return $t->jumlah - ($t->bayar ?? 0); }), 0, ',', '.') }}</flux:text>
                            </div>
                        @else
                            <flux:text class="text-gray-500 italic">Semua tagihan sudah lunas.</flux:text>
                        @endif
                        </div>
                    </div>

                    <!-- Tabungan Siswa -->
                    <div class="p-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col justify-between">
                        <div class="h-full flex flex-col">
                            <div class="flex justify-between items-center mb-4 gap-2 flex-wrap">
                                <flux:heading size="lg">Tabungan Siswa</flux:heading>
                                <flux:modal.trigger name="setor-tabungan">
                                    <flux:button size="sm" variant="primary" icon="plus">Setor Tabungan</flux:button>
                                </flux:modal.trigger>
                            </div>
                        @if($siswa->tabungan->count() > 0)
                            <div class="space-y-3">
                                @foreach($siswa->tabungan as $tabungan)
                                    <div wire:key="tabungan-{{ $tabungan->id }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex justify-between items-center shadow-xs">
                                        <div>
                                            <flux:text class="font-medium">{{ $tabungan->kas->nama ?? 'Tabungan' }}</flux:text>
                                            <flux:text class="text-xs text-gray-500">Saldo saat ini</flux:text>
                                        </div>
                                        <div class="flex items-center gap-4 text-right">
                                            <flux:text class="font-bold text-green-600 dark:text-green-400">Rp {{ number_format($tabungan->saldo, 0, ',', '.') }}</flux:text>
                                            <div class="flex gap-2">
                                                <flux:button wire:click="openSetorSpecific('{{ $tabungan->id }}')" size="sm" class="!bg-green-600 hover:!bg-green-700 !text-white !border-transparent" icon="plus"> Setor</flux:button>
                                                <flux:button wire:click="openTarikSpecific('{{ $tabungan->id }}')" size="sm" class="!bg-orange-500 hover:!bg-orange-600 !text-white !border-transparent" icon="minus">Tarik</flux:button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="mt-auto pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                                <flux:text class="font-bold text-lg">Saldo Tabungan: Rp {{ number_format($siswa->tabungan->sum('saldo'), 0, ',', '.') }}</flux:text>
                            </div>
                        @else
                            <flux:text class="text-gray-500 italic">Belum ada data tabungan.</flux:text>
                        @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 flex gap-2">
                <flux:button variant="primary" href="{{ route('admin.siswa.edit', $siswa->id) }}" wire:navigate>Edit Data</flux:button>
            </div>
        </div>
    </div>

    <flux:modal name="input-tagihan" class="md:w-96 space-y-6">
        <div>
            <flux:heading size="lg">Input Tagihan Baru</flux:heading>
            <flux:subheading>Tambahkan tagihan untuk {{ $siswa->nama }}</flux:subheading>
        </div>

        <form wire:submit="saveTagihan" class="space-y-6">
            <flux:radio.group wire:model="form_tagihan_kas_id" label="Jenis Tagihan" class="flex flex-row flex-wrap gap-4 items-start">
                @foreach($daftarKasTagihan as $kas)
                    <flux:radio value="{{ $kas->id }}" label="{{ $kas->nama }}" />
                @endforeach
            </flux:radio.group>

            <flux:field>
                <div x-data="{
                    raw: @entangle('form_tagihan_jumlah'),
                    formatted: '',
                    formatCurrency(value) {
                        if (!value) return '';
                        return parseInt(value.toString().replace(/[^0-9]/g, '')).toLocaleString('id-ID');
                    },
                    updateRaw(value) {
                        let clean = value.replace(/[^0-9]/g, '');
                        this.raw = clean ? parseInt(clean) : '';
                        this.formatted = this.formatCurrency(this.raw);
                    }
                }" x-init="
                    formatted = formatCurrency(raw);
                    $watch('raw', value => {
                        if (!value) formatted = '';
                    });
                ">
                    <flux:input x-model="formatted" @input="updateRaw($event.target.value)" label="Jumlah Tagihan (Rp)" type="text" placeholder="Contoh: 50000" />
                </div>
                <flux:error name="form_tagihan_jumlah" />
            </flux:field>

            <flux:field>
                <flux:label>Keterangan (Opsional)</flux:label>
                <flux:input type="text" wire:model="form_tagihan_keterangan" placeholder="Contoh: Buku Cetak" />
                <flux:error name="form_tagihan_keterangan" />
            </flux:field>

            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">Simpan Tagihan</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="confirm-pay-all" class="md:w-96 space-y-6">
        <div>
            <flux:heading size="lg">Bayar Semua Tagihan?</flux:heading>
            <flux:subheading>Anda yakin ingin melunasi semua tagihan untuk siswa <span class="font-bold">{{ $siswa->nama }}</span> sebesar <span class="font-bold">Rp {{ number_format($siswa->tagihan->sum(function($t) { return $t->jumlah - ($t->bayar ?? 0); }), 0, ',', '.') }}</span>? Tindakan ini tidak dapat dibatalkan.</flux:subheading>
        </div>
        
        <div class="flex gap-2 justify-end">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button variant="primary" class="!bg-orange-500 hover:!bg-orange-600 !text-white !border-transparent" wire:click="payAllTagihan">Ya, Lunas Semua</flux:button>
        </div>
    </flux:modal>

    <flux:modal name="confirm-delete-tagihan" class="md:w-96 space-y-6">
        <div>
            <flux:heading size="lg">Hapus Tagihan?</flux:heading>
            <flux:subheading>Anda yakin ingin menghapus tagihan yang dipilih? Tindakan ini tidak dapat dibatalkan.</flux:subheading>
        </div>
        
        <div class="flex gap-2 justify-end">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" wire:click="deleteTagihanTerpilih">Ya, Hapus</flux:button>
        </div>
    </flux:modal>
    <flux:modal name="setor-tabungan" class="md:w-96 space-y-6">
        <div>
            <flux:heading size="lg">Setor Tabungan</flux:heading>
            <flux:subheading>Masukkan rincian setor tabungan baru.</flux:subheading>
        </div>

        <flux:radio.group wire:model="form_setor_kas_id" label="Jenis Tabungan">
            @foreach(App\Models\Kas::getDaftarTabungan($siswa->lembaga_id)->get() as $kas)
                <flux:radio value="{{ $kas->id }}" label="{{ $kas->nama }}" />
            @endforeach
        </flux:radio.group>

        <div x-data="{
            raw: @entangle('form_setor_jumlah'),
            formatted: '',
            formatCurrency(value) {
                if (!value) return '';
                return parseInt(value.toString().replace(/[^0-9]/g, '')).toLocaleString('id-ID');
            },
            updateRaw(value) {
                let clean = value.replace(/[^0-9]/g, '');
                this.raw = clean ? parseInt(clean) : '';
                this.formatted = this.formatCurrency(this.raw);
            }
        }" x-init="
            formatted = formatCurrency(raw);
            $watch('raw', value => {
                if (!value) formatted = '';
            });
        ">
            <flux:input x-model="formatted" @input="updateRaw($event.target.value)" label="Jumlah Setor (Rp)" type="text" placeholder="Contoh: 50000" />
        </div>
    
        <div class="flex gap-2 justify-end">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button variant="primary" wire:click="setorTabungan">Simpan</flux:button>
        </div>
    </flux:modal>

    <!-- Modal Setor Tabungan Spesifik -->
    <flux:modal name="setor-tabungan-specific" class="md:w-96 space-y-6">
        <div>
            <flux:heading size="lg">Setor Tabungan</flux:heading>
            <flux:subheading>Masukkan rincian setor tabungan baru.</flux:subheading>
        </div>

        <flux:input value="{{ $active_tabungan_nama }}" label="Jenis Tabungan" readonly disabled />

        <div x-data="{
            raw: @entangle('form_setor_jumlah'),
            formatted: '',
            formatCurrency(value) {
                if (!value) return '';
                return parseInt(value.toString().replace(/[^0-9]/g, '')).toLocaleString('id-ID');
            },
            updateRaw(value) {
                let clean = value.replace(/[^0-9]/g, '');
                this.raw = clean ? parseInt(clean) : '';
                this.formatted = this.formatCurrency(this.raw);
            }
        }" x-init="
            formatted = formatCurrency(raw);
            $watch('raw', value => {
                if (!value) formatted = '';
            });
        ">
            <flux:input x-model="formatted" @input="updateRaw($event.target.value)" label="Jumlah Setor (Rp)" type="text" placeholder="Contoh: 50000" />
        </div>
    
        <div class="flex gap-2 justify-end">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button variant="primary" wire:click="saveSetorSpecific">Simpan</flux:button>
        </div>
    </flux:modal>

    <!-- Modal Tarik Tabungan -->
    <flux:modal name="tarik-tabungan-specific" class="md:w-96 space-y-6">
        <div>
            <flux:heading size="lg">Tarik Tabungan</flux:heading>
            <flux:subheading>Masukkan rincian penarikan tabungan.</flux:subheading>
        </div>

        <flux:input value="{{ $active_tabungan_nama }}" label="Jenis Tabungan" readonly disabled />

        <div x-data="{
            raw: @entangle('form_tarik_jumlah'),
            formatted: '',
            formatCurrency(value) {
                if (!value) return '';
                return parseInt(value.toString().replace(/[^0-9]/g, '')).toLocaleString('id-ID');
            },
            updateRaw(value) {
                let clean = value.replace(/[^0-9]/g, '');
                this.raw = clean ? parseInt(clean) : '';
                this.formatted = this.formatCurrency(this.raw);
            }
        }" x-init="
            formatted = formatCurrency(raw);
            $watch('raw', value => {
                if (!value) formatted = '';
            });
        ">
            <flux:input x-model="formatted" @input="updateRaw($event.target.value)" label="Jumlah Penarikan (Rp)" type="text" placeholder="Contoh: 50000" />
        </div>
    
        <div class="flex gap-2 justify-end">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button variant="primary" class="!bg-orange-500 hover:!bg-orange-600 !text-white !border-transparent" wire:click="saveTarikSpecific">Tarik</flux:button>
        </div>
    </flux:modal>

    <flux:modal name="confirm-pay-selected" class="md:w-[500px] space-y-6">
        <div>
            <flux:heading size="lg">Bayar Tagihan?</flux:heading>
            <flux:subheading>Anda yakin ingin melunasi <span class="font-bold">{{ count($selectedTagihan) }}</span> tagihan yang dipilih? Tindakan ini tidak dapat dibatalkan.</flux:subheading>
        </div>

        @php
            $selectedTagihans = collect($siswa->tagihan)->whereIn('id', $selectedTagihan);
            $totalSelected = $selectedTagihans->sum(function($t) { return $t->jumlah - ($t->bayar ?? 0); });
        @endphp

        @if(count($selectedTagihan) > 0)
        <div class="mt-4">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 space-y-3 max-h-60 overflow-y-auto">
                @foreach($selectedTagihans as $st)
                    <div class="flex justify-between items-center text-sm">
                        <div>
                            <div class="font-medium">{{ $st->kas->nama ?? 'Tagihan' }}</div>
                            <div class="text-xs text-gray-500">{{ $st->keterangan ?? '-' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-medium text-red-600 dark:text-red-400">Rp {{ number_format($st->jumlah - ($st->bayar ?? 0), 0, ',', '.') }}</div>
                        </div>
                    </div>
                    @if(!$loop->last) <hr class="border-gray-200 dark:border-gray-700 my-2"> @endif
                @endforeach
            </div>

            <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <flux:text class="font-bold">Total Pembayaran:</flux:text>
                <flux:text class="font-bold text-lg text-green-600 dark:text-green-400">Rp {{ number_format($totalSelected, 0, ',', '.') }}</flux:text>
            </div>
        </div>
        @endif
        
        <div class="flex gap-2 justify-end">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button variant="primary" class="!bg-orange-500 hover:!bg-orange-600 !text-white !border-transparent" wire:click="bayarTagihanTerpilih">Ya, Bayar</flux:button>
        </div>
    </flux:modal>
</div>
