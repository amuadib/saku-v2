<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Data Tagihan
            </h2>
            <flux:button variant="primary" href="{{ route('admin.tagihan.create') }}" wire:navigate>Buat Tagihan</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4 flex flex-col sm:flex-row gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama, NIS, kode tagihan atau keterangan..." icon="magnifying-glass" class="w-full sm:w-1/3" />
                
                <flux:select wire:model.live="filter_kas_id" class="w-full sm:w-1/4" placeholder="Semua Kas">
                    <flux:select.option value="">Semua Kas</flux:select.option>
                    @foreach($kasList as $kas)
                        <flux:select.option value="{{ $kas->id }}">{{ $kas->nama }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="filter_status" class="w-full sm:w-1/4" placeholder="Semua Status">
                    <flux:select.option value="">Semua Status</flux:select.option>
                    <flux:select.option value="lunas">Lunas</flux:select.option>
                    <flux:select.option value="belum">Belum</flux:select.option>
                </flux:select>
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Kode / Tanggal</flux:table.column>
                    <flux:table.column>Siswa</flux:table.column>
                    <flux:table.column>Keterangan</flux:table.column>
                    <flux:table.column>Nominal</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($tagihans as $tagihan)
                        <flux:table.row>
                            <flux:table.cell>
                                <div class="font-medium">{{ $tagihan->kode ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $tagihan->created_at->format('d/m/Y') }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="font-medium">{{ optional($tagihan->siswa)->nama ?? '-' }} ({{ optional($tagihan->siswa)->kelas->nama ?? '-' }})</div>
                                <div class="text-xs text-gray-500">{{ optional($tagihan->siswa)->nis ?? '-' }}/{{ optional($tagihan->siswa)->nisn ?? '-' }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="font-medium">{{ optional($tagihan->kas)->nama ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $tagihan->keterangan ?? '-' }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="font-medium">Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}</div>
                                <div class="text-xs text-gray-500">Dibayar: Rp {{ number_format($tagihan->bayar, 0, ',', '.') }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($tagihan->isLunas())
                                    <flux:badge color="green">Lunas</flux:badge>
                                @elseif($tagihan->bayar > 0)
                                    <flux:badge color="yellow">Sebagian</flux:badge>
                                @else
                                    <flux:badge color="red">Belum Dibayar</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    @if(!$tagihan->isLunas())
                                        <flux:button size="sm" wire:click="openPayModal('{{ $tagihan->id }}')" icon="banknotes" color="green" tooltip="Bayar Tagihan">Bayar</flux:button>
                                    @endif
                                    <flux:button size="sm" variant="danger" wire:click="delete('{{ $tagihan->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus tagihan ini?" icon="trash"></flux:button>
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
                {{ $tagihans->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Pembayaran Tagihan -->
    <flux:modal wire:model="showPayModal" class="md:w-[500px]">
        @if($this->selectedTagihan)
            <flux:heading size="lg">Konfirmasi Pembayaran Tagihan</flux:heading>
            
            <div class="mt-4 bg-gray-50 dark:bg-gray-800 rounded-lg p-4 space-y-3 border border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-3 text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Siswa</span>
                    <span class="col-span-2 font-medium">{{ optional($this->selectedTagihan->siswa)->nama }} ({{ optional($this->selectedTagihan->siswa)->nis }})</span>
                </div>
                <div class="grid grid-cols-3 text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Jenis Tagihan</span>
                    <span class="col-span-2 font-medium">{{ optional($this->selectedTagihan->kas)->nama }}</span>
                </div>
                <div class="grid grid-cols-3 text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Kode Tagihan</span>
                    <span class="col-span-2 font-medium">{{ $this->selectedTagihan->kode ?? '-' }}</span>
                </div>
                <div class="grid grid-cols-3 text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Total Tagihan</span>
                    <span class="col-span-2 font-medium">Rp {{ number_format($this->selectedTagihan->jumlah, 0, ',', '.') }}</span>
                </div>
                <div class="grid grid-cols-3 text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Sudah Dibayar</span>
                    <span class="col-span-2 font-medium text-green-600 dark:text-green-400">Rp {{ number_format($this->selectedTagihan->bayar, 0, ',', '.') }}</span>
                </div>
                <div class="grid grid-cols-3 text-sm pt-2 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Sisa Tagihan</span>
                    <span class="col-span-2 font-bold text-red-600 dark:text-red-400">Rp {{ number_format($this->selectedTagihan->jumlah - $this->selectedTagihan->bayar, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="mt-4">
                <flux:input wire:model="nominalBayar" type="number" label="Nominal Pembayaran" />
                @error('nominalBayar') 
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                @enderror
            </div>
            
            <div class="mt-6 flex justify-end gap-2">
                <flux:button wire:click="$set('showPayModal', false)" variant="outline">Batal</flux:button>
                <flux:button wire:click="prosesPembayaran" color="green">Proses Pembayaran</flux:button>
            </div>
        @endif
    </flux:modal>
</div>
