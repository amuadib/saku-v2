<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Data Siswa
            </h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4" x-data="{ showAdvanced: false }">
                <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                    <div class="flex flex-row gap-2 items-center w-full sm:w-auto">
                        <div class="w-full md:w-80 flex-1">
                            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama, NIS atau NISN siswa..." icon="magnifying-glass" />
                        </div>
                        <flux:button x-on:click="showAdvanced = !showAdvanced" icon="adjustments-horizontal" variant="outline" class="whitespace-nowrap shrink-0">
                            <span class="hidden sm:inline" x-text="showAdvanced ? 'Sembunyikan Filter' : 'Advanced Filter'"></span>
                        </flux:button>
                    </div>
                    
                    <div class="w-full sm:w-auto flex flex-wrap gap-2 justify-end shrink-0">
                        <flux:button wire:click="generateUserSiswa" icon="user-plus" wire:loading.attr="disabled" color="primary" wire:confirm="Apakah Anda yakin ingin generate user untuk semua data siswa aktif?">
                            <span wire:loading.remove wire:target="generateUserSiswa">Generate User</span>
                            <span wire:loading wire:target="generateUserSiswa">Memproses...</span>
                        </flux:button>
                        <flux:button wire:click="syncSiswa" icon="arrow-path" wire:loading.attr="disabled" color="primary">
                            <span wire:loading.remove wire:target="syncSiswa">Sinkron Manual</span>
                            <span wire:loading wire:target="syncSiswa">Memproses...</span>
                        </flux:button>
                    </div>
                </div>

                <div x-show="showAdvanced" x-transition x-cloak class="mt-4 flex flex-col md:flex-row gap-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                    @if(auth()->user()->isAdmin())
                        <div class="w-full md:w-1/4">
                            <flux:select wire:model.live="filter_lembaga" placeholder="Semua Lembaga">
                                <option value="">Semua Lembaga</option>
                                @foreach(config('custom.lembaga') as $key => $val)
                                    <option value="{{ $key }}">{{ $val }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                    @endif
                    <!-- disable select jika belum memilih Lembaga, isi dengan kelas sesuai lembaga -->
                    <div class="w-full md:w-1/4">
                        <flux:select wire:model.live="filter_kelas" :placeholder="auth()->user()->isAdmin() ? ($filter_lembaga ? 'Semua Kelas' : 'Pilih Lembaga Dulu') : 'Semua Kelas'" :disabled="auth()->user()->isAdmin() && !$filter_lembaga">
                            <option value="">Semua Kelas</option>
                            @foreach($kelas_items as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="w-full md:w-1/4">
                        <flux:select wire:model.live="filter_status" placeholder="Semua Status">
                            <option value="">Semua Status</option>
                            @foreach(config('custom.siswa.status') as $key => $val)
                                <option value="{{ $key }}">{{ $val }}</option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="w-full md:w-1/4">
                        <flux:select wire:model.live="filter_label" placeholder="Semua Label">
                            <option value="">Semua Label</option>
                            @foreach(\App\Models\Tag::all() as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
            </div>

            @if($filter_lembaga || $filter_kelas || $filter_status || $filter_label)
                <div class="mb-4 flex flex-wrap items-center gap-2 text-sm border-b border-gray-100 dark:border-gray-700 pb-4">
                    <span class="text-gray-500 font-medium mr-2">Filter aktif</span>
                    
                    @if($filter_lembaga)
                        <button wire:click="$set('filter_lembaga', '')" class="inline-flex items-center gap-1.5 py-1 px-2.5 text-xs font-medium bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400 rounded-md cursor-pointer hover:bg-green-100 dark:hover:bg-green-500/20 transition-colors focus:outline-none">
                            Lembaga: {{ config('custom.lembaga.'.$filter_lembaga) ?? 'Lembaga terpilih' }}
                            <flux:icon name="x-mark" class="size-3" />
                        </button>
                    @endif

                    @if($filter_kelas)
                        @php
                            $nama_kelas = collect($kelas_items)->firstWhere('id', $filter_kelas)->nama ?? 'Kelas terpilih';
                        @endphp
                        <button wire:click="$set('filter_kelas', '')" class="inline-flex items-center gap-1.5 py-1 px-2.5 text-xs font-medium bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400 rounded-md cursor-pointer hover:bg-green-100 dark:hover:bg-green-500/20 transition-colors focus:outline-none">
                            Kelas: {{ $nama_kelas }}
                            <flux:icon name="x-mark" class="size-3" />
                        </button>
                    @endif

                    @if($filter_status)
                        <button wire:click="$set('filter_status', '')" class="inline-flex items-center gap-1.5 py-1 px-2.5 text-xs font-medium bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400 rounded-md cursor-pointer hover:bg-green-100 dark:hover:bg-green-500/20 transition-colors focus:outline-none">
                            Status: {{ config('custom.siswa.status.'.$filter_status) ?? 'Status terpilih' }}
                            <flux:icon name="x-mark" class="size-3" />
                        </button>
                    @endif

                    @if($filter_label)
                        <button wire:click="$set('filter_label', '')" class="inline-flex items-center gap-1.5 py-1 px-2.5 text-xs font-medium bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400 rounded-md cursor-pointer hover:bg-green-100 dark:hover:bg-green-500/20 transition-colors focus:outline-none">
                            Label: {{ \App\Models\Tag::find($filter_label)?->name ?? 'Label terpilih' }}
                            <flux:icon name="x-mark" class="size-3" />
                        </button>
                    @endif
                </div>
            @endif

            @if(count($selected) > 0)
                <div class="mb-4 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex flex-wrap gap-2">
                        <flux:button size="sm" color="blue" icon="paper-airplane" wire:click="kirimTagihan" wire:confirm="Kirim tagihan ke siswa yang dipilih?">Kirim tagihan</flux:button>
                        <flux:modal.trigger name="export-modal">
                            <flux:button size="sm" color="green" icon="document-arrow-down">Ekspor</flux:button>
                        </flux:modal.trigger>
                        <flux:button size="sm" color="red" icon="trash" wire:click="confirmBulkDelete">Hapus</flux:button>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        <span class="text-gray-600 dark:text-gray-400 font-medium">{{ count($selected) }} data dipilih</span>
                        @if($siswa_items->total() > count($selected))
                            <button wire:click="selectAllMatching" class="text-green-600 hover:text-green-700 font-medium focus:outline-none">Pilih semua ({{ $siswa_items->total() }})</button>
                        @endif
                        <button wire:click="deselectAll" class="text-red-600 hover:text-red-700 font-medium focus:outline-none">Batalkan semua pilihan</button>
                    </div>
                </div>
            @endif

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            @if (session()->has('error'))
                <flux:callout variant="danger" class="mb-4">
                    {{ session('error') }}
                </flux:callout>
            @endif

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>
                        <flux:checkbox wire:model.live="selectAllPage" />
                    </flux:table.column>
                    <flux:table.column>NIS/NISN</flux:table.column>
                    <flux:table.column>Nama Siswa</flux:table.column>
                    <flux:table.column>Kelas</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Label</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($siswa_items as $siswa)
                        <flux:table.row wire:key="{{ $siswa->id }}">
                            <flux:table.cell>
                                <flux:checkbox wire:model.live="selected" value="{{ $siswa->id }}" />
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="text-sm font-medium">{{ $siswa->nis }}</div>
                                <div class="text-xs text-gray-500">{{ $siswa->nisn }}</div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $siswa->nama }}</flux:table.cell>
                            <flux:table.cell>{{ optional($siswa->kelas)->nama ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @if($siswa->status)
                                    @if($siswa->status == 1)
                                    <flux:badge color="green">{{ config('custom.siswa.status.'.$siswa->status) }}</flux:badge>
                                    @else
                                        <flux:badge color="red">{{ config('custom.siswa.status.'.$siswa->status) }}</flux:badge>
                                    @endif
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($siswa->tags->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($siswa->tags as $tag)
                                            <flux:badge color="blue" size="sm">{{ $tag->name }}</flux:badge>
                                        @endforeach
                                    </div>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" color="purple" wire:click="generateMagicLink('{{ $siswa->id }}')" icon="link" tooltip="Magic Link"></flux:button>
                                    <flux:button size="sm" color="green" wire:click="$dispatch('open-penjualan-modal', { siswa_id: '{{ $siswa->id }}' })" icon="shopping-cart" tooltip="Penjualan"></flux:button>
                                    <flux:button size="sm" color="cyan" href="{{ route('admin.siswa.show', $siswa->id) }}" wire:navigate icon="eye" tooltip="Detail"></flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center">Data tidak ditemukan.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $siswa_items->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <flux:modal wire:model="showDeleteModal" class="md:w-96">
        <flux:heading>Konfirmasi Hapus Data</flux:heading>
        
        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            Anda akan menghapus <strong>{{ count($selected) }}</strong> data siswa. Tindakan ini tidak dapat dibatalkan.
        </div>
        
        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            Ketik kode <strong>{{ $deleteConfirmationCode }}</strong> di bawah ini untuk melanjutkan:
        </div>
        
        <div class="mt-2">
            <flux:input wire:model="inputConfirmationCode" placeholder="Masukkan kode..." />
            @error('inputConfirmationCode') 
                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
            @enderror
        </div>
        
        <div class="mt-6 flex justify-end gap-2">
            <flux:button wire:click="$set('showDeleteModal', false)" variant="outline">Batal</flux:button>
            <flux:button wire:click="executeBulkDelete" color="red">Ya, Hapus Data</flux:button>
        </div>
    </flux:modal>

    <flux:modal name="export-modal" class="md:w-[500px] space-y-6">
        <div>
            <flux:heading size="lg">Ekspor Data Siswa</flux:heading>
            <flux:subheading>Pilih kolom data yang ingin diekspor ke dalam format CSV.</flux:subheading>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <flux:checkbox wire:model="export_fields.nis_nisn" label="NIS & NISN" />
            <flux:checkbox wire:model="export_fields.nama" label="Nama Siswa" />
            <flux:checkbox wire:model="export_fields.jenis_kelamin" label="Jenis Kelamin" />
            <flux:checkbox wire:model="export_fields.tempat_tanggal_lahir" label="Tempat & Tgl Lahir" />
            <flux:checkbox wire:model="export_fields.agama" label="Agama" />
            <flux:checkbox wire:model="export_fields.alamat" label="Alamat" />
            <flux:checkbox wire:model="export_fields.telepon" label="Telepon/WhatsApp" />
            <flux:checkbox wire:model="export_fields.orangtua" label="Nama Orang Tua" />
            <flux:checkbox wire:model="export_fields.lembaga" label="Lembaga" />
            <flux:checkbox wire:model="export_fields.kelas" label="Kelas" />
            <flux:checkbox wire:model="export_fields.status" label="Status" />
            <flux:checkbox wire:model="export_fields.label" label="Label" />
        </div>
        
        <div class="flex gap-2 justify-end mt-6">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button variant="primary" wire:click="exportData" icon="arrow-down-tray">Unduh CSV</flux:button>
        </div>
    </flux:modal>

    <flux:modal wire:model="showSyncOutputModal" class="md:w-[600px] space-y-6">
        <div>
            <flux:heading size="lg">Hasil Sinkronisasi</flux:heading>
            <flux:subheading>Berikut adalah detail dari proses sinkronisasi yang dijalankan.</flux:subheading>
        </div>

        <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <pre class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-mono">{{ $syncOutput }}</pre>
        </div>

        <div class="flex gap-2 justify-end mt-6">
            <flux:button wire:click="$set('showSyncOutputModal', false)" variant="ghost">Tutup</flux:button>
        </div>
    </flux:modal>

    <flux:modal wire:model="showMagicLinkModal" class="md:w-[500px] space-y-6">
        <div>
            <flux:heading size="lg">Magic Link Login</flux:heading>
            <flux:subheading>Link login otomatis untuk siswa: <strong>{{ $magicLinkSiswaName }}</strong></flux:subheading>
        </div>

        <div class="mt-4 flex flex-col items-center justify-center gap-4">
            @if($magicLinkUrl)
                @php
                    $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                        new \BaconQrCode\Renderer\RendererStyle\RendererStyle(192),
                        new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
                    );
                    $writer = new \BaconQrCode\Writer($renderer);
                    $qrCodeSvg = $writer->writeString($magicLinkUrl);
                @endphp
                <div class="p-2 bg-white rounded-lg shadow-sm border border-gray-200">
                    {!! $qrCodeSvg !!}
                </div>
                <div class="w-full flex gap-2" x-data="{ 
                    copied: false, 
                    copy() { 
                        let input = document.getElementById('magic-link-input');
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(input.value).then(() => this.onSuccess());
                        } else {
                            // Fallback untuk HTTP lokal
                            input.select();
                            input.setSelectionRange(0, 99999); // Untuk perangkat mobile
                            let success = false;
                            try {
                                success = document.execCommand('copy');
                            } catch (err) {}
                            
                            if (success) {
                                this.onSuccess();
                            } else {
                                Flux.toast('Gagal otomatis menyalin. Silakan copy secara manual.', 'danger');
                            }
                        }
                    },
                    onSuccess() {
                        this.copied = true; 
                        setTimeout(() => this.copied = false, 2000); 
                        Flux.toast('Link disalin ke clipboard');
                    }
                }">
                    <flux:input id="magic-link-input" value="{{ $magicLinkUrl }}" readonly class="flex-1" />
                    <flux:button variant="primary" icon="clipboard-document" @click="copy()">
                        <span x-text="copied ? 'Disalin' : 'Salin'"></span>
                    </flux:button>
                </div>
                <div class="text-xs text-gray-500 text-center mt-2">
                    Link ini berlaku selama 30 hari. Siapapun yang memiliki link atau QR code ini dapat masuk ke akun siswa tanpa password.
                </div>
            @endif
        </div>

        <div class="flex gap-2 justify-end mt-6">
            <flux:button wire:click="$set('showMagicLinkModal', false)" variant="ghost">Tutup</flux:button>
        </div>
    </flux:modal>

    <!-- Transaksi Penjualan Modal Component -->
    <livewire:admin.siswa.siswa-penjualan-modal />
</div>
